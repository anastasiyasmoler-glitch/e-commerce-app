package search

import (
	"context"
	"testing"
	"time"

	"github.com/alicebob/miniredis/v2"
	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/cache"
	"github.com/stretchr/testify/require"
)

type stubEngine struct {
	calls  int
	result Result
}

func (s *stubEngine) Search(context.Context, Params) (Result, error) {
	s.calls++
	return s.result, nil
}

func TestCachingEngineReturnsCachedResultWithoutSecondEngineCall(t *testing.T) {
	mr := miniredis.RunT(t)
	store, err := cache.NewRedis(mr.Addr())
	require.NoError(t, err)

	engine := &stubEngine{
		result: Result{
			Query:      "chair",
			Pagination: Pagination{Total: 3},
		},
	}
	cached := &CachingEngine{
		Inner: engine,
		Store: store,
		TTL:   time.Minute,
	}

	params := Params{Query: "chair", Page: 1, PerPage: 20, Sort: SortRelevance}

	first, err := cached.Search(context.Background(), params)
	require.NoError(t, err)
	require.Equal(t, 3, first.Pagination.Total)
	require.Equal(t, 1, engine.calls)

	second, err := cached.Search(context.Background(), params)
	require.NoError(t, err)
	require.Equal(t, 3, second.Pagination.Total)
	require.Equal(t, 1, engine.calls)
}

func TestParamsCacheKeyIgnoresAttributeOrder(t *testing.T) {
	a := Params{
		Query:      "sofa",
		Attributes: map[string]string{"color": "black", "material": "wood"},
	}
	b := Params{
		Query:      "sofa",
		Attributes: map[string]string{"material": "wood", "color": "black"},
	}

	require.Equal(t, a.CacheKey(), b.CacheKey())
}
