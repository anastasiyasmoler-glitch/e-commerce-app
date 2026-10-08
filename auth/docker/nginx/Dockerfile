FROM nginx:1.27-alpine

RUN apk add --no-cache openssl

COPY nginx.conf /etc/nginx/nginx.conf
COPY generate-certs.sh /docker-entrypoint.d/00-generate-certs.sh
RUN sed -i 's/\r$//' /docker-entrypoint.d/00-generate-certs.sh \
    && chmod +x /docker-entrypoint.d/00-generate-certs.sh
