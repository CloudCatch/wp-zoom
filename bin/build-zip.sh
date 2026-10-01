#!/bin/sh

PLUGIN_SLUG="wp-zoom"
PROJECT_PATH=$(pwd)
BUILD_PATH="${PROJECT_PATH}/build"
DEST_PATH="$BUILD_PATH/$PLUGIN_SLUG"

echo "Generating build directory..."
rm -rf "$BUILD_PATH"
mkdir -p "$DEST_PATH"

echo "Installing JS dependencies..."
npm ci || exit "$?"
echo "Running JS Build..."
npm run build || exit "$?"
echo "Generating translations..."
npm run i18n || exit "$?"

echo "Syncing files..."
# openrsync treats a trailing CR as part of the pattern, so a CRLF .distignore excludes nothing.
DISTIGNORE=$(mktemp)
sed 's/\r$//' "$PROJECT_PATH/.distignore" > "$DISTIGNORE"
rsync -rc --exclude-from="$DISTIGNORE" "$PROJECT_PATH/" "$DEST_PATH/" --delete --delete-excluded
rm -f "$DISTIGNORE"

echo "Generating zip file..."
cd "$BUILD_PATH" || exit
zip -q -r "${PLUGIN_SLUG}.zip" "$PLUGIN_SLUG/"

cd "$PROJECT_PATH" || exit
echo "${PLUGIN_SLUG}.zip file generated!"

echo "Build done!"
