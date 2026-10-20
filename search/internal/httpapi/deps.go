package httpapi

import "context"

type ReadinessChecker interface {
	Ready(ctx context.Context) error
}

type Dependencies struct {
	Ready ReadinessChecker
}
