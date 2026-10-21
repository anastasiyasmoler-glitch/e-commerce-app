package opensearch

import (
	"context"
	"net/http"
	"net/http/httptest"
	"testing"

	"github.com/stretchr/testify/require"
)

func TestReadyAcceptsYellowCluster(t *testing.T) {
	srv := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		require.Equal(t, "/_cluster/health", r.URL.Path)
		w.Header().Set("Content-Type", "application/json")
		_, _ = w.Write([]byte(`{"status":"yellow"}`))
	}))
	defer srv.Close()

	client := NewClient(srv.URL)
	require.NoError(t, client.Ready(context.Background()))
}

func TestEnsureProductsIndexCreatesWhenMissing(t *testing.T) {
	var created bool
	srv := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		switch {
		case r.Method == http.MethodHead && r.URL.Path == "/products":
			http.NotFound(w, r)
		case r.Method == http.MethodPut && r.URL.Path == "/products":
			created = true
			w.WriteHeader(http.StatusCreated)
		default:
			http.NotFound(w, r)
		}
	}))
	defer srv.Close()

	client := NewClient(srv.URL)
	require.NoError(t, client.EnsureProductsIndex(context.Background()))
	require.True(t, created)
}

func TestUpsertProduct(t *testing.T) {
	srv := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		require.Equal(t, http.MethodPut, r.Method)
		require.Equal(t, "/products/_doc/42", r.URL.Path)
		w.WriteHeader(http.StatusCreated)
	}))
	defer srv.Close()

	client := NewClient(srv.URL)
	err := client.UpsertProduct(context.Background(), ProductDocument{
		ID:        "42",
		StoreID:   "1",
		Name:      "Desk",
		Price:     1000,
		Available: true,
	})
	require.NoError(t, err)
}
