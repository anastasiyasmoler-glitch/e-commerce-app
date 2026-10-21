package search

import (
	"net/http"
	"net/url"
	"testing"

	"github.com/stretchr/testify/require"
)

func TestParseParams(t *testing.T) {
	query := url.Values{}
	query.Set("q", "desk")
	query.Set("store_id", "3")
	query.Add("category_id", "9")
	query.Set("available", "true")
	query.Set("price_min", "1000")
	query.Set("price_max", "50000")
	query.Set("attr.color", "black")
	query.Set("page", "2")
	query.Set("per_page", "10")
	query.Set("sort", "price_asc")

	req := &http.Request{URL: &url.URL{RawQuery: query.Encode()}}
	params, err := ParseParams(req)
	require.NoError(t, err)
	require.Equal(t, "desk", params.Query)
	require.Equal(t, "3", params.StoreID)
	require.Equal(t, []string{"9"}, params.CategoryIDs)
	require.NotNil(t, params.Available)
	require.True(t, *params.Available)
	require.Equal(t, 1000, *params.PriceMin)
	require.Equal(t, "black", params.Attributes["color"])
	require.Equal(t, 2, params.Page)
	require.Equal(t, SortPriceAsc, params.Sort)
}
