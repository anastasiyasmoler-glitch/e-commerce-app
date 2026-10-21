package search

type FacetValue struct {
	Value string `json:"value"`
	Count int    `json:"count"`
}

type Facets struct {
	CategoryID []FacetValue            `json:"category_id"`
	Available  []FacetValue            `json:"available"`
	Price      []FacetValue            `json:"price"`
	Attributes map[string][]FacetValue `json:"attributes"`
}

type Item struct {
	ID          string            `json:"id"`
	StoreID     string            `json:"store_id"`
	CategoryID  string            `json:"category_id"`
	Name        string            `json:"name"`
	Slug        string            `json:"slug"`
	SKU         string            `json:"sku,omitempty"`
	Description string            `json:"description,omitempty"`
	Price       int               `json:"price"`
	Attributes  map[string]string `json:"attributes,omitempty"`
	Image       string            `json:"image,omitempty"`
	Available   bool              `json:"available"`
}

type Pagination struct {
	Page       int `json:"page"`
	PerPage    int `json:"per_page"`
	Total      int `json:"total"`
	TotalPages int `json:"total_pages"`
}

type Result struct {
	Query      string     `json:"query"`
	Items      []Item     `json:"items"`
	Facets     Facets     `json:"facets"`
	Pagination Pagination `json:"pagination"`
}
