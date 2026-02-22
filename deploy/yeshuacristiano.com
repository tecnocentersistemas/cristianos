server {
    listen 80;
    server_name yeshuacristiano.com www.yeshuacristiano.com;
    root /var/www/cristianos;
    index index.html;

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php7.4-fpm.sock;
    }

    location / {
        try_files $uri $uri/ /index.html;
    }

    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff2|mp3|mp4)$ {
        expires 7d;
    }
}
