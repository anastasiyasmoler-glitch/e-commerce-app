package config

import (
	"fmt"
	"os"
	"strings"
)

type Config struct {
	HTTPAddr           string
	OpenSearchURL      string
	KafkaBrokers       string
	KafkaConsumerGroup string
	RedisAddr          string
}

func Load() (Config, error) {
	cfg := Config{
		HTTPAddr:           envOrDefault("HTTP_ADDR", ":8080"),
		OpenSearchURL:      strings.TrimSpace(os.Getenv("OPENSEARCH_URL")),
		KafkaBrokers:       envOrDefault("KAFKA_BROKERS", "kafka:9092"),
		KafkaConsumerGroup: envOrDefault("KAFKA_CONSUMER_GROUP", "search-service-group"),
		RedisAddr:          envOrDefault("REDIS_ADDR", "redis:6379"),
	}

	if cfg.OpenSearchURL == "" {
		return Config{}, fmt.Errorf("OPENSEARCH_URL is required")
	}

	return cfg, nil
}

func envOrDefault(key, fallback string) string {
	if v := strings.TrimSpace(os.Getenv(key)); v != "" {
		return v
	}

	return fallback
}
