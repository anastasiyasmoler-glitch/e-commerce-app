package search

import (
	"fmt"
	"net/http"
	"strconv"
	"strings"
)

const (
	defaultPage    = 1
	defaultPerPage = 20
	maxPerPage     = 100
)

type Sort string

const (
	SortRelevance Sort = "relevance"
	SortPriceAsc  Sort = "price_asc"
	SortPriceDesc Sort = "price_desc"
)

type Params struct {
	Query       string
	StoreID     string
	CategoryIDs []string
	Available   *bool
	PriceMin    *int
	PriceMax    *int
	Attributes  map[string]string
	Page        int
	PerPage     int
	Sort        Sort
}

func ParseParams(r *http.Request) (Params, error) {
	q := strings.TrimSpace(r.URL.Query().Get("q"))

	storeID := strings.TrimSpace(r.URL.Query().Get("store_id"))
	categoryIDs := r.URL.Query()["category_id"]
	if len(categoryIDs) == 0 {
		if single := strings.TrimSpace(r.URL.Query().Get("category_id")); single != "" {
			categoryIDs = []string{single}
		}
	}

	var available *bool
	if raw := strings.TrimSpace(r.URL.Query().Get("available")); raw != "" {
		parsed, err := strconv.ParseBool(raw)
		if err != nil {
			return Params{}, fmt.Errorf("invalid available filter")
		}
		available = &parsed
	}

	priceMin, err := optionalIntQuery(r, "price_min")
	if err != nil {
		return Params{}, err
	}
	priceMax, err := optionalIntQuery(r, "price_max")
	if err != nil {
		return Params{}, err
	}

	attributes := map[string]string{}
	for key, values := range r.URL.Query() {
		if !strings.HasPrefix(key, "attr.") {
			continue
		}
		code := strings.TrimPrefix(key, "attr.")
		if code == "" || len(values) == 0 {
			continue
		}
		attributes[code] = strings.TrimSpace(values[0])
	}

	page := defaultPage
	if raw := strings.TrimSpace(r.URL.Query().Get("page")); raw != "" {
		parsed, err := strconv.Atoi(raw)
		if err != nil || parsed < 1 {
			return Params{}, fmt.Errorf("invalid page")
		}
		page = parsed
	}

	perPage := defaultPerPage
	if raw := strings.TrimSpace(r.URL.Query().Get("per_page")); raw != "" {
		parsed, err := strconv.Atoi(raw)
		if err != nil || parsed < 1 {
			return Params{}, fmt.Errorf("invalid per_page")
		}
		perPage = parsed
	}
	if perPage > maxPerPage {
		perPage = maxPerPage
	}

	sort := SortRelevance
	if raw := strings.TrimSpace(r.URL.Query().Get("sort")); raw != "" {
		switch Sort(raw) {
		case SortRelevance, SortPriceAsc, SortPriceDesc:
			sort = Sort(raw)
		default:
			return Params{}, fmt.Errorf("invalid sort")
		}
	}

	return Params{
		Query:       q,
		StoreID:     storeID,
		CategoryIDs: categoryIDs,
		Available:   available,
		PriceMin:    priceMin,
		PriceMax:    priceMax,
		Attributes:  attributes,
		Page:        page,
		PerPage:     perPage,
		Sort:        sort,
	}, nil
}

func (p Params) FiltersMap() map[string]interface{} {
	filters := map[string]interface{}{}
	if p.StoreID != "" {
		filters["store_id"] = p.StoreID
	}
	if len(p.CategoryIDs) > 0 {
		filters["category_id"] = p.CategoryIDs
	}
	if p.Available != nil {
		filters["available"] = *p.Available
	}
	if p.PriceMin != nil {
		filters["price_min"] = *p.PriceMin
	}
	if p.PriceMax != nil {
		filters["price_max"] = *p.PriceMax
	}
	if len(p.Attributes) > 0 {
		filters["attributes"] = p.Attributes
	}

	return filters
}

func optionalIntQuery(r *http.Request, key string) (*int, error) {
	raw := strings.TrimSpace(r.URL.Query().Get(key))
	if raw == "" {
		return nil, nil
	}

	parsed, err := strconv.Atoi(raw)
	if err != nil {
		return nil, fmt.Errorf("invalid %s", key)
	}

	return &parsed, nil
}
