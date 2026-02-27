#!/bin/bash
# Add no-cache rule for sw.js in nginx
cat > /etc/nginx/sites-available/yeshuacristiano.com << 'NGINX'
server {
    listen 80;
    server_name yeshuacristiano.com www.yeshuacristiano.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name yeshuacristiano.com www.yeshuacristiano.com;
    root /var/www/cristianos;
    index index.html;

    ssl_certificate /etc/letsencrypt/live/yeshuacristiano.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yeshuacristiano.com/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    # NEVER cache service worker
    location = /sw.js {
        add_header Cache-Control "no-cache, no-store, must-revalidate";
        add_header Pragma "no-cache";
        expires 0;
    }

    # PHP support
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php7.4-fpm.sock;
    }

    # Main fallback
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Static assets with long cache
    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot|mp3|mp4|webm|json)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # Media files - allow range requests for video streaming
    location /media/ {
        expires 30d;
        add_header Accept-Ranges bytes;
        add_header Cache-Control "public";
    }
}
NGINX

nginx -t && systemctl reload nginx
echo "Done - sw.js no-cache rule added"
