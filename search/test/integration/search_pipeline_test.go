//go:build integration

package integration

import (
	"context"
	"os"
	"testing"
	"time"

	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/indexing"
	searchkafka "github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/kafka"
	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/opensearch"
	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/search"
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

func TestProductIndexingAndSearch(t *testing.T) {
	ctx := context.Background()
	client := opensearch.NewClient(openSearchURL())

	require.NoError(t, client.EnsureProductsIndex(ctx))
	require.NoError(t, client.EnsureCategoriesIndex(ctx))

	productID := "99001"
	require.NoError(t, client.DeleteProduct(ctx, productID))

	handler := indexing.NewHandler(client, client)
	require.NoError(t, handler.Handle(ctx, "category.created", []byte(`{
		"id": 501,
		"store_id": 10,
		"name": "Integration Category",
		"slug": "integration-category"
	}`)))
	require.NoError(t, handler.Handle(ctx, "product.created", []byte(`{
		"id": 99001,
		"store_id": 10,
		"category_id": 501,
		"name": "Integration Oak Chair",
		"slug": "integration-oak-chair",
		"price": 4200,
		"attributes": {"color":"oak"}
	}`)))
	require.NoError(t, client.Refresh(ctx))

	engine := &search.OpenSearchEngine{
		Client:     client,
		Categories: client,
	}
	result, err := engine.Search(ctx, search.Params{
		Query:   "Integration Oak",
		StoreID: "10",
		Page:    1,
		PerPage: 10,
		Sort:    search.SortRelevance,
	})
	require.NoError(t, err)
	require.GreaterOrEqual(t, result.Pagination.Total, 1)
	require.NotEmpty(t, result.Items)
}

func TestPublishSearchPerformed(t *testing.T) {
	brokers := kafkaBrokers()
	if brokers == "" {
		t.Skip("KAFKA_BROKERS not set")
	}

	ctx, cancel := context.WithTimeout(context.Background(), 30*time.Second)
	defer cancel()

	publisher := searchkafka.NewPublisher(brokers)
	defer publisher.Close()

	err := publisher.PublishSearchPerformed(ctx, searchkafka.SearchPerformedEvent{
		Query:       "integration-test",
		Filters:     map[string]interface{}{"store_id": "10"},
		ResultCount: 1,
		Timestamp:   time.Now().UTC(),
	})
	require.NoError(t, err)

	reader := kafka.NewReader(kafka.ReaderConfig{
		Brokers:   []string{brokers},
		Topic:     searchkafka.TopicSearchPerformed,
		Partition: 0,
		MaxBytes:  10e6,
		MaxWait:   2 * time.Second,
	})
	defer reader.Close()

	_, err = reader.ReadMessage(ctx)
	require.NoError(t, err)
}
