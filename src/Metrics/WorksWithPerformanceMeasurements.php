<?php declare(strict_types=1);


namespace App\Metrics;


use Cake\Chronos\Chronos;

trait WorksWithPerformanceMeasurements
{
    /**
     * @var PerformanceMeasurement[]
     */
    protected $measurements = [];

    /**
     * Extract the metrics (bytes per second) only for mean/min/max etc calculations
     *
     * @return float[]
     */
    protected function getMetricsOnly(): array
    {
        return array_map(function (PerformanceMeasurement $measurement) {
            return $measurement->getBytesPerSecond();
        }, $this->measurements);
    }

    /**
     * Extract the dimensions (measurement dates) only for date range calculations
     *
     * @return Chronos[]
     */
    protected function getDimensionsOnly(): array
    {
        return array_map(function (PerformanceMeasurement $measurement) {
            return $measurement->getDate();
        }, $this->measurements);
    }
}
