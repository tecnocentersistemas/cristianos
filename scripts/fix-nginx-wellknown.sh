#!/bin/bash
# Add .well-known location block to nginx config for TWA assetlinks
CONF="/etc/nginx/sites-available/yeshuacristiano.com"

# Check if already has .well-known block
if grep -q "well-known" "$CONF"; then
  echo ".well-known block already exists in nginx config"
else
  # Add before the "location / {" line
  sed -i '/location \/ {/i\    location \/.well-known\/ {\n        alias \/var\/www\/cristianos\/.well-known\/;\n        default_type application\/json;\n    }\n' "$CONF"
  echo "Added .well-known location block"
fi

nginx -t && systemctl reload nginx
echo "Nginx reloaded OK"
