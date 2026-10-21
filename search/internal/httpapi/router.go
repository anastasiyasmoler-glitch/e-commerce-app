package httpapi

import (
	"context"
	"encoding/json"
	"net/http"
	"time"
)

func NewRouter(deps Dependencies) http.Handler {
	mux := http.NewServeMux()
	mux.HandleFunc("GET /healthz", handleHealthz)
	mux.HandleFunc("GET /readyz", func(w http.ResponseWriter, r *http.Request) {
		handleReadyz(w, r, deps)
	})
	mux.HandleFunc("GET /search", func(w http.ResponseWriter, r *http.Request) {
		if deps.Search == nil {
			writeJSONError(w, http.StatusServiceUnavailable, "search is not configured")
			return
		}
		handleSearch(w, r, deps.Search)
	})
	registerDocsRoutes(mux)

	return mux
}

func handleReadyz(w http.ResponseWriter, r *http.Request, deps Dependencies) {
	if deps.Ready == nil {
		w.Header().Set("Content-Type", "text/plain; charset=utf-8")
		w.WriteHeader(http.StatusServiceUnavailable)
		_, _ = w.Write([]byte("not ready"))
		return
	}

	ctx, cancel := context.WithTimeout(r.Context(), 5*time.Second)
	defer cancel()

	if err := deps.Ready.Ready(ctx); err != nil {
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusServiceUnavailable)
		_ = json.NewEncoder(w).Encode(map[string]string{
			"message": "opensearch not ready",
			"error":   err.Error(),
		})
		return
	}

	w.Header().Set("Content-Type", "text/plain; charset=utf-8")
	w.WriteHeader(http.StatusOK)
	_, _ = w.Write([]byte("ok"))
}

func handleHealthz(w http.ResponseWriter, _ *http.Request) {
	w.Header().Set("Content-Type", "text/plain; charset=utf-8")
	w.WriteHeader(http.StatusOK)
	_, _ = w.Write([]byte("ok"))
}
