#!/usr/bin/env bash
set -euo pipefail

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PHAR="$DIR/phpunit.phar"

if [ -f "$PHAR" ]; then
    echo "phpunit.phar ya existe en $PHAR"
    exit 0
fi

echo "Descargando phpunit (rama 10.x, compatible con PHP 8.1+)..."
curl -sL https://phar.phpunit.de/phpunit-10.phar -o "$PHAR"
chmod +x "$PHAR"
echo "Listo: $PHAR"
