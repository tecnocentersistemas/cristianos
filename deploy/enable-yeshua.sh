#!/bin/bash
# enable-yeshua.sh - Reactiva yeshuacristiano.com desde el backup original

CONF="/etc/nginx/sites-available/yeshuacristiano.com"
BACKUP="/etc/nginx/sites-available/yeshuacristiano.com.original"

echo "=============================="
echo "VERIFICANDO BACKUP..."
echo "=============================="
if [ ! -f "$BACKUP" ]; then
    echo "ERROR: No se encontro el backup en $BACKUP"
    echo "No se puede reactivar sin el backup."
    exit 1
fi
echo "Backup encontrado OK."

echo ""
echo "=============================="
echo "RESTAURANDO CONFIG ORIGINAL..."
echo "=============================="
cp "$BACKUP" "$CONF"
echo "Config original restaurado."

echo ""
echo "=============================="
echo "VERIFICANDO CONFIG NGINX..."
echo "=============================="
nginx -t 2>&1

if [ $? -eq 0 ]; then
    echo ""
    echo "Config OK. Recargando nginx..."
    systemctl reload nginx
    echo "LISTO - Sitio yeshuacristiano.com REACTIVADO"
else
    echo ""
    echo "ERROR en config nginx. Revisar manualmente."
fi
