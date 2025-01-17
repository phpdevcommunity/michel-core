<?php

if (!function_exists('filepath_join')) {

    /**
     * @param ...$paths
     * @return string
     */
    function filepath_join(...$paths): string
    {
        $cleanedPaths = [];
        foreach ($paths as $path) {
            $path = trim($path);
            $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
            if (empty($path)) {
                continue;
            }

            $path = rtrim($path, DIRECTORY_SEPARATOR);
            $cleanedPaths[] = $path;
        }

        return implode(DIRECTORY_SEPARATOR, $cleanedPaths);
    }
}



