package search

import (
	"context"
	"encoding/json"
	"fmt"
	"strings"

	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/opensearch"
)

type CategoryNameLookup interface {
	CategoryNames(ctx context.Context, ids []string) (map[string]string, error)
}

type OpenSearchEngine struct {
	Client             *opensearch.Client
	Categories         CategoryNameLookup
	FacetAttributeKeys []string
	FuzzySearch        bool
}

func (e *OpenSearchEngine) Search(ctx context.Context, params Params) (Result, error) {
	body, err := buildSearchBody(params, e.FacetAttributeKeys, e.FuzzySearch)
	if err != nil {
		return Result{}, err
	}

	raw, err := e.Client.SearchIndex(ctx, body)
	if err != nil {
		return Result{}, err
	}

	return parseSearchResponse(ctx, raw, params, e.FacetAttributeKeys, e.Categories)
}

func buildSearchBody(p Params, facetAttributeKeys []string, fuzzySearch bool) ([]byte, error) {
	from := (p.Page - 1) * p.PerPage
	// OpenSearch rejects from+size beyond max_result_window (default 10000) with a 400.
	// Clamp so an out-of-range page still returns a valid empty page instead of an error.
	const maxResultWindow = 10000
	if from+p.PerPage > maxResultWindow {
		from = maxResultWindow - p.PerPage
		if from < 0 {
			from = 0
		}
	}

	filters := []map[string]interface{}{}
	if p.StoreID != "" {
		filters = append(filters, map[string]interface{}{
			"term": map[string]interface{}{"store_id": p.StoreID},
		})
	}
	if len(p.CategoryIDs) > 0 {
		filters = append(filters, map[string]interface{}{
			"terms": map[string]interface{}{"category_id": p.CategoryIDs},
		})
	}
	if p.Available != nil {
		filters = append(filters, map[string]interface{}{
			"term": map[string]interface{}{"available": *p.Available},
		})
	}
	if p.PriceMin != nil || p.PriceMax != nil {
		rangeFilter := map[string]interface{}{}
		if p.PriceMin != nil {
			rangeFilter["gte"] = *p.PriceMin
		}
		if p.PriceMax != nil {
			rangeFilter["lte"] = *p.PriceMax
		}
		filters = append(filters, map[string]interface{}{
			"range": map[string]interface{}{"price": rangeFilter},
		})
	}
	for code, value := range p.Attributes {
		filters = append(filters, map[string]interface{}{
			"term": map[string]interface{}{
				fmt.Sprintf("attributes.%s", code): value,
			},
		})
	}

	var must interface{}
	if strings.TrimSpace(p.Query) == "" {
		must = map[string]interface{}{"match_all": map[string]interface{}{}}
	} else {
		multiMatch := map[string]interface{}{
			"query":  p.Query,
			"fields": []string{"name^2", "description"},
		}
		if fuzzySearch {
			multiMatch["fuzziness"] = "AUTO"
		}
		must = map[string]interface{}{
			"multi_match": multiMatch,
		}
	}

	boolQuery := map[string]interface{}{
		"must": must,
	}
	if len(filters) > 0 {
		boolQuery["filter"] = filters
	}

	sort := []map[string]interface{}{}
	switch p.Sort {
	case SortPriceAsc:
		sort = append(sort, map[string]interface{}{"price": map[string]string{"order": "asc"}})
	case SortPriceDesc:
		sort = append(sort, map[string]interface{}{"price": map[string]string{"order": "desc"}})
	default:
		sort = append(sort, map[string]interface{}{"_score": map[string]string{"order": "desc"}})
	}

	aggs := map[string]interface{}{
		"category_id": map[string]interface{}{
			"terms": map[string]interface{}{"field": "category_id", "size": 50},
		},
		"available": map[string]interface{}{
			"terms": map[string]interface{}{"field": "available", "size": 2},
		},
		"price": map[string]interface{}{
			"range": map[string]interface{}{
				"field": "price",
				"ranges": []map[string]interface{}{
					{"key": "0-5000", "to": 5000},
					{"key": "5000-10000", "from": 5000, "to": 10000},
					{"key": "10000-50000", "from": 10000, "to": 50000},
					{"key": "50000+", "from": 50000},
				},
			},
		},
	}

	for _, key := range facetAttributeKeys {
		aggs["attr_"+key] = map[string]interface{}{
			"terms": map[string]interface{}{
				"field": fmt.Sprintf("attributes.%s", key),
				"size":  20,
			},
		}
	}

	payload := map[string]interface{}{
		"from":  from,
		"size":  p.PerPage,
		"query": map[string]interface{}{"bool": boolQuery},
		"sort":  sort,
		"aggs":  aggs,
	}

	return json.Marshal(payload)
}

