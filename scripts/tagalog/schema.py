#!/usr/bin/env python3
import sqlalchemy as sa

metadata = sa.MetaData()
"Database metadata."

migration_log = sa.Table(
    "tagalog_migration_log",
    metadata,
    sa.Column("id", sa.Integer, primary_key=True, autoincrement=True),
    sa.Column("migration", sa.String, nullable=False),
    sa.Column("applied_at", sa.DateTime, nullable=False),
    sa.Index("migration_log__applied_at__i", "applied_at"),
)
"Migration log and database signature."

artists = sa.Table(
    "artists",
    metadata,
    sa.Column("id", sa.Integer, primary_key=True, autoincrement=True),
    sa.Column("mbid", sa.Uuid(as_uuid=False), nullable=True, unique=True),
    sa.Column("name", sa.String, nullable=False),
    sa.Index("artists__name__u", "name", unique=True),
)
"Table of artists."

tracks = sa.Table(
    "tracks",
    metadata,
    sa.Column("id", sa.Integer, primary_key=True, autoincrement=True),
    sa.Column("mbid", sa.Uuid(as_uuid=False), nullable=True, unique=True),
    sa.Column("title", sa.String, nullable=False),
    sa.Column("artist", sa.ForeignKey(artists.c.id)),
    sa.Index("tracks__artist__i", "artist"),
)
"Table of tracks."

artist_aliases = sa.Table(
    "artist_aliases",
    metadata,
    sa.Column("artist", sa.ForeignKey(artists.c.id), nullable=False),
    sa.Column("name", sa.String, nullable=False),
    sa.Column("lang", sa.String, nullable=False),
    sa.PrimaryKeyConstraint("artist", "lang", name="artist_aliases__p"),
)
"Names of artists in non-native languages."

track_aliases = sa.Table(
    "track_aliases",
    metadata,
    sa.Column("track", sa.ForeignKey(tracks.c.id), nullable=False),
    sa.Column("title", sa.String, nullable=False),
    sa.Column("lang", sa.String, nullable=False),
    sa.PrimaryKeyConstraint("track", "lang", name="track_aliases__p"),
)
"Names of tracks in non-native languages."

track_urls: sa.Table = sa.Table(
    "track_urls",
    metadata,
    sa.Column("track", sa.ForeignKey(tracks.c.id)),
    sa.Column("website", sa.String),
    sa.Column("url", sa.String),
    sa.PrimaryKeyConstraint("track", "website", name="track_urls__p"),
)
"Links of tracks to other websites, e.g. YouTube."

tags = sa.Table(
    "tags",
    metadata,
    sa.Column("id", sa.Integer, primary_key=True, autoincrement=True),
    sa.Column("name", sa.String, nullable=False),
    sa.Column("total_count", sa.Integer, nullable=False, default=0),
    sa.Index("tags__name__u", "name", unique=True),
    sa.Index("tags__total_count__i", "total_count"),
)
"Table of tags."

track_tags = sa.Table(
    "track_tags",
    metadata,
    sa.Column("track", sa.ForeignKey(tracks.c.id)),
    sa.Column("tag", sa.ForeignKey(tags.c.id)),
    sa.Column("count", sa.Integer, nullable=False),
    sa.PrimaryKeyConstraint("tag", "track", name="track_tags__p"),
    sa.Index("track_tags__track__i", "track"),
)
"Tags applied to tracks by Last.fm users."
