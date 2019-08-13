<?php declare(strict_types=1);


namespace App\Metrics;

use RuntimeException;

class MedianCalculator
{
    use WorksWithPerformanceMeasurements;

    /**
     * @param MeasurementRange $measurementRange
     * @return float media metric from the range
     */
    public function calculate(MeasurementRange $measurementRange): float
    {
        $count = count($measurementRange->getMeasurements());
        $middleKey = (int)floor(($count - 1) / 2);

        $set = array_values($this->getMetricsOnly($measurementRange->getMeasurements()));
        // Sorting here might get ugly for very large sets
        $sorted = sort($set);
        if (!$sorted) {
            throw new RuntimeException("Could not sort metrics");
        }

        // Odd number of elements in set, can use middle
        if ($count % 2 !== 0) {
            return $set[$middleKey];
        }

        // Even number of elements in set, take mean of two neighbouring elements
        return ($set[$middleKey] + $set[$middleKey + 1]) / 2;
    }
}
