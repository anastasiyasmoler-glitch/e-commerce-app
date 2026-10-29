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

type recordingCategoryStore struct {
	upserted []opensearch.CategoryDocument
	deleted  []string
}

func (r *recordingCategoryStore) UpsertCategory(_ context.Context, doc opensearch.CategoryDocument) error {
	r.upserted = append(r.upserted, doc)
	return nil
}

func (r *recordingCategoryStore) DeleteCategory(_ context.Context, categoryID string) error {
	r.deleted = append(r.deleted, categoryID)
	return nil
}

func newTestHandler(products *recordingStore) *Handler {
	return NewHandler(products, &recordingCategoryStore{})
}

func TestHandlerUpsertsProductCreated(t *testing.T) {
	store := &recordingStore{patched: map[string]bool{}}
	handler := newTestHandler(store)

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
	handler := newTestHandler(store)

	err := handler.Handle(context.Background(), "product.deleted", []byte(`{"id":5,"store_id":1}`))
	require.NoError(t, err)
	require.Equal(t, []string{"5"}, store.deleted)
}

func TestHandlerPatchesAvailabilityFromStock(t *testing.T) {
	store := &recordingStore{patched: map[string]bool{}}
	handler := newTestHandler(store)

	err := handler.Handle(context.Background(), "stock.changed", []byte(`{
		"product_id": 7,
		"store_id": 1,
		"quantity": 2,
		"reserved": 2
	}`))
	require.NoError(t, err)
	require.False(t, store.patched["7"])
}

func TestHandlerProductUpdatedTwiceUpsertsLatestPayload(t *testing.T) {
	store := &recordingStore{patched: map[string]bool{}}
	handler := newTestHandler(store)

	require.NoError(t, handler.Handle(context.Background(), "product.updated", []byte(`{
		"id": 20,
		"store_id": 1,
		"name": "Version one",
		"price": 900
	}`)))
	require.NoError(t, handler.Handle(context.Background(), "product.updated", []byte(`{
		"id": 20,
		"store_id": 1,
		"name": "Version two",
		"price": 1800
	}`)))
	require.Len(t, store.upserted, 2)
	require.Equal(t, "Version two", store.upserted[1].Name)
	require.Equal(t, 1800, store.upserted[1].Price)
}

func TestHandlerUpsertsCategoryCreated(t *testing.T) {
	products := &recordingStore{patched: map[string]bool{}}
	categories := &recordingCategoryStore{}
	handler := NewHandler(products, categories)

	err := handler.Handle(context.Background(), "category.created", []byte(`{
		"id": 9,
		"store_id": 3,
		"parent_id": null,
		"name": "Sofas",
		"slug": "sofas"
	}`))
	require.NoError(t, err)
	require.Len(t, categories.upserted, 1)
	require.Equal(t, "9", categories.upserted[0].ID)
	require.Equal(t, "Sofas", categories.upserted[0].Name)
}

func TestHandlerDeletesCategory(t *testing.T) {
	products := &recordingStore{patched: map[string]bool{}}
	categories := &recordingCategoryStore{}
	handler := NewHandler(products, categories)

	err := handler.Handle(context.Background(), "category.deleted", []byte(`{"id":4,"store_id":1}`))
	require.NoError(t, err)
	require.Equal(t, []string{"4"}, categories.deleted)
}
