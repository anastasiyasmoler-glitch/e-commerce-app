# syntax=docker/dockerfile:1

FROM golang:1.23-alpine AS build

RUN apk add --no-cache ca-certificates git

WORKDIR /src

COPY go.mod ./
RUN go mod download

COPY . .

RUN CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -trimpath -ldflags="-s -w" -o /out/search-server ./cmd/server

FROM alpine:3.21

RUN apk add --no-cache ca-certificates wget

WORKDIR /app

COPY --from=build /out/search-server /app/search-server

RUN adduser -D -H -u 10001 appuser \
    && chown -R appuser:appuser /app

USER appuser

EXPOSE 8080

ENTRYPOINT ["/app/search-server"]
