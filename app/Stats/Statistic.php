<?php

namespace App\Stats;

use Cache;

abstract class Statistic
{
    private $doNotCache = false;

    public function fetch()
    {
        return $this->getCachedValue();
    }

    /**
     * Implement the logic to fetch the stat data here.
     *
     * @return mixed
     */
    protected function getValue()
    {
        dd('Override the getValue method');
    }

    /**
     * Override this method to specify a cache key.
     *
     * @return string
     */
    protected function getCacheKey()
    {
        dd('Override the getCacheKey method');
    }

    /**
     * Override this method to specify cache tags.
     *
     * @return array
     */
    protected function getCacheTags()
    {
        return false;
    }

    /**
     * How long (minutes) should it be cached.
     * @return int
     */
    protected function getCacheDuration()
    {
        return 60;
    }

    /**
     * Disables the cache.
     *
     * @return $this
     */
    public function noCache()
    {
        $this->doNotCache = true;

        return $this;
    }

    /**
     * Remove the cached data.
     *
     * @return $this
     */
    public function clearCache()
    {
        $this->getCacheObject()->forget($this->getCacheKey());

        return $this;
    }

    /**
     * Returns a cache or a tagged cache depending on the settings.
     *
     * @return Cache|\Illuminate\Cache\TaggedCache
     */
    private function getCacheObject()
    {
        // Check if we want to tag this value
        if ($tags = $this->getCacheTags()) {
            // Add the tags to the cache
            $cache = Cache::tags($tags);
        } else {
            $cache = Cache::driver();
        }

        return $cache;
    }

    /**
     * Calculates the value and caches it.
     *
     * @return mixed The value this statistic returns
     */
    private function getCachedValue()
    {
        if ($this->doNotCache || $this->getCacheDuration() == 0) {
            return $this->getValue();
        } else {
            return $this->getCacheObject()->remember($this->getCacheKey(), $this->getCacheDuration() * 60, function () {
                return $this->getValue();
            });
        }
    }
}
