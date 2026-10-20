package opensearch

import (
	"context"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"strings"
	"time"
)

const productsIndex = "products"

type Client struct {
	baseURL    string
	httpClient *http.Client
}

func NewClient(baseURL string) *Client {
	return &Client{
		baseURL: strings.TrimRight(strings.TrimSpace(baseURL), "/"),
		httpClient: &http.Client{
			Timeout: 10 * time.Second,
		},
	}
}

func (c *Client) Ready(ctx context.Context) error {
	req, err := http.NewRequestWithContext(ctx, http.MethodGet, c.baseURL+"/_cluster/health?wait_for_status=yellow&timeout=5s", nil)
	if err != nil {
		return err
	}

	resp, err := c.httpClient.Do(req)
	if err != nil {
		return err
	}
	defer resp.Body.Close()

	if resp.StatusCode < http.StatusOK || resp.StatusCode >= http.StatusMultipleChoices {
		body, _ := io.ReadAll(io.LimitReader(resp.Body, 512))
		return fmt.Errorf("cluster health: status %d: %s", resp.StatusCode, strings.TrimSpace(string(body)))
	}

	var payload struct {
		Status string `json:"status"`
	}
	if err := json.NewDecoder(resp.Body).Decode(&payload); err != nil {
		return err
	}

	switch payload.Status {
	case "green", "yellow":
		return nil
	default:
		return fmt.Errorf("cluster status %q", payload.Status)
	}
}

func (c *Client) EnsureProductsIndex(ctx context.Context) error {
	head, err := http.NewRequestWithContext(ctx, http.MethodHead, c.baseURL+"/"+productsIndex, nil)
	if err != nil {
		return err
	}

	headResp, err := c.httpClient.Do(head)
	if err != nil {
		return err
	}
	headResp.Body.Close()

	if headResp.StatusCode == http.StatusOK {
		return nil
	}

	if headResp.StatusCode != http.StatusNotFound {
		return fmt.Errorf("index head: status %d", headResp.StatusCode)
	}

	req, err := http.NewRequestWithContext(ctx, http.MethodPut, c.baseURL+"/"+productsIndex, strings.NewReader(productsIndexMapping))
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
		body, _ := io.ReadAll(io.LimitReader(resp.Body, 512))
		return fmt.Errorf("create index: status %d: %s", resp.StatusCode, strings.TrimSpace(string(body)))
	}

	return nil
}

const productsIndexMapping = `{
  "settings": {
    "index": {
      "number_of_shards": 1,
      "number_of_replicas": 0
    }
  },
  "mappings": {
    "properties": {
      "id": { "type": "keyword" },
      "store_id": { "type": "keyword" },
      "category_id": { "type": "keyword" },
      "name": { "type": "text" },
      "slug": { "type": "keyword" },
      "sku": { "type": "keyword" },
      "description": { "type": "text" },
      "price": { "type": "integer" },
      "attributes": { "type": "object", "enabled": true },
      "image": { "type": "keyword" },
      "available": { "type": "boolean" }
    }
  }
}`
