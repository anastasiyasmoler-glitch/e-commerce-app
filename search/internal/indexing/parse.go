package indexing

import (
	"encoding/json"
	"fmt"
	"strconv"
	"strings"
)

func requiredStringID(raw map[string]json.RawMessage, key string) (string, error) {
	value, err := requiredString(raw, key)
	if err != nil {
		return "", err
	}
	if value == "" || value == "0" {
		return "", fmt.Errorf("%s is required", key)
	}

	return value, nil
}

func optionalStringID(raw map[string]json.RawMessage, key string) string {
	value, err := optionalString(raw, key)
	if err != nil || value == "0" {
		return ""
	}

	return value
}

func requiredString(raw map[string]json.RawMessage, key string) (string, error) {
	value, err := optionalString(raw, key)
	if err != nil {
		return "", err
	}
	if strings.TrimSpace(value) == "" {
		return "", fmt.Errorf("%s is required", key)
	}

	return value, nil
}

func optionalString(raw map[string]json.RawMessage, key string) (string, error) {
	field, ok := raw[key]
	if !ok || len(field) == 0 || string(field) == "null" {
		return "", nil
	}

	var asString string
	if err := json.Unmarshal(field, &asString); err == nil {
		return asString, nil
	}

	var asNumber float64
	if err := json.Unmarshal(field, &asNumber); err == nil {
		return strconv.FormatInt(int64(asNumber), 10), nil
	}

	return "", fmt.Errorf("%s must be string or number", key)
}

func optionalInt(raw map[string]json.RawMessage, key string) int {
	field, ok := raw[key]
	if !ok || len(field) == 0 || string(field) == "null" {
		return 0
	}

	var asInt int
	if err := json.Unmarshal(field, &asInt); err == nil {
		return asInt
	}

	var asFloat float64
	if err := json.Unmarshal(field, &asFloat); err == nil {
		return int(asFloat)
	}

	return 0
}
