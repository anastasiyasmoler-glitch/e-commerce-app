package config

import (
	"testing"

	"github.com/stretchr/testify/require"
)

func TestLoadRequiresOpenSearchURL(t *testing.T) {
	t.Setenv("OPENSEARCH_URL", "")

	_, err := Load()
	require.Error(t, err)
}

func TestLoadDefaults(t *testing.T) {
	t.Setenv("OPENSEARCH_URL", "http://opensearch:9200")
	t.Setenv("HTTP_ADDR", "")
	t.Setenv("KAFKA_BROKERS", "")
	t.Setenv("KAFKA_CONSUMER_GROUP", "")
	t.Setenv("REDIS_ADDR", "")

	cfg, err := Load()
	require.NoError(t, err)
	require.Equal(t, ":8080", cfg.HTTPAddr)
	require.Equal(t, "http://opensearch:9200", cfg.OpenSearchURL)
	require.Equal(t, "kafka:9092", cfg.KafkaBrokers)
	require.Equal(t, "search-service-group", cfg.KafkaConsumerGroup)
	require.Equal(t, "redis:6379", cfg.RedisAddr)
	require.True(t, cfg.CacheEnabled)
	require.Equal(t, 60, cfg.CacheTTLSeconds)
}
