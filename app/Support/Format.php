<?php

namespace App\Support;

/** Formatting that matches Rails' pt-BR helpers (number_to_currency, l(date)) and BigDecimal#to_s. */
class Format
{
    public const MONTHS = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];

    /** "R$ 1.234,50"; negatives as "-R$ 1.234,50". */
    public static function currency(string|int|float|null $value): string
    {
        $amount = round((float) ($value ?? 0), 2);
        $sign = $amount < 0 ? '-' : '';

        return $sign.'R$ '.number_format(abs($amount), 2, ',', '.');
    }

    public static function amountClass(string|int|float|null $value): string
    {
        return (float) ($value ?? 0) < 0 ? 'text-red-700' : 'text-emerald-700';
    }

    /** How Rails prints a BigDecimal: 150 -> "150.0", 12.50 -> "12.5". */
    public static function decimalText(string|int|float|null $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        $text = (string) $value;
        if (str_contains($text, '.')) {
            $text = rtrim(rtrim($text, '0'), '.');
        }

        return str_contains($text, '.') ? $text : $text.'.0';
    }
}
