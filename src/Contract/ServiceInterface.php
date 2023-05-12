<?php

declare(strict_types=1);

namespace Service\Models\Contract;

use Service\GeoIP\Location;

interface ServiceInterface
{
    /**
     * The "booting" method of the service.
     *
     * @return void
     */
    public function boot();

    /**
     * Determine a location based off of
     * the provided IP address.
     *
     * @param string $ip
     *
     * @return Location
     */
    public function locate($ip);

    /**
     * Create a location instance from the provided attributes.
     *
     * @param array $attributes
     *
     * @return Location
     */
    public function hydrate(array $attributes = []): Location;

    /**
     * Get configuration value.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function config($key, $default = null);
}
