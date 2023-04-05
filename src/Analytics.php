<?php

namespace ErlanCarreira\Analytics;

use DateTimeInterface;
use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\Filter;
use Google\Analytics\Data\V1beta\FilterExpression;
use Google\Analytics\Data\V1beta\FilterExpressionList;
use Illuminate\Support\Traits\Macroable;

class Analytics
{
    use Macroable;

    /** @var \ErlanCarreira\Analytics\AnalyticsClient */
    protected $client;

    /** @var string */
    protected $propertyId;

    private $dateRanges       = [];
    private $metrics          = [];
    private $dimensions       = [];
    private $orderBys         = [];
    private $dimensionFilters = [];
    private $report;

    /**
     * @param  \ErlanCarreira\Analytics\AnalyticsClient  $client
     * @param  string  $propertyId
     */
    public function __construct(AnalyticsClient $client, string $propertyId)
    {
        $this->client = $client;

        $this->propertyId = $propertyId;

        
    }

   

    /**
     * @param  string  $propertyId
     *
     * @return $this
     */
    public function setPropertyId(string $propertyId)
    {
        $this->propertyId = $propertyId;

        return $this;
    }

    public function getPropertyId()
    {
        return $this->propertyId;
    }

    public function setDateRanges(DateTimeInterface $startDate, DateTimeInterface $endDate)
    {
        $this->dateRanges[] = new DateRange(
            [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date'   => $endDate->format('Y-m-d'),
            ]
        );

        return $this;
    }

    public function setMetrics(array $metrics = [])
    {
        $this->metrics = [];

        if (count($metrics) > 0) {
            foreach ($metrics as $metric):
                $this->metrics[] = new Metric(
                    [
                        'name' => $metric,
                    ]
                );
            endforeach;
        }

        return $this;
    }

    public function setDimensions(array $dimensions = [])
    {
        $this->dimensions = [];

        if (count($dimensions) > 0) {
            foreach ($dimensions as $dimension):
                $this->dimensions[] = new Dimension(
                    [
                        'name' => $dimension,
                    ]
                );
            endforeach;
        }

        return $this;
    }

    
    public function setDimensionFilter($dimensionFilters = [])
    {
        $this->dimensionFilters = new FilterExpression($dimensionFilters);
        return $this;
    }

    public function setOrderBys(array $orderBys = [])
    {
        $this->orderBys = [];
        
        if (count($orderBys) > 0) {
            foreach ($orderBys as $orderBy):
                
                $this->orderBys[] = new OrderBy($orderBy);

            endforeach; 
        }

        return $this;
    }

    public function checkCompatibility() 
    {

    }

    /**
     * Call the query method on the authenticated client.
     *
     */
    public function runReport()
    {
        $this->report = $this->client->runReport(
            $this->propertyId,
            $this->dateRanges,
            $this->metrics,
            $this->dimensions,
            $this->orderBys,
            $this->dimensionFilters,
        );

        return $this;
    }

    public function raw()
    {
        return $this->report;
    }

    public function toData() {
        
        $data = [];

        $metricHeaders    = collect($this->report->getMetricHeaders() ?? [])->map(
            function ($value) {
                return $value->getName();
            }
        );

        $dimensionHeaders = collect($this->report->getDimensionHeaders() ?? [])->map(
            function ($value) {
                return $value->getName();
            }
        );

        $headers = $dimensionHeaders->merge($metricHeaders);

        $rows    = $this->report->getRows();

        //"id" => uniqid(rand()), 
        foreach ($rows as $index => $row):

            $metricsDimensions = $this
                ->getValues($row->getDimensionValues())
                ->merge($this->getValues($row->getMetricValues()));
            
            foreach ($metricsDimensions as $idx => $row):
                
                array_push($data, [
                        "id"    => uniqid(rand()),
                        "title" => $headers[$idx], 
                        "label" => $headers[$idx],
                        "value" => $row 
                    ]
                );

            endforeach;

        endforeach;

        return $data;
    }

    public function tableArray() {
        
        $data = [];

        $metricHeaders    = collect($this->report->getMetricHeaders() ?? [])->map(
            function ($value) {
                return $value->getName();
            }
        );

        $dimensionHeaders = collect($this->report->getDimensionHeaders() ?? [])->map(
            function ($value) {
                return $value->getName();
            }
        );

        $headers       = $dimensionHeaders->merge($metricHeaders);

        $rows          = $this->report->getRows();

        $rowCount      = $this->report->getRowCount();

        $totals        = $this->report->getTotals();

        foreach ($rows as $index => $row):

            $metricsDimensions = $this
                ->getValues($row->getDimensionValues())
                ->merge($this->getValues($row->getMetricValues()));
            
            $array = $metricsDimensions->map( function( $item, $idx ) use ($headers) {
                return [ $headers[$idx] => $item ];
            })->collapse();

            $data['rows'][] = [...$array->toArray(), "id" => uniqid(rand())];    
        
        endforeach;

        foreach ($totals as $key => $row):

            $metricsDimensions = $this
                ->getValues($row->getDimensionValues())
                ->merge($this->getValues($row->getMetricValues()));
           
               
            $dimensionHeader = $dimensionHeaders->get($key);

            $array = $metricsDimensions->map( function( $item, $idx ) use ($headers, $data, $dimensionHeader) {
                
                $value = '';

                $key   = $headers[$idx]; 

                $value = $item;
                
                if ($dimensionHeader === $key && $item === 'RESERVED_TOTAL') {

                    $dataCollection = collect($data['rows'] ?? [])->pluck($dimensionHeader);

                    $value = $dataCollection->toArray();
                }

                return [ $key => $value ];

            })->collapse();

            $data['totals'] = $array->toArray();
            
        endforeach;
       
        return $data;
    }

    public function toArray()
    {
        $data = [];
        
        foreach ($this->report->getRows() as $row):
            
            if (count($this->dimensions) === 1):
                $data["rows"][$row->getDimensionValues()[0]->getValue()] = collect($row->getMetricValues() ?? [])->map(
                    function ($value) {
                        return $value->getValue();
                    }
                );
            else:
                $dimensions = collect($row->getDimensionValues() ?? [])->map(
                    function ($value) {
                        return $value->getValue();
                    }
                );

                $metrics    = collect($row->getMetricValues() ?? [])->map(
                    function ($value) {
                        return $value->getValue();
                    }
                );

                // $orderBys   = collect($row->getOrderBysValues() ?? [])->map(
                //     function ($value) {
                //         return $value->getValue();
                //     }
                // );

                $data["rows"][] = $dimensions->merge($metrics);
            endif;
        endforeach;

        $metricsteste  = [];

        $metricHeaders = $this->report->getMetricHeaders();

        foreach ($this->report->getTotals() as $key => $item):

            
            $metrics = $this->getValues($item->getMetricValues());
            $totalItem = [];

            foreach ($metrics as $key => $item):
                $data["totals"][$metricHeaders[$key]->getName()] = $item;
            endforeach;
            
        endforeach;

        return $data;
    }

    public function getValues($items) {
        return collect($items ?? [])->map(
            function ($value) {
                return $value->getValue();   
            }
        );
    }

    public function toCollection()
    {
        return collect($this->toArray());
    }

    public function getAnalyticsService(): BetaAnalyticsDataClient
    {
        return $this->client->getAnalyticsService();
    }
}
