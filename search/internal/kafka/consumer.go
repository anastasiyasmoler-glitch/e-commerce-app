package kafka

import (
	"context"
	"log/slog"
	"strings"
	"time"

	"github.com/segmentio/kafka-go"
)

type MessageHandler func(ctx context.Context, topic string, payload []byte) error

type Consumer struct {
	reader *kafka.Reader
}

func NewConsumer(brokers string, groupID string, topics []string) *Consumer {
	brokerList := splitBrokers(brokers)
	return &Consumer{
		reader: kafka.NewReader(kafka.ReaderConfig{
			Brokers:     brokerList,
			GroupID:     groupID,
			GroupTopics: topics,
			MinBytes:    1,
			MaxBytes:    10e6,
			MaxWait:     500 * time.Millisecond,
		}),
	}
}

func (c *Consumer) Run(ctx context.Context, handle MessageHandler) error {
	for {
		if ctx.Err() != nil {
			return ctx.Err()
		}

		message, err := c.reader.FetchMessage(ctx)
		if err != nil {
			return err
		}

		if err := handle(ctx, message.Topic, message.Value); err != nil {
			slog.Error("kafka message handling failed", "topic", message.Topic, "err", err)
		} else if err := c.reader.CommitMessages(ctx, message); err != nil {
			slog.Error("kafka commit failed", "topic", message.Topic, "err", err)
		}
	}
}

func (c *Consumer) Close() error {
	return c.reader.Close()
}

func splitBrokers(brokers string) []string {
	parts := strings.Split(brokers, ",")
	out := make([]string, 0, len(parts))
	for _, part := range parts {
		trimmed := strings.TrimSpace(part)
		if trimmed != "" {
			out = append(out, trimmed)
		}
	}

	if len(out) == 0 {
		return []string{"kafka:9092"}
	}

	return out
}
