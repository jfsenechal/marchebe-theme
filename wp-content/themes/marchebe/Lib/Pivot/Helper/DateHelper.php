<?php

namespace AcMarche\Theme\Lib\Pivot\Helper;

class DateHelper
{
    public static function convertStringToDateTime(string $dateString, string $format = "d/m/Y"): \DateTimeInterface
    {
        return Carbon::createFromFormat($format, $dateString)->toDateTime();
    }
}