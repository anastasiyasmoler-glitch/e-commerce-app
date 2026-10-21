package opensearch

import (
	"bytes"
	"context"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"strings"
)

type ProductDocument struct {
	ID          string         `json:"id"`
	StoreID     string         `json:"store_id"`
	CategoryID  string         `json:"category_id"`
	Name        string         `json:"name"`
	Slug        string         `json:"slug"`
	SKU         string         `json:"sku,omitempty"`
	Description string         `json:"description,omitempty"` // indexed for full-text search
	Price       int            `json:"price"`
	Attributes  map[string]any `json:"attributes,omitempty"`
	Image       string         `json:"image,omitempty"`
	Available   bool           `json:"available"`
}

func (c *Client) UpsertProduct(ctx context.Context, doc ProductDocument) error {
	if doc.ID == "" {
		return fmt.Errorf("product id is required")
	}

	body, err := json.Marshal(doc)
	if err != nil {
		return err
	}

	url := fmt.Sprintf("%s/%s/_doc/%s", c.baseURL, productsIndex, doc.ID)
	req, err := http.NewRequestWithContext(ctx, http.MethodPut, url, bytes.NewReader(body))
	if err != nil {
		return err
	}
	req.Header.Set("Content-Type", "application/json")

	resp, err := c.httpClient.Do(req)
	if err != nil {
		return err
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK && resp.StatusCode != http.StatusCreated {
		raw, _ := io.ReadAll(io.LimitReader(resp.Body, 512))
		return fmt.Errorf("upsert product: status %d: %s", resp.StatusCode, strings.TrimSpace(string(raw)))
	}

	return nil
}

func (c *Client) PatchProductAvailability(ctx context.Context, productID string, available bool) error {
	if productID == "" {
		return fmt.Errorf("product id is required")
	}

	body, err := json.Marshal(map[string]map[string]bool{
		"doc": {"available": available},
	})
	if err != nil {
		return err
	}

	url := fmt.Sprintf("%s/%s/_update/%s", c.baseURL, productsIndex, productID)
	req, err := http.NewRequestWithContext(ctx, http.MethodPost, url, bytes.NewReader(body))
	if err != nil {
		return err
	}
	req.Header.Set("Content-Type", "application/json")

	resp, err := c.httpClient.Do(req)
	if err != nil {
		return err
	}
	defer resp.Body.Close()

	if resp.StatusCode == http.StatusNotFound {
		return nil
	}

	if resp.StatusCode != http.StatusOK {
		raw, _ := io.ReadAll(io.LimitReader(resp.Body, 512))
		return fmt.Errorf("patch availability: status %d: %s", resp.StatusCode, strings.TrimSpace(string(raw)))
	}

	return nil
}

func (c *Client) DeleteProduct(ctx context.Context, productID string) error {
	if productID == "" {
		return fmt.Errorf("product id is required")
	}

	url := fmt.Sprintf("%s/%s/_doc/%s", c.baseURL, productsIndex, productID)
	req, err := http.NewRequestWithContext(ctx, http.MethodDelete, url, nil)
	if err != nil {
		return err
	}

	resp, err := c.httpClient.Do(req)
	if err != nil {
		return err
	}
	defer resp.Body.Close()

	if resp.StatusCode == http.StatusNotFound {
		return nil
	}

	if resp.StatusCode != http.StatusOK {
		raw, _ := io.ReadAll(io.LimitReader(resp.Body, 512))
		return fmt.Errorf("delete product: status %d: %s", resp.StatusCode, strings.TrimSpace(string(raw)))
	}

	return nil
}
