#!/usr/bin/env bash
# Crea (o recrea) la base "recolix_test" a partir del schema versionado en
# recolix.sql, renombrando la base de 'recolix' a 'recolix_test' al vuelo.
# No toca la base 'recolix' real: nunca se conecta a ella ni la referencia.
set -euo pipefail

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(cd "$DIR/../.." && pwd)"
SQL_FUENTE="$ROOT/recolix.sql"

# Mac (XAMPP) y Windows (XAMPP) instalan el binario en rutas distintas;
# se puede pisar con MYSQL_BIN si hace falta otra ubicación.
if [ -n "${MYSQL_BIN:-}" ]; then
    : # usa el valor ya exportado
elif [ -f "/Applications/XAMPP/xamppfiles/bin/mysql" ]; then
    MYSQL_BIN="/Applications/XAMPP/xamppfiles/bin/mysql"
elif [ -f "/c/xampp/mysql/bin/mysql.exe" ]; then
    MYSQL_BIN="/c/xampp/mysql/bin/mysql.exe"
else
    MYSQL_BIN="mysql"
fi

if [ ! -f "$SQL_FUENTE" ]; then
    echo "No se encontró $SQL_FUENTE" >&2
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

echo "Recreando '$DB_NAME' desde recolix.sql (con $MYSQL_BIN)..."
sed \
    -e "s/DROP DATABASE IF EXISTS \`recolix\`;/DROP DATABASE IF EXISTS \`${DB_NAME}\`;/" \
    -e "s/CREATE DATABASE IF NOT EXISTS \`recolix\`/CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`/" \
    -e "s/USE \`recolix\`;/USE \`${DB_NAME}\`;/" \
    "$SQL_FUENTE" \
    | "$MYSQL_BIN" -h "$DB_HOST" -u "$DB_USER" ${DB_PASS:+-p"$DB_PASS"} --skip-ssl

echo "Listo: base '$DB_NAME' creada/recreada."
