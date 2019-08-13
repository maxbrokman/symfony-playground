<?php declare(strict_types=1);


namespace App\Metrics;


class MinimumCalculator
{
    use WorksWithPerformanceMeasurements;

    /**
     * @param MeasurementRange $measurementRange
     * @return float minimum metric from the provided range
     */
    public function calculate(MeasurementRange $measurementRange): float
    {
        return min($this->getMetricsOnly($measurementRange->getMeasurements()));
    }
}
