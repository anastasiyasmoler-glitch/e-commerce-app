package indexing

import (
	"context"
	"testing"

	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/opensearch"
	"github.com/stretchr/testify/require"
)

type recordingStore struct {
	upserted []opensearch.ProductDocument
	deleted  []string
	patched  map[string]bool
}

func (r *recordingStore) UpsertProduct(_ context.Context, doc opensearch.ProductDocument) error {
	r.upserted = append(r.upserted, doc)
	return nil
}

func (r *recordingStore) DeleteProduct(_ context.Context, productID string) error {
	r.deleted = append(r.deleted, productID)
	return nil
}

func (r *recordingStore) PatchProductAvailability(_ context.Context, productID string, available bool) error {
	r.patched[productID] = available
	return nil
}

func TestHandlerUpsertsProductCreated(t *testing.T) {
	store := &recordingStore{patched: map[string]bool{}}
	handler := NewHandler(store)

	err := handler.Handle(context.Background(), "product.created", []byte(`{
		"id": 12,
		"store_id": 3,
		"category_id": 9,
		"name": "Chair",
		"price": 1500,
		"attributes": {"color":"black"}
	}`))
	require.NoError(t, err)
	require.Len(t, store.upserted, 1)
	require.Equal(t, "12", store.upserted[0].ID)
	require.Equal(t, "3", store.upserted[0].StoreID)
	require.Equal(t, "9", store.upserted[0].CategoryID)
	require.Equal(t, 1500, store.upserted[0].Price)
	require.True(t, store.upserted[0].Available)
}

func TestHandlerDeletesProduct(t *testing.T) {
	store := &recordingStore{patched: map[string]bool{}}
	handler := NewHandler(store)

	err := handler.Handle(context.Background(), "product.deleted", []byte(`{"id":5,"store_id":1}`))
	require.NoError(t, err)
	require.Equal(t, []string{"5"}, store.deleted)
}

func TestHandlerPatchesAvailabilityFromStock(t *testing.T) {
	store := &recordingStore{patched: map[string]bool{}}
	handler := NewHandler(store)

	err := handler.Handle(context.Background(), "stock.changed", []byte(`{
		"product_id": 7,
		"store_id": 1,
		"quantity": 2,
		"reserved": 2
	}`))
	require.NoError(t, err)
	require.False(t, store.patched["7"])
}
