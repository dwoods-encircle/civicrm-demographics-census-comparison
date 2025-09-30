<?php

namespace Civicrm\DemographicsCensusComparison\Settings;

/**
 * Enumeration of supported frequency units for entity evaluation.
 */
final class FrequencyUnit
{
    public const DAYS = 'days';
    public const WEEKS = 'weeks';
    public const MONTHS = 'months';
    public const YEARS = 'years';

    /**
     * @return string[]
     */
    public static function all(): array
    {
        return [
            self::DAYS,
            self::WEEKS,
            self::MONTHS,
            self::YEARS,
        ];
    }

    public static function isValid(string $unit): bool
    {
        return in_array(strtolower($unit), self::all(), true);
    }

    public static function label(string $unit): string
    {
        switch (strtolower($unit)) {
            case self::DAYS:
                return 'Days';
            case self::WEEKS:
                return 'Weeks';
            case self::MONTHS:
                return 'Months';
            case self::YEARS:
                return 'Years';
            default:
                throw new \InvalidArgumentException(sprintf('Unsupported frequency unit "%s"', $unit));
        }
    }
}
