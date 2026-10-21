package kafka

import (
	"context"
	"encoding/json"
	"time"

	"github.com/segmentio/kafka-go"
)

const TopicSearchPerformed = "search.performed"

type SearchPerformedEvent struct {
	Query       string                 `json:"query"`
	Filters     map[string]interface{} `json:"filters"`
	ResultCount int                    `json:"result_count"`
	Timestamp   time.Time              `json:"timestamp"`
}

type Publisher struct {
	writer *kafka.Writer
}

func NewPublisher(brokers string) *Publisher {
	return &Publisher{
		writer: &kafka.Writer{
			Addr:     kafka.TCP(splitBrokers(brokers)...),
			Topic:    TopicSearchPerformed,
			Balancer: &kafka.LeastBytes{},
		},
	}
}

func (p *Publisher) PublishSearchPerformed(ctx context.Context, event SearchPerformedEvent) error {
	if event.Timestamp.IsZero() {
		event.Timestamp = time.Now().UTC()
	}

	body, err := json.Marshal(event)
	if err != nil {
		return err
	}

	return p.writer.WriteMessages(ctx, kafka.Message{
		Key:   []byte(event.Query),
		Value: body,
	})
}

func (p *Publisher) Close() error {
	return p.writer.Close()
}
