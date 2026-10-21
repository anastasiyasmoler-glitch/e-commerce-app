package search

import (
	"context"
	"encoding/json"
	"fmt"
	"time"

	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/cache"
)

type CachingEngine struct {
	Inner Engine
	Store *cache.Store
	TTL   time.Duration
}

func (c *CachingEngine) Search(ctx context.Context, params Params) (Result, error) {
	if c.Inner == nil {
		return Result{}, fmt.Errorf("search engine is not configured")
	}
	if c.Store == nil || c.TTL <= 0 {
		return c.Inner.Search(ctx, params)
	}

	key := "search:result:" + params.CacheKey()
	if raw, err := c.Store.Get(ctx, key); err == nil && len(raw) > 0 {
		var cached Result
		if err := json.Unmarshal(raw, &cached); err == nil {
			return cached, nil
		}
	}

	result, err := c.Inner.Search(ctx, params)
	if err != nil {
		return Result{}, err
	}

	if encoded, err := json.Marshal(result); err == nil {
		_ = c.Store.Set(ctx, key, encoded, c.TTL)
	}

	return result, nil
}
