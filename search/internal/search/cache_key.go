package search

import (
	"crypto/sha256"
	"encoding/hex"
	"encoding/json"
	"sort"
)

func (p Params) CacheKey() string {
	attrKeys := make([]string, 0, len(p.Attributes))
	for key := range p.Attributes {
		attrKeys = append(attrKeys, key)
	}
	sort.Strings(attrKeys)

	attrPairs := make([][2]string, 0, len(attrKeys))
	for _, key := range attrKeys {
		attrPairs = append(attrPairs, [2]string{key, p.Attributes[key]})
	}

	categories := append([]string(nil), p.CategoryIDs...)
	sort.Strings(categories)

	var available *bool
	if p.Available != nil {
		v := *p.Available
		available = &v
	}

	canonical := struct {
		Query       string            `json:"q"`
		StoreID     string            `json:"store_id"`
		CategoryIDs []string          `json:"category_id"`
		Available   *bool             `json:"available"`
		PriceMin    *int              `json:"price_min"`
		PriceMax    *int              `json:"price_max"`
		Attributes  [][2]string       `json:"attributes"`
		Page        int               `json:"page"`
		PerPage     int               `json:"per_page"`
		Sort        Sort              `json:"sort"`
	}{
		Query:       p.Query,
		StoreID:     p.StoreID,
		CategoryIDs: categories,
		Available:   available,
		PriceMin:    p.PriceMin,
		PriceMax:    p.PriceMax,
		Attributes:  attrPairs,
		Page:        p.Page,
		PerPage:     p.PerPage,
		Sort:        p.Sort,
	}

	raw, _ := json.Marshal(canonical)
	sum := sha256.Sum256(raw)

	return hex.EncodeToString(sum[:])
}