func parseSearchResponse(ctx context.Context, raw []byte, params Params, facetAttributeKeys []string, categories CategoryNameLookup) (Result, error) {
	var decoded struct {
		Hits struct {
			Total struct {
				Value int `json:"value"`
			} `json:"total"`
			Hits []struct {
				Source map[string]interface{} `json:"_source"`
			} `json:"hits"`
		} `json:"hits"`
		Aggregations map[string]json.RawMessage `json:"aggregations"`
	}

	if err := json.Unmarshal(raw, &decoded); err != nil {
		return Result{}, err
	}

	items := make([]Item, 0, len(decoded.Hits.Hits))
	for _, hit := range decoded.Hits.Hits {
		items = append(items, mapHit(hit.Source))
	}

	total := decoded.Hits.Total.Value
	totalPages := 0
	if params.PerPage > 0 {
		totalPages = (total + params.PerPage - 1) / params.PerPage
	}

	result := Result{
		Query: params.Query,
		Items: items,
		Facets: Facets{
			Attributes: map[string][]FacetValue{},
		},
		Pagination: Pagination{
			Page:       params.Page,
			PerPage:    params.PerPage,
			Total:      total,
			TotalPages: totalPages,
		},
	}

	if agg, ok := decoded.Aggregations["category_id"]; ok {
		result.Facets.CategoryID = parseTermsBuckets(agg)
		if categories != nil && len(result.Facets.CategoryID) > 0 {
			ids := make([]string, 0, len(result.Facets.CategoryID))
			for _, facet := range result.Facets.CategoryID {
				ids = append(ids, facet.Value)
			}
			names, err := categories.CategoryNames(ctx, ids)
			if err != nil {
				return Result{}, err
			}
			for i := range result.Facets.CategoryID {
				if label := names[result.Facets.CategoryID[i].Value]; label != "" {
					result.Facets.CategoryID[i].Label = label
				}
			}
		}
	}
	if agg, ok := decoded.Aggregations["available"]; ok {
		result.Facets.Available = parseTermsBuckets(agg)
	}
	if agg, ok := decoded.Aggregations["price"]; ok {
		result.Facets.Price = parseRangeBuckets(agg)
	}
	for _, key := range facetAttributeKeys {
		if agg, ok := decoded.Aggregations["attr_"+key]; ok {
			result.Facets.Attributes[key] = parseTermsBuckets(agg)
		}
	}

	return result, nil
}

func mapHit(source map[string]interface{}) Item {
	item := Item{
		Attributes: map[string]string{},
	}
	if v, ok := source["id"]; ok {
		item.ID = fmt.Sprint(v)
	}
	if v, ok := source["store_id"]; ok {
		item.StoreID = fmt.Sprint(v)
	}
	if v, ok := source["category_id"]; ok {
		item.CategoryID = fmt.Sprint(v)
	}
	if v, ok := source["name"].(string); ok {
		item.Name = v
	}
	if v, ok := source["slug"].(string); ok {
		item.Slug = v
	}
	if v, ok := source["sku"].(string); ok {
		item.SKU = v
	}
	if v, ok := source["description"].(string); ok {
		item.Description = v
	}
	if v, ok := source["image"].(string); ok {
		item.Image = v
	}
	if v, ok := source["price"]; ok {
		switch typed := v.(type) {
		case float64:
			item.Price = int(typed)
		case int:
			item.Price = typed
		}
	}
	if v, ok := source["available"].(bool); ok {
		item.Available = v
	}
	if attrs, ok := source["attributes"].(map[string]interface{}); ok {
		for key, value := range attrs {
			item.Attributes[key] = fmt.Sprint(value)
		}
	}

	return item
}

func parseTermsBuckets(raw json.RawMessage) []FacetValue {
	var decoded struct {
		Buckets []struct {
			Key      interface{} `json:"key"`
			DocCount int         `json:"doc_count"`
		} `json:"buckets"`
	}
	if err := json.Unmarshal(raw, &decoded); err != nil {
		return nil
	}

	out := make([]FacetValue, 0, len(decoded.Buckets))
	for _, bucket := range decoded.Buckets {
		out = append(out, FacetValue{
			Value: fmt.Sprint(bucket.Key),
			Count: bucket.DocCount,
		})
	}

	return out
}

func parseRangeBuckets(raw json.RawMessage) []FacetValue {
	var decoded struct {
		Buckets []struct {
			Key      string `json:"key"`
			DocCount int    `json:"doc_count"`
		} `json:"buckets"`
	}
	if err := json.Unmarshal(raw, &decoded); err != nil {
		return nil
	}

	out := make([]FacetValue, 0, len(decoded.Buckets))
	for _, bucket := range decoded.Buckets {
		out = append(out, FacetValue{
			Value: bucket.Key,
			Count: bucket.DocCount,
		})
	}

	return out
}
