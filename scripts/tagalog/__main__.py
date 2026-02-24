#!/usr/bin/env python3
import sys
import traceback

from .lastfm import fetch_last_fm
from .copy import copy_to_production


def help() -> None:
    print(
        """Available subcommands:

    - fetch
    Fetch track data from Last.fm.

    - copy <sqlalchemy_url> <path/to/taglist>
    Copy data to production database.
"""
    )


def copy() -> None:
    try:
        connstr = sys.argv[2]
        taglist_path = sys.argv[3]
    except IndexError:
        traceback.print_exc()
        print("Missing arguments", file=sys.stderr)
        exit(1)

    with open(taglist_path, "r") as f:
        taglist = [tag.rstrip() for tag in f]

    copy_to_production(connstr, taglist)


commands = {
    "fetch": fetch_last_fm,
    "copy": copy,
    "help": help,
}

if len(sys.argv) <= 1:
    help()
else:
    cmd = commands.get(sys.argv[1])
    if cmd is None:
        print(f"Unrecognized command {sys.argv[1]}", file=sys.stderr)
        exit(1)

    cmd()
