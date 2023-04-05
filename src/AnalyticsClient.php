<?php

namespace ErlanCarreira\Analytics;

use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Illuminate\Contracts\Cache\Repository;
use Google\Analytics\Data\V1beta\MetricAggregation;
use Illuminate\Support\Facades\Http;


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

    public function checkCompatibility($request) 
    {
      
        $response = Http::post("https://analyticsdata.googleapis.com/v1beta/properties/{$this->propertyId}:checkCompatibility", $request);  
        return $response;
    }

    /**
     * Query the Google Analytics Service with given parameters.
     *
     * @return array|null
     */
    public function runReport($propertyId, $dateRanges, $metrics, $dimensions, $orderBys, $dimensionFilter)
    {
        $cacheName = $this->determineCacheName(func_get_args());

        if ($this->cacheLifeTimeInMinutes === 0) {
            $this->cache->forget($cacheName);
        }

        return $this->cache->remember(
            $cacheName,
            $this->cacheLifeTimeInMinutes,
            function () use ($propertyId, $dateRanges, $metrics, $dimensions, $orderBys, $dimensionFilter) {
                 
                $data =  [
                    'property'        => 'properties/'.$propertyId,
                    'dateRanges'      => $dateRanges,
                    'dimensions'      => $dimensions,
                    'metrics'         => $metrics,
                    'orderBys'        => $orderBys,
                    'dimensionFilter' => $dimensionFilter,
                    'metricAggregations' => [ // Não remover
                        MetricAggregation::TOTAL,
                    ],
                
                ];

                $request = collect($data ?? [])->reduce( function($acc, $item, $key) {
                    
                    if (is_array($item) && count($item) > 0 || $item) { 
                        $acc[$key] = $item; 
                    }

                    return $acc;
                     
                }, []);

                return $this->service->runReport($request);
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
        return 'erlancarreira.laravel-analyticsV1.'.md5(serialize($properties));
    }
}
