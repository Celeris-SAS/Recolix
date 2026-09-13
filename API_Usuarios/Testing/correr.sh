#!/usr/bin/env bash
# Corre toda la suite de tests de API_Usuarios de punta a punta:
# recrea la base de test 'recolix_test' desde recolix.sql y ejecuta phpunit.
# Requiere haber corrido antes API_Usuarios/Testing/install.sh una vez
# (para tener el phpunit.phar).
set -euo pipefail

TESTING_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PHPUNIT="$TESTING_DIR/phpunit.phar"

if [ ! -f "$PHPUNIT" ]; then
    echo "No se encontró $PHPUNIT. Corré primero: $TESTING_DIR/install.sh" >&2
    exit 1
fi

# Ubica el binario de php sin asumir una ruta fija de máquina/SO:
# 1) override explícito, 2) PATH, 3) rutas típicas de XAMPP por SO.
PHP_BIN="${RECOLIX_PHP_BIN:-}"
if [ -z "$PHP_BIN" ]; then
    PHP_BIN="$(command -v php 2>/dev/null || true)"
fi
if [ -z "$PHP_BIN" ]; then
    for candidate in \
        "/c/xampp/php/php.exe" \
        "C:/xampp/php/php.exe" \
        "/d/xampp/php/php.exe" \
        "D:/xampp/php/php.exe" \
        "/opt/lampp/bin/php" \
        "/Applications/XAMPP/xamppfiles/bin/php" \
        "/usr/local/bin/php"; do
        if [ -x "$candidate" ]; then
            PHP_BIN="$candidate"
            break
        fi
    done
fi

if [ -z "$PHP_BIN" ] || [ ! -x "$PHP_BIN" ]; then
    echo "No se encontró el binario de php. Definí la variable RECOLIX_PHP_BIN con la ruta correcta, ej:" >&2
    echo "  RECOLIX_PHP_BIN=/c/xampp/php/php.exe $0" >&2
    exit 1
fi

echo "== Recreando base de test (recolix_test) =="
"$TESTING_DIR/setup-test-db.sh"

echo
echo "== Corriendo la suite de tests =="
"$PHP_BIN" "$PHPUNIT" --configuration "$TESTING_DIR/phpunit.xml"
