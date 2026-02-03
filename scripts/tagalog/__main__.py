#!/usr/bin/env python3
import argparse
import sys
import traceback

from .lastfm import fetch_last_fm
from .copy import copy_to_production


def copy() -> None:
    try:
        connstr = sys.argv[2]
    except IndexError:
        print("Missing destination connection string", file=sys.stderr)
        exit(1)

    copy_to_production(connstr)


commands = {
    "fetch": fetch_last_fm,
    "copy": copy,
}

if len(sys.argv) <= 1:
    fetch_last_fm()
else:
    cmd = commands.get(sys.argv[1])
    if cmd is None:
        print(f"Unrecognized command {sys.argv[1]}", file=sys.stderr)
        exit(1)

    cmd()
