#!/bin/sh
set -eu

SCRIPT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)"
CERT_DIR="${SCRIPT_DIR}/certs"

mkdir -p "${CERT_DIR}"

if [ ! -f "${CERT_DIR}/localhost.pem" ] || [ ! -f "${CERT_DIR}/localhost.key" ]; then
    openssl req -x509 -nodes -newkey rsa:2048 -days 365 \
        -keyout "${CERT_DIR}/localhost.key" \
        -out "${CERT_DIR}/localhost.pem" \
        -subj "/CN=localhost"
    echo "Created ${CERT_DIR}/localhost.pem and localhost.key"
else
    echo "Certs already exist in ${CERT_DIR}"
fi
