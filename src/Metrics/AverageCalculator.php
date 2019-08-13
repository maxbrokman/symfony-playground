<?php declare(strict_types=1);


namespace App\Metrics;

class AverageCalculator
{
    use WorksWithPerformanceMeasurements;

    /**
     * @param MeasurementRange $measurementRange
     * @return float average (mean)metric from the provided range
     */
    public function calculate(MeasurementRange $measurementRange): float
    {
        $sum = array_sum($this->getMetricsOnly($measurementRange->getMeasurements()));
        $count = count($measurementRange->getMeasurements());

        return $sum / $count;
    }
}
