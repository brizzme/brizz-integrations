#!/usr/bin/env bash
# Builds the installable plugin zip: dist/brizz-tickets-<version>.zip, with
# the plugin folder at the root of the archive (what Plugins → Add New →
# Upload expects). Tests, composer files and this script are not inside
# the plugin folder, so they never ship.
set -euo pipefail
cd "$(dirname "$0")/.."

VERSION=$(grep -m1 -E '^\s*\*\s*Version:' brizz-tickets/brizz-tickets.php | awk '{print $NF}')
[ -n "$VERSION" ] || { echo "could not read the version from brizz-tickets.php" >&2; exit 1; }
OUT="dist/brizz-tickets-${VERSION}.zip"

mkdir -p dist
rm -f "$OUT"
zip -qr "$OUT" brizz-tickets -x '*.DS_Store'
echo "$OUT ($(du -h "$OUT" | cut -f1))"
