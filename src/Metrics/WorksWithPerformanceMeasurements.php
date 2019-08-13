<?php declare(strict_types=1);


namespace App\Metrics;

use Cake\Chronos\Chronos;

trait WorksWithPerformanceMeasurements
{
    /**
     * Extract the metrics (bytes per second) only for mean/min/max etc calculations
     *
     * @param PerformanceMeasurement[] $measurements
     * @return float[]
     */
    protected function getMetricsOnly(array $measurements): array
    {
        return array_map(function (PerformanceMeasurement $measurement) {
            return $measurement->getBytesPerSecond();
        }, $measurements);
    }

    /**
     * Extract the dimensions (measurement dates) only for date range calculations
     *
     * @param array $measurements
     * @return Chronos[]
     */
    protected function getDimensionsOnly(array $measurements): array
    {
        return array_map(function (PerformanceMeasurement $measurement) {
            return $measurement->getDate();
        }, $measurements);
    }
}
