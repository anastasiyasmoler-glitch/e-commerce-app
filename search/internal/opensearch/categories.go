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

const categoriesIndex = "categories"

type CategoryDocument struct {
	ID       string `json:"id"`
	StoreID  string `json:"store_id"`
	ParentID string `json:"parent_id,omitempty"`
	Name     string `json:"name"`
	Slug     string `json:"slug"`
}

func (c *Client) EnsureCategoriesIndex(ctx context.Context) error {
	head, err := http.NewRequestWithContext(ctx, http.MethodHead, c.baseURL+"/"+categoriesIndex, nil)
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
		return fmt.Errorf("categories index head: status %d", headResp.StatusCode)
	}

	req, err := http.NewRequestWithContext(ctx, http.MethodPut, c.baseURL+"/"+categoriesIndex, strings.NewReader(categoriesIndexMapping))
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
		return fmt.Errorf("create categories index: status %d: %s", resp.StatusCode, strings.TrimSpace(string(body)))
	}

	return nil
}

func (c *Client) UpsertCategory(ctx context.Context, doc CategoryDocument) error {
	if doc.ID == "" {
		return fmt.Errorf("category id is required")
	}

	body, err := json.Marshal(doc)
	if err != nil {
		return err
	}

	url := fmt.Sprintf("%s/%s/_doc/%s", c.baseURL, categoriesIndex, doc.ID)
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
		return fmt.Errorf("upsert category: status %d: %s", resp.StatusCode, strings.TrimSpace(string(raw)))
	}

	return nil
}

func (c *Client) DeleteCategory(ctx context.Context, categoryID string) error {
	if categoryID == "" {
		return fmt.Errorf("category id is required")
	}

	url := fmt.Sprintf("%s/%s/_doc/%s", c.baseURL, categoriesIndex, categoryID)
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
		return fmt.Errorf("delete category: status %d: %s", resp.StatusCode, strings.TrimSpace(string(raw)))
	}

	return nil
}

// CategoryNames returns display names for category IDs (missing IDs are omitted).
func (c *Client) CategoryNames(ctx context.Context, ids []string) (map[string]string, error) {
	unique := make([]string, 0, len(ids))
	seen := make(map[string]struct{}, len(ids))
	for _, id := range ids {
		id = strings.TrimSpace(id)
		if id == "" {
			continue
		}
		if _, ok := seen[id]; ok {
			continue
		}
		seen[id] = struct{}{}
		unique = append(unique, id)
	}

	if len(unique) == 0 {
		return map[string]string{}, nil
	}

	payload, err := json.Marshal(map[string]interface{}{"ids": unique})
	if err != nil {
		return nil, err
	}

	req, err := http.NewRequestWithContext(ctx, http.MethodPost, c.baseURL+"/"+categoriesIndex+"/_mget", bytes.NewReader(payload))
	if err != nil {
		return nil, err
	}
	req.Header.Set("Content-Type", "application/json")

	resp, err := c.httpClient.Do(req)
	if err != nil {
		return nil, err
	}
	defer resp.Body.Close()

	raw, err := io.ReadAll(io.LimitReader(resp.Body, 1<<20))
	if err != nil {
		return nil, err
	}

	if resp.StatusCode != http.StatusOK {
		return nil, fmt.Errorf("category mget: status %d: %s", resp.StatusCode, strings.TrimSpace(string(raw)))
	}

	var decoded struct {
		Docs []struct {
			Found  bool `json:"found"`
			Source struct {
				Name string `json:"name"`
			} `json:"_source"`
			ID string `json:"_id"`
		} `json:"docs"`
	}
	if err := json.Unmarshal(raw, &decoded); err != nil {
		return nil, err
	}

	out := make(map[string]string, len(decoded.Docs))
	for _, doc := range decoded.Docs {
		if !doc.Found || strings.TrimSpace(doc.Source.Name) == "" {
			continue
		}
		out[doc.ID] = doc.Source.Name
	}

	return out, nil
}

const categoriesIndexMapping = `{
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
      "parent_id": { "type": "keyword" },
      "name": { "type": "keyword" },
      "slug": { "type": "keyword" }
    }
  }
}`
