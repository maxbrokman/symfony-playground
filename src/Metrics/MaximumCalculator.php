<?php declare(strict_types=1);


namespace App\Metrics;

class MaximumCalculator
{
    use WorksWithPerformanceMeasurements;

    /**
     * @param MeasurementRange $measurementRange
     * @return float maximum metric from range
     */
    public function calculate(MeasurementRange $measurementRange): float
    {
        return max($this->getMetricsOnly($measurementRange->getMeasurements()));
    }
}
