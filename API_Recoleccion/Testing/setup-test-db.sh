#!/usr/bin/env bash
set -euo pipefail

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(cd "$DIR/../.." && pwd)"
SQL_FUENTE="$ROOT/recolix.sql"

if [ ! -f "$SQL_FUENTE" ]; then
    echo "No se encontró $SQL_FUENTE" >&2
    exit 1
fi

MYSQL_BIN="${RECOLIX_MYSQL_BIN:-}"
if [ -z "$MYSQL_BIN" ]; then
    MYSQL_BIN="$(command -v mysql 2>/dev/null || true)"
fi
if [ -z "$MYSQL_BIN" ]; then
    for candidate in \
        "/c/xampp/mysql/bin/mysql.exe" \
        "C:/xampp/mysql/bin/mysql.exe" \
        "/d/xampp/mysql/bin/mysql.exe" \
        "D:/xampp/mysql/bin/mysql.exe" \
        "/opt/lampp/bin/mysql" \
        "/Applications/XAMPP/xamppfiles/bin/mysql" \
        "/usr/local/mysql/bin/mysql"; do
        if [ -x "$candidate" ]; then
            MYSQL_BIN="$candidate"
            break
        fi
    done
fi

if [ -z "$MYSQL_BIN" ] || [ ! -x "$MYSQL_BIN" ]; then
    echo "No se encontró el binario de mysql. Definí la variable RECOLIX_MYSQL_BIN con la ruta correcta, ej:" >&2
    echo "  RECOLIX_MYSQL_BIN=/c/xampp/mysql/bin/mysql.exe $0" >&2
    exit 1
fi

DB_HOST="${RECOLIX_DB_HOST:-localhost}"
DB_USER="${RECOLIX_DB_USER:-root}"
DB_PASS="${RECOLIX_DB_PASS:-}"
DB_NAME="${RECOLIX_DB_NAME:-recolix_test}"

if [[ "$DB_NAME" != *test* ]]; then
    echo "Este script solo debe apuntar a una base de test (el nombre debe contener 'test'). Recibido: $DB_NAME" >&2
    exit 1
fi

echo "Recreando '$DB_NAME' desde recolix.sql..."
sed \
    -e "s/DROP DATABASE IF EXISTS \`recolix\`;/DROP DATABASE IF EXISTS \`${DB_NAME}\`;/" \
    -e "s/CREATE DATABASE IF NOT EXISTS \`recolix\`/CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`/" \
    -e "s/USE \`recolix\`;/USE \`${DB_NAME}\`;/" \
    "$SQL_FUENTE" \
    | "$MYSQL_BIN" -h "$DB_HOST" -u "$DB_USER" ${DB_PASS:+-p"$DB_PASS"} --skip-ssl

echo "Listo: base '$DB_NAME' creada/recreada."
