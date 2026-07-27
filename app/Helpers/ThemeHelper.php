<?php

use App\Services\ThemeService;

if (!function_exists('theme')) {
    /**
     * Get a theme configuration value.
     *
     * @param string|null $key Dot-notation key (e.g., 'colors.primary', 'name')
     * @param mixed $default
     * @return mixed
     */
    function theme(?string $key = null, mixed $default = null): mixed
    {
        $service = app(ThemeService::class);

        if (is_null($key)) {
            return $service;
        }

        return $service->get($key, $default);
    }
}
