package config

import "strings"

func DefaultKafkaConsumeTopics() []string {
	return []string{
		"product.created",
		"product.updated",
		"product.deleted",
		"category.created",
		"category.updated",
		"category.deleted",
		"stock.changed",
	}
}

func KafkaConsumeTopicsFromEnv() []string {
	raw := strings.TrimSpace(envOrDefault("KAFKA_CONSUME_TOPICS", ""))
	if raw == "" {
		return DefaultKafkaConsumeTopics()
	}

	parts := strings.Split(raw, ",")
	topics := make([]string, 0, len(parts))
	for _, part := range parts {
		topic := strings.TrimSpace(part)
		if topic != "" {
			topics = append(topics, topic)
		}
	}

	if len(topics) == 0 {
		return DefaultKafkaConsumeTopics()
	}

	return topics
}
