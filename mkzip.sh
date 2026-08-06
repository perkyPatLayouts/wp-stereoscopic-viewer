#!/bin/bash
set -euo pipefail

# Build the distributable plugin archive.
# Junk files are excluded at creation time (-x) rather than deleted afterwards,
# so the pattern catches them at any depth and the build doesn't fail when a
# given path happens not to exist.

rm -f stereoscopic-image-viewer.zip

zip -r stereoscopic-image-viewer.zip \
	stereoscopic-image-viewer.php \
	render.php \
	uninstall.php \
	readme.txt \
	block.json \
	languages/ \
	includes/ \
	assets/ \
	admin/ \
	-x "*.DS_Store" "__MACOSX/*" "*/.git/*"

echo
echo "Built stereoscopic-image-viewer.zip"
unzip -l stereoscopic-image-viewer.zip | tail -1
