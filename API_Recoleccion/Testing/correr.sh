#!/usr/bin/env bash
# Corre toda la suite de tests de API_Recoleccion de punta a punta:
# recrea la base de test 'recolix_test' desde recolix.sql y ejecuta phpunit.
# Requiere haber corrido antes API_Recoleccion/Testing/install.sh una vez
# (para tener el phpunit.phar).
set -euo pipefail

TESTING_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PHP_BIN="/Applications/XAMPP/xamppfiles/bin/php"
PHPUNIT="$TESTING_DIR/phpunit.phar"

if [ ! -f "$PHPUNIT" ]; then
    echo "No se encontró $PHPUNIT. Corré primero: $TESTING_DIR/install.sh" >&2
    exit 1
fi

echo "== Recreando base de test (recolix_test) =="
"$TESTING_DIR/setup-test-db.sh"

echo
echo "== Corriendo la suite de tests =="
"$PHP_BIN" "$PHPUNIT" --configuration "$TESTING_DIR/phpunit.xml"
