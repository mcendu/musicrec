#!/usr/bin/env python3
import datetime
import sys
import traceback
import sqlalchemy as sa

from . import config as cfg
from . import schema as sch


dst_schema = sa.MetaData()

dst_tracks = sa.Table(
    "tracks",
    dst_schema,
    sa.Column("id", sa.Integer),
    sa.Column("name", sa.String),
    sa.Column("artist", sa.String),
    sa.Column("created_at", sa.DateTime),
    sa.Column("updated_at", sa.DateTime),
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


def copy_to_production(dst_constr: str) -> None:
    src_engine = sa.create_engine(cfg.SQLALCHEMY_STRING)
    dst_engine = sa.create_engine(dst_constr)

    with sa.Connection(src_engine) as src, sa.Connection(dst_engine) as dst:
        tracks = src.execute(
            sa.text(
                """
                SELECT t.id AS id, t.title AS title, a.name AS artist
                    FROM tracks AS t
                    LEFT JOIN artists AS a ON t.artist = a.id;
                """
            )
        )
        src.commit()

        for i in tracks:
            time = datetime.datetime.now(datetime.UTC)

            urls = src.execute(
                sa.select(sch.track_urls).where(sch.track_urls.c.track == i.id)
            )
            src.commit()

            try:
                with dst.begin():
                    dst_id = dst.execute(
                        sa.insert(dst_tracks)
                        .values(
                            name=i.title,
                            artist=i.artist,
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
