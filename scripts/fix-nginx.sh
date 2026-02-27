#!/bin/bash
# Fix nginx gzip and yeshuacristiano.com config

# 1) Enable gzip types in nginx.conf
sed -i 's/# gzip_vary on;/gzip_vary on;/' /etc/nginx/nginx.conf
sed -i 's/# gzip_proxied any;/gzip_proxied any;/' /etc/nginx/nginx.conf
sed -i 's/# gzip_comp_level 6;/gzip_comp_level 6;/' /etc/nginx/nginx.conf
sed -i 's/# gzip_buffers 16 8k;/gzip_buffers 16 8k;/' /etc/nginx/nginx.conf
sed -i 's/# gzip_http_version 1.1;/gzip_http_version 1.1;/' /etc/nginx/nginx.conf
sed -i 's/# gzip_types text\/plain/gzip_types text\/plain/' /etc/nginx/nginx.conf

# 2) Update yeshuacristiano.com nginx config with proper optimizations
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

# 3) Test and reload nginx
nginx -t && systemctl reload nginx
echo "Done! Gzip enabled + nginx config updated"
