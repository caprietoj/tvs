<?php

namespace App\Support;

use Illuminate\Support\Collection;

class SurveyAnswerEvaluator
{
    public const CAFETERIA_RATING_POSITIVE = ['Excelente', 'Bueno', 'Buena'];
    public const SATISFACTION_POSITIVE = ['Muy satisfecho', 'Satisfecho'];
    public const YES_POSITIVE = ['Sí'];
    public const CLEANLINESS_POSITIVE = ['Limpio y ordenado'];

    public static function normalize($value): string
    {
        $value = trim((string) $value);
        $value = mb_strtolower($value, 'UTF-8');
        $value = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ü'],
            ['a', 'e', 'i', 'o', 'u', 'u'],
            $value
        );
        $value = preg_replace('/\s+/', ' ', $value);

        return rtrim($value, " .;,");
    }

    public static function isAffirmative($value): bool
    {
        $normalized = self::normalize($value);

        if ($normalized === '') {
            return false;
        }

        return $normalized === 'si'
            || str_starts_with($normalized, 'si.')
            || str_starts_with($normalized, 'si ');
    }

    public static function isPositive($value, array $positiveValues): bool
    {
        $normalized = self::normalize($value);

        if ($normalized === '') {
            return false;
        }

        if (is_numeric($normalized)) {
            return (float) $normalized >= 4;
        }

        $normalizedPositives = array_map(
            fn ($positive) => self::normalize($positive),
            $positiveValues
        );

        return in_array($normalized, $normalizedPositives, true);
    }

    public static function positivePercentage($values, array $positiveValues): float
    {
        $values = $values instanceof Collection ? $values : collect($values);

        $values = $values->filter(function ($value) {
            return $value !== null && trim((string) $value) !== '';
        });

        $total = $values->count();

        if ($total === 0) {
            return 0.0;
        }

        $positive = $values->filter(function ($value) use ($positiveValues) {
            return self::isPositive($value, $positiveValues);
        })->count();

        return round(($positive / $total) * 100, 1);
    }

    public static function usesService($row, string $service): bool
    {
        $flag = $service === 'cafeteria'
            ? ($row->uses_cafeteria ?? null)
            : ($row->uses_transport ?? null);

        if (self::isAffirmative($flag)) {
            return true;
        }

        if (trim((string) $flag) !== '') {
            return false;
        }

        $fields = $service === 'cafeteria'
            ? [
                'food_quality',
                'portion_satisfaction',
                'menu_offered',
                'menu_variety',
                'food_temperature',
                'dining_cleanliness',
                'store_service',
                'staff_treatment_cafeteria',
                'positive_aspects_cafeteria',
                'improvement_opportunities_cafeteria',
                'withdrawal_reason_cafeteria',
            ]
            : [
                'route_number',
                'punctuality',
                'vehicle_cleanliness',
                'staff_treatment_transport',
                'communication',
                'positive_aspects_transport',
                'improvement_opportunities_transport',
                'withdrawal_reason_transport',
            ];

        foreach ($fields as $field) {
            if (trim((string) ($row->{$field} ?? '')) !== '') {
                return true;
            }
        }

        return false;
    }
}
