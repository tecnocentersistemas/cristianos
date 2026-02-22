#!/bin/bash
set -e

echo "=== Arreglando nginx y configurando yeshuacristiano.com con SSL ==="

# Crear archivo faltante si no existe
touch /etc/letsencrypt/le_http_01_cert_challenge.conf 2>/dev/null || true
echo "[OK] Archivo challenge creado"

# Copiar config nginx con SSL
cp /var/www/cristianos/deploy/yeshuacristiano.com /etc/nginx/sites-available/yeshuacristiano.com
ln -sf /etc/nginx/sites-available/yeshuacristiano.com /etc/nginx/sites-enabled/yeshuacristiano.com
echo "[OK] Config con SSL copiada y habilitada"

# Verificar y recargar
nginx -t && systemctl reload nginx
echo "[OK] Nginx recargado con SSL"

echo "=== LISTO! https://yeshuacristiano.com configurado ==="
