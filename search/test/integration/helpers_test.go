//go:build integration

package integration

import (
	"context"
	"fmt"
	"os"
	"testing"
	"time"

	"github.com/segmentio/kafka-go"
	"github.com/stretchr/testify/require"
)

func openSearchURL() string {
	if v := os.Getenv("OPENSEARCH_URL"); v != "" {
		return v
	}

	return "http://127.0.0.1:9200"
}

func kafkaBrokers() string {
	return os.Getenv("KAFKA_BROKERS")
}

func redisAddr() string {
	return os.Getenv("REDIS_ADDR")
}

func requireKafka(t *testing.T) string {
	t.Helper()
	brokers := kafkaBrokers()
	if brokers == "" {
		t.Skip("KAFKA_BROKERS not set")
	}

	return brokers
}

func requireRedis(t *testing.T) string {
	t.Helper()
	addr := redisAddr()
	if addr == "" {
		t.Skip("REDIS_ADDR not set")
	}

	return addr
}

func uniqueConsumerGroup(t *testing.T) string {
	t.Helper()
	return fmt.Sprintf("search-integration-%d", time.Now().UnixNano())
}

func publishKafka(t *testing.T, brokers, topic string, payload []byte) {
	t.Helper()

	ctx, cancel := context.WithTimeout(context.Background(), 15*time.Second)
	defer cancel()

	writer := &kafka.Writer{
		Addr:     kafka.TCP(brokers),
		Topic:    topic,
		Balancer: &kafka.LeastBytes{},
	}
	defer writer.Close()

	err := writer.WriteMessages(ctx, kafka.Message{
		Key:   []byte(fmt.Sprintf("integration-%d", time.Now().UnixNano())),
		Value: payload,
	})
	require.NoError(t, err)
}
