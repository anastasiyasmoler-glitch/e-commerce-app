package indexing

import (
	"context"
	"encoding/json"
	"fmt"
	"log/slog"

	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/opensearch"
)

type ProductStore interface {
	UpsertProduct(ctx context.Context, doc opensearch.ProductDocument) error
	DeleteProduct(ctx context.Context, productID string) error
	PatchProductAvailability(ctx context.Context, productID string, available bool) error
}

type Handler struct {
	products ProductStore
}

func NewHandler(products ProductStore) *Handler {
	return &Handler{products: products}
}

func (h *Handler) Handle(ctx context.Context, topic string, payload []byte) error {
	switch topic {
	case "product.created", "product.updated":
		doc, err := productDocumentFromPayload(payload)
		if err != nil {
			return err
		}
		return h.products.UpsertProduct(ctx, doc)
	case "product.deleted":
		id, err := productIDFromDeletePayload(payload)
		if err != nil {
			return err
		}
		return h.products.DeleteProduct(ctx, id)
	case "stock.changed":
		id, available, err := availabilityFromStockPayload(payload)
		if err != nil {
			return err
		}
		return h.products.PatchProductAvailability(ctx, id, available)
	default:
		slog.Debug("skip unhandled topic", "topic", topic)
		return nil
	}
}

func productDocumentFromPayload(payload []byte) (opensearch.ProductDocument, error) {
	var raw map[string]json.RawMessage
	if err := json.Unmarshal(payload, &raw); err != nil {
		return opensearch.ProductDocument{}, fmt.Errorf("decode product payload: %w", err)
	}

	id, err := requiredStringID(raw, "id")
	if err != nil {
		return opensearch.ProductDocument{}, err
	}
	storeID, err := requiredStringID(raw, "store_id")
	if err != nil {
		return opensearch.ProductDocument{}, err
	}

	name, err := requiredString(raw, "name")
	if err != nil {
		return opensearch.ProductDocument{}, err
	}

	slug, _ := optionalString(raw, "slug")
	sku, _ := optionalString(raw, "sku")
	image, _ := optionalString(raw, "image")
	description, _ := optionalString(raw, "description")

	doc := opensearch.ProductDocument{
		ID:          id,
		StoreID:     storeID,
		CategoryID:  optionalStringID(raw, "category_id"),
		Name:        name,
		Slug:        slug,
		SKU:         sku,
		Image:       image,
		Description: description,
		Price:       optionalInt(raw, "price"),
		Available:   true,
	}

	if attrs, ok := raw["attributes"]; ok && len(attrs) > 0 && string(attrs) != "null" {
		var decoded map[string]any
		if err := json.Unmarshal(attrs, &decoded); err != nil {
			return opensearch.ProductDocument{}, fmt.Errorf("decode attributes: %w", err)
		}
		doc.Attributes = decoded
	}

	return doc, nil
}

func productIDFromDeletePayload(payload []byte) (string, error) {
	var raw map[string]json.RawMessage
	if err := json.Unmarshal(payload, &raw); err != nil {
		return "", fmt.Errorf("decode delete payload: %w", err)
	}

	return requiredStringID(raw, "id")
}

func availabilityFromStockPayload(payload []byte) (string, bool, error) {
	var raw map[string]json.RawMessage
	if err := json.Unmarshal(payload, &raw); err != nil {
		return "", false, fmt.Errorf("decode stock payload: %w", err)
	}

	productID, err := requiredStringID(raw, "product_id")
	if err != nil {
		return "", false, err
	}

	quantity := optionalInt(raw, "quantity")
	reserved := optionalInt(raw, "reserved")
	available := quantity-reserved > 0

	return productID, available, nil
}
