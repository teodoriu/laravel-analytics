<?php

namespace ErlanCarreira\Analytics;

use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Illuminate\Contracts\Cache\Repository;

class AnalyticsClient
{
    /** @var BetaAnalyticsDataClient */
    protected $service;

    /** @var \Illuminate\Contracts\Cache\Repository */
    protected $cache;

    /** @var int */
    protected $cacheLifeTimeInMinutes = 0;


    public function __construct(BetaAnalyticsDataClient $service, Repository $cache)
    {
        $this->service = $service;

        $this->cache = $cache;

        $this->propertyId = config('analytics.property_id');

        $this->setCacheLifeTimeInMinutes(config('analytics.cache_lifetime_in_minutes'));
    }

    /**
     * Set the cache time.
     *
     * @param  int  $cacheLifeTimeInMinutes
     *
     * @return self
     */
    public function setCacheLifeTimeInMinutes(int $cacheLifeTimeInMinutes)
    {
        $this->cacheLifeTimeInMinutes = $cacheLifeTimeInMinutes * 60;

        return $this;
    }

    /**
     * Query the Google Analytics Service with given parameters.
     *
     * @return array|null
     */
    public function runReport($propertyId, $dateRanges, $metrics, $dimensions)
    {
        $cacheName = $this->determineCacheName(func_get_args());

        if ($this->cacheLifeTimeInMinutes === 0) {
            $this->cache->forget($cacheName);
        }

        return $this->cache->remember(
            $cacheName,
            $this->cacheLifeTimeInMinutes,
            function () use ($propertyId, $dateRanges, $metrics, $dimensions) {
                return $this->service->runReport(
                    [
                        'property'   => 'properties/'.$propertyId,
                        'dateRanges' => [$dateRanges],
                        'dimensions' => $dimensions,
                        'metrics'    => $metrics,
                    ]
                );
            }
        );
    }

    public function getAnalyticsService(): BetaAnalyticsDataClient
    {
        return $this->service;
    }

    /*
     * Determine the cache name for the set of query properties given.
     */
    protected function determineCacheName($properties): string
    {
        return 'erlancarreira.laravel-analytics.'.md5(serialize($properties));
    }
}
