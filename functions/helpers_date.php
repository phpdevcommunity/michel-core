<?php

if (!function_exists('days_between')) {

    function days_between(DateTime $datetime1, DateTime $datetime2): int
    {
        $interval = $datetime1->diff($datetime2);
        return $interval->days;
    }
}

if (!function_exists('is_leap_year')) {
    function is_leap_year(DateTime $date): bool
    {
        $year = $date->format('Y');
        return ($year % 4 === 0 && $year % 100 !== 0) || ($year % 400 === 0);
    }
}

if (!function_exists('is_weekend')) {

    /**
     * @param DateTime $date
     * @return bool
     */
    function is_weekend(DateTime $date): bool
    {
        return in_array($date->format('N'), [6, 7]);
    }
}

if (!function_exists('is_today')) {

    /**
     * @param DateTime $date
     * @return bool
     */
    function is_today(DateTime $date): bool
    {
        return $date->format('Y-m-d') === (new DateTime())->format('Y-m-d');
    }
}

if (!function_exists('is_past')) {
    function is_past(DateTime $date): bool
    {
        return (new DateTime()) > $date;
    }
}
