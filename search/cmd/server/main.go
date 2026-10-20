package main

import (
	"context"
	"log/slog"
	"net/http"
	"os"
	"time"

	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/config"
	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/httpapi"
	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/opensearch"
)

func main() {
	slog.SetDefault(slog.New(slog.NewJSONHandler(os.Stdout, nil)))

	cfg, err := config.Load()
	if err != nil {
		slog.Error("config failed", "err", err)
		os.Exit(1)
	}

	osClient := opensearch.NewClient(cfg.OpenSearchURL)

	ctx, cancel := context.WithTimeout(context.Background(), 30*time.Second)
	defer cancel()

	if err := osClient.EnsureProductsIndex(ctx); err != nil {
		slog.Error("opensearch index bootstrap failed", "err", err)
		os.Exit(1)
	}

	server := &http.Server{
		Addr:              cfg.HTTPAddr,
		Handler:           httpapi.NewRouter(httpapi.Dependencies{Ready: osClient}),
		ReadHeaderTimeout: 5 * time.Second,
	}

	slog.Info("search server listening", "addr", cfg.HTTPAddr, "index", "products")
	if err := server.ListenAndServe(); err != nil && err != http.ErrServerClosed {
		slog.Error("server failed", "err", err)
		os.Exit(1)
	}
}
