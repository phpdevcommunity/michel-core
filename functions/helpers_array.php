<?php

if (!function_exists('array_flatten')) {

    /**
     * @param array $array
     * @return array
     */
    function array_flatten(array $array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, array_flatten($value));
                continue;
            }
            $result[$key] = $value;
        }
        return $result;
    }
}

if (!function_exists('array_dot')) {

    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param array $array The array to flatten.
     * @param string $rootKey The base key prefix (used internally for recursion).
     * @return array The flattened array with dot notation keys.
     */
    function array_dot(array $array, string $rootKey = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $key = strval($key);
            $key = $rootKey !== '' ? ($rootKey . '.' . $key) : $key;
            if (is_array($value)) {
                $result = $result + array_dot($value, $key);
                continue;
            }
            $result[$key] = $value;
        }

        return $result;
    }
}

if (!function_exists('array_group_by')) {

    /**
     * @param array $array
     * @param string $key
     * @return array
     */
    function array_group_by(array $array, string $key): array
    {
        $result = [];
        foreach ($array as $value) {
            $group = $value;
            if (is_array( $value)) {
                $group = $value[$key];
            }elseif (is_object($value)) {
                $group = $value->$key;
            }
            $result[$group][] = $value;
        }
        return $result;
    }
}
