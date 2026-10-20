#!/bin/bash
set -e

# Configuración por defecto
PROJECT_ROOT="/var/www/html/dlgc_rrhh"
FUNCTIONS_DIR="$PROJECT_ROOT/scripts/functions"
DB_NAME="db_dlgc_rrhh"
DB_USER="dlgc_user"

# Solicitar la contraseña de MariaDB de forma oculta
read -sp "Password para el usuario $DB_USER: " DB_PASS
echo -e "\n"

export MYSQL_PWD="$DB_PASS"

# Buscar archivos .sql de funciones y procedimientos
SQL_FILES=$(find "$FUNCTIONS_DIR" -type f \( -name "fun_*.sql" -o -name "proc_*.sql" \) | sort)

if [ -z "$SQL_FILES" ]; then
    echo "ERROR: No se encontraron archivos de funciones o procedimientos en $FUNCTIONS_DIR"
    exit 1
fi

echo "1/2: Eliminando funciones y procedimientos existentes (DROP)..."

# Extraer nombres de funciones/procedimientos y ejecutar DROP
for file in $SQL_FILES; do
    # Buscar patrones CREATE OR REPLACE FUNCTION/PROCEDURE nombre
    grep -iE 'CREATE[[:space:]]+(OR[[:space:]]+REPLACE[[:space:]]+)?(FUNCTION|PROCEDURE)' "$file" | while read -r line; do
        TYPE=$(echo "$line" | grep -ioE 'FUNCTION|PROCEDURE' | head -n 1 | tr '[:lower:]' '[:upper:]')
        NAME=$(echo "$line" | sed -E 's/.*(FUNCTION|PROCEDURE)[[:space:]]+([a-zA-Z0-9_]+).*/\2/i')
        
        if [ -n "$TYPE" ] && [ -n "$NAME" ]; then
            mariadb -u "$DB_USER" "$DB_NAME" -e "DROP $TYPE IF EXISTS $NAME;" 2>/dev/null || true
        fi
    done
done

echo "Funciones anteriores eliminadas."
echo -e "\n2/2: Recreando funciones y procedimientos..."

# Recrear cada función/procedimiento
for file in $SQL_FILES; do
    echo "  - Cargando: ${file#$PROJECT_ROOT/}"
    mariadb -u "$DB_USER" "$DB_NAME" < "$file"
done

# Limpiar variable de entorno
export MYSQL_PWD=""

echo -e "\n¡Todas las funciones y procedimientos se recrearon correctamente!"