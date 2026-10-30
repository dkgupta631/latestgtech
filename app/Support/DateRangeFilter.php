<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Date range presets used by the transaction lists and their Excel exports.
 * Every preset is an exact [start, end] range, so "this month" never matches
 * the same month of another year.
 */
class DateRangeFilter
{
    /**
     * @return array{0: Carbon, 1: Carbon}|null  null = no date filter
     */
    public static function range(?string $preset): ?array
    {
        $now = now();

        return match ($preset) {
            'today'      => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'yesterday'  => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
            // last 7 days including today
            '7days'      => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
            'this_week'  => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'last_week'  => [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()],
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            // NoOverflow: on 31 March "last month" is February, not March
            'last_month' => [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()],
            'this_year'  => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'last_year'  => [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()],
            default      => null,
        };
    }

    public static function apply(Builder $query, ?string $preset, string $column = 'created_at'): Builder
    {
        if ($range = self::range($preset)) {
            $query->whereBetween($column, $range);
        }

        return $query;
    }
}
