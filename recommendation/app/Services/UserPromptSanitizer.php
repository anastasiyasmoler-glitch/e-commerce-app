<?php

namespace App\Services;

class UserPromptSanitizer
{
    public function sanitizeForPrompt(string $input): string
    {
        $text = preg_replace('/\x00/u', '', $input) ?? '';
        $text = preg_replace('/[\x01-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text) ?? '';
        $text = str_replace(
            ['<<<USER>>>', '<<<END_USER>>>', '<<<SYSTEM>>>', '<<<END_SYSTEM>>>'],
            '',
            $text,
        );

        return trim($text);
    }

    public function wrapUserMessage(string $input): string
    {
        $safe = $this->sanitizeForPrompt($input);
        if ($safe === '') {
            $safe = '(empty message)';
        }

        return "<<<USER>>>\n{$safe}\n<<<END_USER>>>";
    }
}
