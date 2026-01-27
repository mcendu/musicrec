#!/usr/bin/env python3
import requests
import sqlalchemy as sa
import sqlalchemy.exc
import re
import sys
import time
import traceback

from . import config as cfg
from . import schema as sch


USER_AGENT = (
    f"Tagalog/1.0 (ynd1@student.london.ac.uk) "
    + f"Python/{sys.version_info.major}.{sys.version_info.minor}"
)


session = requests.Session()
session.headers.update({"User-Agent": USER_AGENT})


class RateLimiter:
    def __init__(self, requests: int, period: float):
        self.max_requests = requests
        self.period = period
        self.requests = 0
        self.next_reset = 0.0

    def wait(self):
        """
        Check if a request can be sent to the desired API now, and sleep
        if it cannot.
        """
        if time.time() >= self.next_reset:
            self.requests = 0
            self.next_reset = 0

        if self.requests == self.max_requests:
            time.sleep(self.next_reset - time.time())
            self.requests = 0
            self.next_reset = 0

        if self.requests == 0:
            self.next_reset = time.time() + self.period

        self.requests += 1


lastfm_limiter = RateLimiter(1, 1.0)


class LastFmError(Exception):
    "Error returned by the Last.fm API."

    def __init__(self, error: int, message: str):
        super().__init__(self, error, message)
        self.error = error
        self.message = message

    def __str__(self):
        return f"[Error {self.error}] {self.message}"


def create_schema(conn: sa.Connection) -> None:
    "Create the database schema."
    with conn.begin():
        # check if schema is up to date
        existing_tables = sa.inspect(conn).get_table_names()
        if "tagalog_migration_log" in existing_tables:
            latest = conn.execute(
                sa.select(sch.migration_log.c.migration)
                .order_by(sch.migration_log.c.applied_at.desc())
                .limit(1)
            ).scalar()
            if latest == "1970_01_01_000000_initial_schema":
                return

        # create schema
        sch.metadata.create_all(conn)

        # log migration
        conn.execute(
            sa.insert(sch.migration_log).values(
                migration="1970_01_01_000000_initial_schema",
                applied_at=sa.func.now(),
            )
        )


def fetch_track_tags(
    conn: sa.Connection,
    id: int,
    track: str,
    artist: str,
) -> None:
    """
    Get the most popular tags for a track from Last.fm and store it in
    the database.
    """
    lastfm_limiter.wait()
    resp = session.get(
        "https://ws.audioscrobbler.com/2.0/",
        params={
            "method": "track.gettoptags",
            "artist": artist,
            "track": track,
            "api_key": cfg.LASTFM_KEY,
            "format": "json",
        },
    )
    resp.raise_for_status()
    data = resp.json()

    if "error" in data:
        raise LastFmError(data["error"], data["message"])

    tags = data["toptags"]["tag"]

    for tag in tags:
        tag_name = tag["name"]
        tag_count = int(tag["count"])

        with conn.begin():
            tag_id = conn.execute(
                sa.select(sch.tags.c.id).where(sch.tags.c.name == tag_name)
            ).scalar()
            # insert tag if it doesn't exist
            if tag_id is None:
                result = conn.execute(
                    sa.insert(sch.tags).values(name=tag_name).returning(sch.tags.c.id)
                )
                tag_id = result.scalar()

            # append
            conn.execute(
                sa.insert(sch.track_tags).values(
                    track=id,
                    tag=tag_id,
                    count=tag_count,
                )
            )

            # update tag total count
            conn.execute(
                sa.update(sch.tags)
                .where(sch.tags.c.id == tag_id)
                .values(total_count=sch.tags.c.total_count + tag_count)
            )

    print(f"Loaded tags for '{artist} - {track}'", file=sys.stderr)


def fetch_track(
    conn: sa.Connection,
    track: str,
    artist: str,
    url: str,
    artist_mbid: str | None,
    track_mbid: str | None,
) -> None:
    """
    Fetch data for a track from Last.fm and store it in the database.
    """
    track_id = None

    with conn.begin():
        # find artist
        artist_id = conn.execute(
            sa.select(sch.artists.c.id).where(sch.artists.c.name == artist)
        ).scalar()
        # insert artist if nonexistent
        if artist_id is None:
            artist_id = conn.execute(
                sa.insert(sch.artists)
                .values(
                    name=artist,
                    mbid=artist_mbid,
                )
                .returning(sch.artists.c.id)
            ).scalar()
        assert isinstance(artist_id, int)

        track_exists = conn.execute(
            sa.select(
                sa.select(sch.tracks.c.id)
                .where(sch.tracks.c.title == track and sch.tracks.c.artist == artist_id)
                .exists()
            )
        ).scalar()
        if track_exists:
            print(f"Skipping known track '{artist} - {track}'", file=sys.stderr)
        else:
            # insert track
            track_id = conn.execute(
                sa.insert(sch.tracks)
                .values(
                    mbid=track_mbid,
                    title=track,
                    artist=artist_id,
                )
                .returning(sch.tracks.c.id)
            ).scalar()

            # insert last.fm link
            conn.execute(
                sa.insert(sch.track_urls).values(
                    track=track_id,
                    website="lastfm",
                    url=url,
                )
            )

    if not track_exists:
        assert isinstance(track_id, int)
        fetch_track_tags(conn, track_id, track, artist)


def fetch_tag_top_tracks(conn: sa.Connection, tag: str, page: int) -> None:
    lastfm_limiter.wait()
    resp = session.get(
        "https://ws.audioscrobbler.com/2.0/",
        params={
            "method": "tag.getTopTracks",
            "tag": tag,
            "page": page,
            "limit": 50,
            "api_key": cfg.LASTFM_KEY,
            "format": "json",
        },
    )
    resp.raise_for_status()
    data = resp.json()

    if "error" in data:
        raise LastFmError(data["error"], data["message"])

    print(f"Getting top tracks for tag '{tag}', page {page}", file=sys.stderr)

    tracks = data["tracks"]["track"]

    for track in tracks:
        artist = track["artist"]["name"]
        name = track["name"]
        url = track["url"]
        artist_mbid = track["artist"].get("mbid", None)
        track_mbid = track.get("mbid", None)

        try:
            fetch_track(conn, name, artist, url, artist_mbid, track_mbid)
        except Exception as e:
            traceback.print_exception(e, file=sys.stderr)
            print(f"Failed to fetch data for '{artist} - {name}'", file=sys.stderr)


def fetch_top_tags(conn: sa.Connection) -> None:
    lastfm_limiter.wait()
    resp = session.get(
        "https://ws.audioscrobbler.com/2.0/",
        params={
            "method": "tag.getTopTags",
            "api_key": cfg.LASTFM_KEY,
            "format": "json",
        },
    )
    resp.raise_for_status()
    data = resp.json()

    if "error" in data:
        raise LastFmError(data["error"], data["message"])

    tags = data["toptags"]["tag"]

    for tag in tags:
        tag_name = tag["name"]
        print(f"Fetching tracks for tag '{tag_name}'", file=sys.stderr)
        for page in range(1, 6):
            fetch_tag_top_tracks(conn, tag_name, page)


engine = sa.create_engine(cfg.SQLALCHEMY_STRING)

with sa.Connection(engine) as conn:
    create_schema(conn)

    fetch_top_tags(conn)
