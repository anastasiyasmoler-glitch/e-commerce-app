package main

import (
	"context"
	"log/slog"
	"net/http"
	"os"
	"time"

	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/cache"
	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/config"
	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/httpapi"
	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/kafka"
	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/opensearch"
	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/search"
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
		slog.Error("opensearch products index bootstrap failed", "err", err)
		os.Exit(1)
	}
	if err := osClient.EnsureCategoriesIndex(ctx); err != nil {
		slog.Error("opensearch categories index bootstrap failed", "err", err)
		os.Exit(1)
	}

	publisher := kafka.NewPublisher(cfg.KafkaBrokers)
	facetKeys := config.FacetAttributeKeysFromEnv()
	baseEngine := &search.OpenSearchEngine{
		Client:             osClient,
		Categories:         osClient,
		FacetAttributeKeys: facetKeys,
		FuzzySearch:        cfg.FuzzySearch,
	}

	var engine search.Engine = baseEngine
	if cfg.CacheEnabled {
		redisStore, err := cache.NewRedis(cfg.RedisAddr)
		if err != nil {
			slog.Warn("redis cache disabled", "err", err)
		} else {
			engine = &search.CachingEngine{
				Inner: baseEngine,
				Store: redisStore,
				TTL:   time.Duration(cfg.CacheTTLSeconds) * time.Second,
			}
			slog.Info("redis search cache enabled", "ttl_seconds", cfg.CacheTTLSeconds)
		}
	}

	searchService := search.NewService(engine, publisher)

	server := &http.Server{
		Addr: cfg.HTTPAddr,
		Handler: httpapi.NewRouter(httpapi.Dependencies{
			Ready:  osClient,
			Search: searchService,
		}),
		ReadHeaderTimeout: 5 * time.Second,
	}

	slog.Info("search server listening", "addr", cfg.HTTPAddr, "index", "products")
	if err := server.ListenAndServe(); err != nil && err != http.ErrServerClosed {
		slog.Error("server failed", "err", err)
		os.Exit(1)
	}
}
