#!/bin/sh
set -eu

mkdir -p /etc/nginx/certs

if [ ! -f /etc/nginx/certs/localhost.pem ] || [ ! -f /etc/nginx/certs/localhost.key ]; then
    openssl req -x509 -nodes -newkey rsa:2048 -days 365 \
        -keyout /etc/nginx/certs/localhost.key \
        -out /etc/nginx/certs/localhost.pem \
        -subj "/CN=localhost"
fi
