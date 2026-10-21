package httpapi

import (
	"encoding/json"
	"net/http"

	"github.com/anastasiyasmoler-glitch/e-commerce-app/search/internal/search"
)

func handleSearch(w http.ResponseWriter, r *http.Request, svc *search.Service) {
	params, err := search.ParseParams(r)
	if err != nil {
		writeJSONError(w, http.StatusBadRequest, err.Error())
		return
	}

	result, err := svc.Search(r.Context(), params)
	if err != nil {
		writeJSONError(w, http.StatusInternalServerError, "search failed")
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusOK)
	_ = json.NewEncoder(w).Encode(map[string]interface{}{
		"data": result,
	})
}

func writeJSONError(w http.ResponseWriter, status int, message string) {
	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(status)
	_ = json.NewEncoder(w).Encode(map[string]string{
		"message": message,
	})
}
