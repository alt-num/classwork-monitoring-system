<?php

namespace App\Models\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait HasCalendarYear
{
    /**
     * Scope a query to only include records from a specific calendar year.
     */
    public function scopeInCalendarYear(Builder $query, ?int $year = null): Builder
    {
        $year = $year ?? Carbon::now()->year;
        $dateField = $this->getDateFieldForYearFilter();

        return $query->whereYear($dateField, $year);
    }

    /**
     * Scope a query to only include records from the current calendar year.
     */
    public function scopeCurrentYear(Builder $query): Builder
    {
        return $this->scopeInCalendarYear($query);
    }

    /**
     * Get the date field to use for year filtering.
     */
    protected function getDateFieldForYearFilter(): string
    {
        return $this->yearFilterField ?? 'created_at';
    }
} 