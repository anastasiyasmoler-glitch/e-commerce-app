<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;

class ChatPromptBuilder
{
    public function __construct(
        private readonly UserPromptSanitizer $sanitizer,
    ) {}

    /**
     * @param  list<array{role: string, content: string}>  $priorHistory
     */
    public function buildFilterExtractionPrompt(string $userMessage, array $priorHistory = []): string
    {
        $sections = [
            $this->load('filter_extraction.txt'),
            $this->load('user_boundary_rules.txt'),
        ];

        $historyBlock = $this->formatHistoryBlock($priorHistory);
        if ($historyBlock !== '') {
            $sections[] = $historyBlock;
        }

        $sections[] = 'Current shopper message:';
        $sections[] = $this->sanitizer->wrapUserMessage($userMessage);

        return implode("\n\n", $sections);
    }

    /**
     * @param  list<array<string, mixed>>  $products
     * @param  list<array{role: string, content: string}>  $priorHistory
     */
    public function buildAnswerPrompt(string $userMessage, array $products, array $priorHistory = []): string
    {
        $sections = [
            $this->load('consultant_role.txt'),
            $this->load('grounding_rules.txt'),
            $this->load('backend_search_note.txt'),
            $this->load('user_boundary_rules.txt'),
        ];

        $historyBlock = $this->formatHistoryBlock($priorHistory);
        if ($historyBlock !== '') {
            $sections[] = $historyBlock;
        }

        $sections[] = 'User request:';
        $sections[] = $this->sanitizer->wrapUserMessage($userMessage);
        $sections[] = '';
        $sections[] = 'Verified catalog products (use only these; do not invent others):';
        $sections[] = $this->formatProductBlock($products);
        $sections[] = '';
        $sections[] = 'Write a short helpful answer in English (1–3 short paragraphs). Mention only products from the verified list.';

        return implode("\n", array_filter($sections, static fn (string $line): bool => $line !== ''));
    }

    public function noMatchAnswer(): string
    {
        return 'No close matches found in this store. Try refining your request (product type, color, material, size, or budget).';
    }

    public function embedUnavailableAnswer(): string
    {
        return 'Search is temporarily unavailable. Please try again in a moment or refine your product request.';
    }

    /**
     * @param  list<array<string, mixed>>  $products
     */
    public function fallbackAnswer(array $products): string
    {
        $names = array_filter(array_map(
            static fn (array $product): string => (string) ($product['name'] ?? ''),
            $products,
        ));

        if ($names === []) {
            return $this->noMatchAnswer();
        }

        return 'Here are the closest matches from our catalog: '.implode(', ', $names).'.';
    }

    /**
     * @param  list<array{role: string, content: string}>  $priorHistory
     */
    private function formatHistoryBlock(array $priorHistory): string
    {
        if ($priorHistory === []) {
            return '';
        }

        $lines = ['Prior conversation (context only):'];
        foreach ($priorHistory as $message) {
            $role = $message['role'] === 'assistant' ? 'assistant' : 'user';
            $content = $this->sanitizer->sanitizeForPrompt($message['content']);
            if ($content === '') {
                continue;
            }

            $lines[] = $role.': '.$content;
        }

        if (count($lines) === 1) {
            return '';
        }

        return implode("\n", $lines);
    }

    /**
     * @param  list<array<string, mixed>>  $products
     */
    private function formatProductBlock(array $products): string
    {
        if ($products === []) {
            return '(none)';
        }

        $lines = [];
        foreach ($products as $product) {
            $parts = [
                '- '.(string) ($product['name'] ?? 'Product'),
                'product_id='.(int) ($product['product_id'] ?? 0),
            ];

            if (isset($product['slug']) && (string) $product['slug'] !== '') {
                $parts[] = 'slug='.(string) $product['slug'];
            }

            if (array_key_exists('score', $product) && $product['score'] !== null) {
                $parts[] = 'relevance_score='.(string) $product['score'];
            }

            $lines[] = implode(', ', $parts);
        }

        return implode("\n", $lines);
    }

    private function load(string $filename): string
    {
        $path = resource_path('prompts/chat/'.$filename);

        if (! File::isFile($path)) {
            throw new RuntimeException('Missing prompt file: '.$path);
        }

        return trim(File::get($path));
    }
}
