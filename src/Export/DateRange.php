<?php

namespace WisoExport\Export;

class DateRange
{
    public static function isValid($from, $to): bool
    {
        foreach ([$from, $to] as $value) {
            if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value)) {
                return false;
            }
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
            if (!$date || $date->format('Y-m-d') !== $value || substr($value, 0, 4) === '0000') {
                return false;
            }
        }
        return $from <= $to;
    }
}
