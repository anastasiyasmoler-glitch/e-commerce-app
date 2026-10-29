//go:build integration

package integration

import (
	"context"
	"testing"
	"time"

	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/cache"
	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/search"
	"github.com/stretchr/testify/require"
)

type countingEngine struct {
	calls int
}

func (c *countingEngine) Search(context.Context, search.Params) (search.Result, error) {
	c.calls++
	return search.Result{
		Query:      "redis-integration",
		Pagination: search.Pagination{Total: 2},
	}, nil
}

func TestRedisCachesSearchResults(t *testing.T) {
	addr := requireRedis(t)

	ctx := context.Background()
	store, err := cache.NewRedis(addr)
	require.NoError(t, err)
	defer store.Close()

	inner := &countingEngine{}
	engine := &search.CachingEngine{
		Inner: inner,
		Store: store,
		TTL:   time.Minute,
	}

	params := search.Params{
		Query:   "redis-integration",
		StoreID: "10",
		Page:    1,
		PerPage: 20,
		Sort:    search.SortRelevance,
	}

	first, err := engine.Search(ctx, params)
	require.NoError(t, err)
	require.Equal(t, 2, first.Pagination.Total)
	require.Equal(t, 1, inner.calls)

	second, err := engine.Search(ctx, params)
	require.NoError(t, err)
	require.Equal(t, 2, second.Pagination.Total)
	require.Equal(t, 1, inner.calls, "second search should hit Redis cache")
}
