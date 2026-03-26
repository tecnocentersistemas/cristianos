#!/bin/bash
# check-nginx.sh - Verifica estado actual de nginx para yeshuacristiano.com

echo "=============================="
echo "ESTADO DEL SYMLINK EN sites-enabled:"
echo "=============================="
ls -la /etc/nginx/sites-enabled/ | grep yeshua || echo "(no encontrado)"

echo ""
echo "=============================="
echo "ARCHIVOS EN sites-available:"
echo "=============================="
ls -la /etc/nginx/sites-available/ | grep yeshua || echo "(no encontrado)"

echo ""
echo "=============================="
echo "CONTENIDO DEL CONFIG ACTUAL:"
echo "=============================="
cat /etc/nginx/sites-available/yeshuacristiano.com 2>/dev/null || echo "(archivo no encontrado)"

echo ""
echo "=============================="
echo "TEST NGINX CONFIG:"
echo "=============================="
nginx -t 2>&1

echo ""
echo "=============================="
echo "NGINX STATUS:"
echo "=============================="
systemctl is-active nginx
