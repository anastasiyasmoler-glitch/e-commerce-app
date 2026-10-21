package config

import "strings"

func FacetAttributeKeysFromEnv() []string {
	raw := strings.TrimSpace(envOrDefault("FACET_ATTRIBUTE_KEYS", "color,material"))
	if raw == "" {
		return []string{}
	}

	parts := strings.Split(raw, ",")
	keys := make([]string, 0, len(parts))
	for _, part := range parts {
		key := strings.TrimSpace(part)
		if key != "" {
			keys = append(keys, key)
		}
	}

	return keys
}
