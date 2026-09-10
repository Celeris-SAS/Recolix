#!/usr/bin/env bash
# Corre toda la suite de tests de API_Gestion de punta a punta:
# recrea la base de test 'recolix_test' desde recolix.sql y ejecuta phpunit.
# Requiere haber corrido antes API_Gestion/Testing/install.sh una vez
# (para tener el phpunit.phar).
set -euo pipefail

TESTING_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PHPUNIT="$TESTING_DIR/phpunit.phar"

# Mac (XAMPP) y Windows (XAMPP) instalan el binario en rutas distintas;
# se puede pisar con PHP_BIN si hace falta otra ubicación.
if [ -n "${PHP_BIN:-}" ]; then
    : # usa el valor ya exportado
elif [ -f "/Applications/XAMPP/xamppfiles/bin/php" ]; then
    PHP_BIN="/Applications/XAMPP/xamppfiles/bin/php"
elif [ -f "/c/xampp/php/php.exe" ]; then
    PHP_BIN="/c/xampp/php/php.exe"
else
    PHP_BIN="php"
fi

if [ ! -f "$PHPUNIT" ]; then
    echo "No se encontró $PHPUNIT. Corré primero: $TESTING_DIR/install.sh" >&2
    exit 1
fi

echo "== Recreando base de test (recolix_test) =="
"$TESTING_DIR/setup-test-db.sh"

echo
echo "== Corriendo la suite de tests =="
"$PHP_BIN" "$PHPUNIT" --configuration "$TESTING_DIR/phpunit.xml"
