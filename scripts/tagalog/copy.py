#!/usr/bin/env python3
from itertools import repeat
from pgvector.sqlalchemy import HALFVEC
import datetime
import sys
import traceback
import sqlalchemy as sa

from . import config as cfg
from . import schema as sch


dst_schema = sa.MetaData()

dst_artists = sa.Table(
    "artists",
    dst_schema,
    sa.Column("id", sa.Integer),
    sa.Column("name", sa.String),
    sa.Column("created_at", sa.DateTime),
    sa.Column("updated_at", sa.DateTime),
)

dst_tracks = sa.Table(
    "tracks",
    dst_schema,
    sa.Column("id", sa.Integer),
    sa.Column("name", sa.String),
    sa.Column("artist_id", sa.Integer),
    sa.Column("created_at", sa.DateTime),
    sa.Column("updated_at", sa.DateTime),
    sa.Column("tags", HALFVEC(128)),
)

dst_urls = sa.Table(
    "urls",
    dst_schema,
    sa.Column("id", sa.Integer),
    sa.Column("track_id", sa.ForeignKey(dst_tracks.c.id)),
    sa.Column("website", sa.String),
    sa.Column("url", sa.String),
    sa.Column("created_at", sa.DateTime),
    sa.Column("updated_at", sa.DateTime),
)


def copy_to_production(dst_url: str, tag_components: list[str]) -> None:
    if len(tag_components) != 128:
        raise ValueError("tag_components must consist of exactly 128 tags")

    src_engine = sa.create_engine(cfg.SQLALCHEMY_STRING)
    dst_engine = sa.create_engine(dst_url)

    with sa.Connection(src_engine) as src, sa.Connection(dst_engine) as dst:

        # retrieve data
        artists = src.execute(sa.text("SELECT id, name FROM artists;"))
        tracks = src.execute(sa.text("SELECT id, title, artist FROM tracks;"))

        # map tags to ids
        tag_map = {}
        for i, tag in zip(range(128), tag_components):
            tag_id = src.execute(
                sa.text("SELECT id FROM tags WHERE name=:name"), {"name": tag}
            ).scalar()
            if tag_id is None:
                raise ValueError(f"tag '{tag}' does not exist in source database")
            tag_map[tag_id] = i

        src.commit()

        # insert and remap artist ids
        artist_id_map = {}
        for i in artists:
            time = datetime.datetime.now(datetime.UTC)

            try:
                with dst.begin():
                    dst_id = dst.execute(
                        sa.insert(dst_artists).values(
                            name=i.name,
                            created_at=time,
                            updated_at=time,
                        ).returning(dst_artists.c.id)
                    ).scalar()
                    artist_id_map[i.id] = dst_id
            except Exception:
                traceback.print_exc()
                print(f"Failed to import artist {i.name}", file=sys.stderr)

        # input tracks
        for i in tracks:
            time = datetime.datetime.now(datetime.UTC)

            # load track urls
            urls = src.execute(
                sa.select(sch.track_urls).where(sch.track_urls.c.track == i.id)
            )

            # load track tags
            track_tags = src.execute(
                sa.text("SELECT tag, count AS significance FROM track_tags \
                        WHERE track=:id;"),
                {"id": i.id},
            )
            src.commit()

            # collect tag vector
            tag_vector = list(repeat(0, 128))
            for tag in track_tags:
                component = tag_map.get(tag.tag)
                if component is not None:
                    tag_vector[component] = tag.significance / 100.0

            try:
                with dst.begin():
                    dst_id = dst.execute(
                        sa.insert(dst_tracks)
                        .values(
                            name=i.title,
                            artist_id=artist_id_map[i.artist],
                            tags=tag_vector,
                            created_at=time,
                            updated_at=time,
                        )
                        .returning(dst_tracks.c.id)
                    ).scalar()

                    for url in urls:
                        dst.execute(
                            sa.insert(dst_urls).values(
                                track_id=dst_id,
                                website=url.website,
                                url=url.url,
                                created_at=time,
                                updated_at=time,
                            )
                        )
            except Exception:
                traceback.print_exc()
                print(f"Failed to import {i.artist} - {i.title}", file=sys.stderr)
