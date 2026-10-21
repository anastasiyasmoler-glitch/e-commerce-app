package httpapi

import (
	"context"

	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/search"
)

type ReadinessChecker interface {
	Ready(ctx context.Context) error
}

type Dependencies struct {
	Ready  ReadinessChecker
	Search *search.Service
}
