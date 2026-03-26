#!/bin/bash
# disable-yeshua.sh - Desactiva TEMPORALMENTE yeshuacristiano.com devolviendo 503
# Para reactivar: ejecutar enable-yeshua.sh

CONF="/etc/nginx/sites-available/yeshuacristiano.com"
BACKUP="/etc/nginx/sites-available/yeshuacristiano.com.original"

echo "=============================="
echo "HACIENDO BACKUP DEL CONFIG..."
echo "=============================="
cp "$CONF" "$BACKUP"
echo "Backup guardado en: $BACKUP"

echo ""
echo "=============================="
echo "ESCRIBIENDO CONFIG DE MANTENIMIENTO (503)..."
echo "=============================="
cat > "$CONF" << 'NGINXCONF'
server {
    listen 80;
    server_name yeshuacristiano.com www.yeshuacristiano.com;
    return 503;
}

server {
    listen 443 ssl;
    server_name yeshuacristiano.com www.yeshuacristiano.com;

    ssl_certificate /etc/letsencrypt/live/yeshuacristiano.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yeshuacristiano.com/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    return 503;
}
NGINXCONF

echo "Config de mantenimiento escrito."

echo ""
echo "=============================="
echo "VERIFICANDO CONFIG NGINX..."
echo "=============================="
nginx -t 2>&1

if [ $? -eq 0 ]; then
    echo ""
    echo "Config OK. Recargando nginx..."
    systemctl reload nginx
    echo "LISTO - Sitio yeshuacristiano.com DESACTIVADO (503)"
else
    echo ""
    echo "ERROR en config! Restaurando backup..."
    cp "$BACKUP" "$CONF"
    echo "Backup restaurado. Sitio intacto."
fi
