<?php

namespace App\utils;

class DateFormat {
    private static $dateFormatMap = [
        'hour' => '%d-%m-%Y %h:00:00',
        'day' => '%d-%m-%Y'
    ];

    public static function getFormat($dateType) {
        if(!array_key_exists($dateType, static::$dateFormatMap)) {
            return null;
        }

        return static::$dateFormatMap[$dateType];
    }

    public static function getFormatKeys() {
        return array_keys(static::$dateFormatMap);
    }
}