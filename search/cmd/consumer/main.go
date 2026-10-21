package main

import (
	"context"
	"log/slog"
	"os"
	"os/signal"
	"syscall"
	"time"

	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/config"
	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/indexing"
	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/kafka"
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
	bootstrapCtx, cancelBootstrap := context.WithTimeout(context.Background(), 30*time.Second)
	defer cancelBootstrap()

	if err := osClient.EnsureProductsIndex(bootstrapCtx); err != nil {
		slog.Error("opensearch index bootstrap failed", "err", err)
		os.Exit(1)
	}

	handler := indexing.NewHandler(osClient)
	consumer := kafka.NewConsumer(cfg.KafkaBrokers, cfg.KafkaConsumerGroup, config.KafkaConsumeTopicsFromEnv())

	ctx, stop := signal.NotifyContext(context.Background(), syscall.SIGINT, syscall.SIGTERM)
	defer stop()

	slog.Info("search consumer started",
		"brokers", cfg.KafkaBrokers,
		"group", cfg.KafkaConsumerGroup,
		"topics", config.KafkaConsumeTopicsFromEnv(),
	)

	if err := consumer.Run(ctx, handler.Handle); err != nil && ctx.Err() == nil {
		slog.Error("consumer stopped", "err", err)
		os.Exit(1)
	}

	_ = consumer.Close()
}
