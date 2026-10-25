<?php

namespace App\Contracts;

interface LlmClient
{
    /**
     * Turn product or query text into a vector for pgvector similarity search.
     *
     * @return list<float>
     */
    public function embed(string $text): array;

    /**
     * Generate a short answer from a prompt (chat). Must not invent catalog SKUs.
     */
    public function generate(string $prompt): string;
}
