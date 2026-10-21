package config

import (
	"fmt"
	"os"
	"strconv"
	"strings"
)

type Config struct {
	HTTPAddr           string
	OpenSearchURL      string
	KafkaBrokers       string
	KafkaConsumerGroup string
	RedisAddr          string
	CacheEnabled       bool
	CacheTTLSeconds    int
	FuzzySearch        bool
}

func Load() (Config, error) {
	cfg := Config{
		HTTPAddr:           envOrDefault("HTTP_ADDR", ":8080"),
		OpenSearchURL:      strings.TrimSpace(os.Getenv("OPENSEARCH_URL")),
		KafkaBrokers:       envOrDefault("KAFKA_BROKERS", "kafka:9092"),
		KafkaConsumerGroup: envOrDefault("KAFKA_CONSUMER_GROUP", "search-service-group"),
		RedisAddr:          envOrDefault("REDIS_ADDR", "redis:6379"),
		CacheEnabled:       envOrDefault("CACHE_ENABLED", "true") == "true",
		CacheTTLSeconds:    envIntOrDefault("CACHE_TTL_SECONDS", 60),
		FuzzySearch:        envOrDefault("FUZZY_SEARCH", "true") == "true",
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

func envIntOrDefault(key string, fallback int) int {
	raw := strings.TrimSpace(os.Getenv(key))
	if raw == "" {
		return fallback
	}

	parsed, err := strconv.Atoi(raw)
	if err != nil || parsed < 1 {
		return fallback
	}

	return parsed
}
