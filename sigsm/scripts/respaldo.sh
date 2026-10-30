#!/bin/bash
# ============================================================
# S.I.G.S.M. - Copia de seguridad para el servidor Debian (DTI)
# Respalda la base y los PDF en /var/backups/sigsm/AAAAMMDD_HHMM
# y borra los respaldos con mas de 30 dias.
# Programar con cron (todos los dias a las 02:00):
#   0 2 * * * /var/www/sigsm/scripts/respaldo.sh >> /var/log/sigsm_respaldo.log 2>&1
# Las credenciales se leen de /root/.my.cnf (no quedan en el script).
# ============================================================
set -euo pipefail

BD="hc_gestion_documental"
APP="/var/www/sigsm"
DESTINO_BASE="/var/backups/sigsm"
FECHA=$(date +%Y%m%d_%H%M)
DESTINO="$DESTINO_BASE/$FECHA"

mkdir -p "$DESTINO"

echo "[$(date)] Exportando base $BD"
mysqldump --single-transaction --routines --default-character-set=utf8mb4 --databases "$BD" | gzip > "$DESTINO/base.sql.gz"

echo "[$(date)] Comprimiendo PDF"
tar -czf "$DESTINO/documentos.tar.gz" -C "$APP" documentos

# Verificacion rapida: los archivos no pueden estar vacios
test -s "$DESTINO/base.sql.gz" && test -s "$DESTINO/documentos.tar.gz"

echo "[$(date)] Eliminando respaldos de mas de 30 dias"
find "$DESTINO_BASE" -mindepth 1 -maxdepth 1 -type d -mtime +30 -exec rm -rf {} +

echo "[$(date)] Respaldo OK en $DESTINO"
