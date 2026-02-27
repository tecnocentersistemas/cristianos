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

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php7.4-fpm.sock;
    }

    location /.well-known/ {
        alias /var/www/cristianos/.well-known/;
        default_type application/json;
    }

    location / {
        try_files $uri $uri/ /index.html;
    }

    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff2|mp3|mp4)$ {
        expires 7d;
    }
}
