<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

class ExportReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }

    /** Start of the period; defaults to the first day of this month. */
    public function from(): CarbonImmutable
    {
        return $this->validated('from')
            ? CarbonImmutable::parse($this->validated('from'))->startOfDay()
            : CarbonImmutable::now()->startOfMonth();
    }

    /** End of the period; defaults to today. */
    public function to(): CarbonImmutable
    {
        return $this->validated('to')
            ? CarbonImmutable::parse($this->validated('to'))->endOfDay()
            : CarbonImmutable::now()->endOfDay();
    }
}
