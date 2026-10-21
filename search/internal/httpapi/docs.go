package httpapi

import (
	_ "embed"
	"net/http"
	"os"
)

//go:embed swagger/index.html
var swaggerIndexHTML []byte

func registerDocsRoutes(mux *http.ServeMux) {
	mux.HandleFunc("GET /docs", serveSwaggerUI)
	mux.HandleFunc("GET /docs/", serveSwaggerUI)
	mux.HandleFunc("GET /docs/openapi.yaml", serveOpenAPISpec)
	mux.HandleFunc("GET /openapi.yaml", serveOpenAPISpec)
}

func serveSwaggerUI(w http.ResponseWriter, _ *http.Request) {
	w.Header().Set("Content-Type", "text/html; charset=utf-8")
	w.Header().Set("Cache-Control", "no-store")
	w.WriteHeader(http.StatusOK)
	_, _ = w.Write(swaggerIndexHTML)
}

func serveOpenAPISpec(w http.ResponseWriter, r *http.Request) {
	body, err := loadOpenAPISpec()
	if err != nil {
		writeJSONError(w, http.StatusInternalServerError, "openapi spec is not available")
		return
	}

	w.Header().Set("Content-Type", "application/yaml; charset=utf-8")
	w.WriteHeader(http.StatusOK)
	_, _ = w.Write(body)
}

func loadOpenAPISpec() ([]byte, error) {
	candidates := []string{
		os.Getenv("OPENAPI_PATH"),
		"/app/openapi.yaml",
		"openapi.yaml",
	}

	for _, path := range candidates {
		if path == "" {
			continue
		}
		body, err := os.ReadFile(path)
		if err == nil && len(body) > 0 {
			return body, nil
		}
	}

	return nil, os.ErrNotExist
}
