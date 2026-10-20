package httpapi

import (
	"context"
	"net/http"
	"net/http/httptest"
	"testing"

	"github.com/stretchr/testify/require"
)

type stubReady struct {
	err error
}

func (s stubReady) Ready(context.Context) error {
	return s.err
}

func TestHealthz(t *testing.T) {
	req := httptest.NewRequest(http.MethodGet, "/healthz", nil)
	rec := httptest.NewRecorder()

	NewRouter(Dependencies{}).ServeHTTP(rec, req)

	require.Equal(t, http.StatusOK, rec.Code)
	require.Equal(t, "ok", rec.Body.String())
}

func TestSearchNotImplemented(t *testing.T) {
	req := httptest.NewRequest(http.MethodGet, "/search?q=chair", nil)
	rec := httptest.NewRecorder()

	NewRouter(Dependencies{}).ServeHTTP(rec, req)

	require.Equal(t, http.StatusNotImplemented, rec.Code)
	require.Contains(t, rec.Body.String(), "not implemented")
}

func TestReadyzOk(t *testing.T) {
	req := httptest.NewRequest(http.MethodGet, "/readyz", nil)
	rec := httptest.NewRecorder()

	NewRouter(Dependencies{Ready: stubReady{}}).ServeHTTP(rec, req)
	require.Equal(t, http.StatusOK, rec.Code)
}

func TestReadyzFailsWhenOpenSearchDown(t *testing.T) {
	req := httptest.NewRequest(http.MethodGet, "/readyz", nil)
	rec := httptest.NewRecorder()

	NewRouter(Dependencies{Ready: stubReady{err: context.DeadlineExceeded}}).ServeHTTP(rec, req)
	require.Equal(t, http.StatusServiceUnavailable, rec.Code)
}
