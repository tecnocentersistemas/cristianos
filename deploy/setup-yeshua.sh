#!/bin/bash
set -e

echo "=== Configurando yeshuacristiano.com ==="

# Copiar config nginx
cp /var/www/cristianos/deploy/yeshuacristiano.com /etc/nginx/sites-available/yeshuacristiano.com
echo "[OK] Config copiada a sites-available"

# Habilitar sitio
ln -sf /etc/nginx/sites-available/yeshuacristiano.com /etc/nginx/sites-enabled/yeshuacristiano.com
echo "[OK] Sitio habilitado en sites-enabled"

# Verificar config nginx
nginx -t
echo "[OK] Nginx config valida"

# Recargar nginx
systemctl reload nginx
echo "[OK] Nginx recargado"

# Intentar obtener certificado SSL con certbot
if command -v certbot &> /dev/null; then
    certbot --nginx -d yeshuacristiano.com -d www.yeshuacristiano.com --non-interactive --agree-tos --email admin@yeshuacristiano.com --redirect 2>&1 || echo "[WARN] Certbot fallo - SSL se configura despues"
else
    echo "[WARN] Certbot no instalado. Instalando..."
    apt-get update -qq && apt-get install -y -qq certbot python3-certbot-nginx
    certbot --nginx -d yeshuacristiano.com -d www.yeshuacristiano.com --non-interactive --agree-tos --email admin@yeshuacristiano.com --redirect 2>&1 || echo "[WARN] Certbot fallo - verificar DNS"
fi

echo "=== LISTO! yeshuacristiano.com configurado ==="
