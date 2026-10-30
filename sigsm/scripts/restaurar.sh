#!/bin/bash
# ============================================================
# S.I.G.S.M. - Restaurar un respaldo en el servidor Debian
# Uso: sudo ./restaurar.sh 20261028_0200
# ATENCION: reemplaza la base y los PDF actuales.
# ============================================================
set -euo pipefail

if [ $# -ne 1 ]; then
    echo "Uso: $0 CARPETA_DEL_RESPALDO (ej: 20261028_0200)"
    exit 1
fi

APP="/var/www/sigsm"
ORIGEN="/var/backups/sigsm/$1"

[ -f "$ORIGEN/base.sql.gz" ] || { echo "No existe $ORIGEN/base.sql.gz"; exit 1; }

echo "Restaurando base..."
gunzip -c "$ORIGEN/base.sql.gz" | mysql --default-character-set=utf8mb4

echo "Restaurando PDF..."
tar -xzf "$ORIGEN/documentos.tar.gz" -C "$APP"
chown -R www-data:www-data "$APP/documentos"

echo "Restauracion completa desde $ORIGEN"
