<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

class AnalyticsPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    public function period(): array
    {
        $to = $this->filled('to')
            ? CarbonImmutable::parse((string) $this->input('to'), 'UTC')->endOfDay()
            : CarbonImmutable::now('UTC')->endOfDay();

        $from = $this->filled('from')
            ? CarbonImmutable::parse((string) $this->input('from'), 'UTC')->startOfDay()
            : $to->subDays(30)->startOfDay();

        return [$from, $to];
    }
}
