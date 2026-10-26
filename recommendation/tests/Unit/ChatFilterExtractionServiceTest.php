<?php

namespace Tests\Unit;

use App\Contracts\LlmClient;
use App\Services\ChatFilterExtractionService;
use App\Services\ChatPromptBuilder;
use Mockery;
use Tests\TestCase;

class ChatFilterExtractionServiceTest extends TestCase
{
    public function test_parses_clarification_from_llm_json(): void
    {
        $llm = Mockery::mock(LlmClient::class);
        $llm->shouldReceive('generate')->once()->andReturn(json_encode([
            'needs_clarification' => true,
            'clarification_question' => 'What type of sofa and your budget?',
            'search_query' => null,
        ], JSON_THROW_ON_ERROR));

        $service = new ChatFilterExtractionService($llm, new ChatPromptBuilder(new \App\Services\UserPromptSanitizer));
        $result = $service->extract('something nice');

        $this->assertTrue($result['needs_clarification']);
        $this->assertSame('What type of sofa and your budget?', $result['clarification_question']);
        $this->assertNull($result['search_query']);
    }

    public function test_falls_back_to_user_message_when_json_invalid(): void
    {
        $llm = Mockery::mock(LlmClient::class);
        $llm->shouldReceive('generate')->once()->andReturn('not json');

        $service = new ChatFilterExtractionService($llm, new ChatPromptBuilder(new \App\Services\UserPromptSanitizer));
        $result = $service->extract('pink sofa');

        $this->assertFalse($result['needs_clarification']);
        $this->assertSame('pink sofa', $result['search_query']);
    }

    public function test_falls_back_when_llm_generate_fails(): void
    {
        $llm = Mockery::mock(LlmClient::class);
        $llm->shouldReceive('generate')->once()->andThrow(new \RuntimeException('Gemini down'));

        $service = new ChatFilterExtractionService($llm, new ChatPromptBuilder(new \App\Services\UserPromptSanitizer));
        $result = $service->extract('blue chair');

        $this->assertFalse($result['needs_clarification']);
        $this->assertSame('blue chair', $result['search_query']);
    }
}
