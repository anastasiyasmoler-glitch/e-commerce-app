//go:build integration

package integration

import (
	"context"
	"testing"
	"time"

	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/indexing"
	searchkafka "github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/kafka"
	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/opensearch"
	"github.com/segmentio/kafka-go"
	"github.com/stretchr/testify/require"
)

func TestKafkaConsumerIndexesProductIntoOpenSearch(t *testing.T) {
	brokers := requireKafka(t)

	ctx, cancel := context.WithTimeout(context.Background(), 45*time.Second)
	defer cancel()

	client := opensearch.NewClient(openSearchURL())
	require.NoError(t, client.EnsureProductsIndex(ctx))
	require.NoError(t, client.EnsureCategoriesIndex(ctx))

	productID := "88001"
	require.NoError(t, client.DeleteProduct(ctx, productID))

	handler := indexing.NewHandler(client, client)
	groupID := uniqueConsumerGroup(t)
	consumer := searchkafka.NewConsumerWithOffset(
		brokers,
		groupID,
		[]string{"product.created"},
		kafka.FirstOffset,
	)
	defer consumer.Close()

	consumerCtx, stopConsumer := context.WithCancel(ctx)
	defer stopConsumer()

	consumerErr := make(chan error, 1)
	go func() {
		consumerErr <- consumer.Run(consumerCtx, handler.Handle)
	}()

	publishKafka(t, brokers, "product.created", []byte(`{
		"id": 88001,
		"store_id": 10,
		"category_id": 501,
		"name": "Kafka Consumer Desk",
		"slug": "kafka-consumer-desk",
		"price": 9900,
		"description": "Indexed via Kafka consumer"
	}`))

	require.Eventually(t, func() bool {
		refreshCtx, refreshCancel := context.WithTimeout(ctx, 5*time.Second)
		defer refreshCancel()
		if err := client.Refresh(refreshCtx); err != nil {
			return false
		}

		doc, err := client.GetProduct(refreshCtx, productID)
		if err != nil {
			return false
		}

		return doc.Name == "Kafka Consumer Desk" && doc.Price == 9900
	}, 30*time.Second, 500*time.Millisecond)

	stopConsumer()

	select {
	case err := <-consumerErr:
		require.ErrorIs(t, err, context.Canceled)
	default:
	}
}

func TestKafkaConsumerAppliesStockChanged(t *testing.T) {
	brokers := requireKafka(t)

	ctx, cancel := context.WithTimeout(context.Background(), 45*time.Second)
	defer cancel()

	client := opensearch.NewClient(openSearchURL())
	require.NoError(t, client.EnsureProductsIndex(ctx))

	productID := "88002"
	require.NoError(t, client.DeleteProduct(ctx, productID))

	handler := indexing.NewHandler(client, client)
	require.NoError(t, handler.Handle(ctx, "product.created", []byte(`{
		"id": 88002,
		"store_id": 10,
		"name": "Kafka Stock Lamp",
		"price": 1200,
		"quantity": 5,
		"reserved": 0
	}`)))
	require.NoError(t, client.Refresh(ctx))

	groupID := uniqueConsumerGroup(t)
	consumer := searchkafka.NewConsumerWithOffset(
		brokers,
		groupID,
		[]string{"stock.changed"},
		kafka.FirstOffset,
	)
	defer consumer.Close()

	consumerCtx, stopConsumer := context.WithCancel(ctx)
	defer stopConsumer()

	go func() {
		_ = consumer.Run(consumerCtx, handler.Handle)
	}()

	publishKafka(t, brokers, "stock.changed", []byte(`{
		"product_id": 88002,
		"store_id": 10,
		"quantity": 1,
		"reserved": 1
	}`))

	require.Eventually(t, func() bool {
		refreshCtx, refreshCancel := context.WithTimeout(ctx, 5*time.Second)
		defer refreshCancel()
		if err := client.Refresh(refreshCtx); err != nil {
			return false
		}

		doc, err := client.GetProduct(refreshCtx, productID)
		if err != nil {
			return false
		}

		return !doc.Available
	}, 30*time.Second, 500*time.Millisecond)

	stopConsumer()
}
