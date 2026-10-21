package search

import (
	"context"
	"log/slog"
	"time"

	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/kafka"
)

type Engine interface {
	Search(ctx context.Context, params Params) (Result, error)
}

type PerformedPublisher interface {
	PublishSearchPerformed(ctx context.Context, event kafka.SearchPerformedEvent) error
}

type Service struct {
	engine    Engine
	publisher PerformedPublisher
}

func NewService(engine Engine, publisher PerformedPublisher) *Service {
	return &Service{
		engine:    engine,
		publisher: publisher,
	}
}

func (s *Service) Search(ctx context.Context, params Params) (Result, error) {
	result, err := s.engine.Search(ctx, params)
	if err != nil {
		return Result{}, err
	}

	if s.publisher != nil {
		event := kafka.SearchPerformedEvent{
			Query:       params.Query,
			Filters:     params.FiltersMap(),
			ResultCount: result.Pagination.Total,
			Timestamp:   time.Now().UTC(),
		}
		if err := s.publisher.PublishSearchPerformed(ctx, event); err != nil {
			slog.Error("publish search.performed failed", "err", err)
		}
	}

	return result, nil
}
