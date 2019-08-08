<?php declare(strict_types=1);


namespace App\Metrics;

use Carbon\Carbon;
use InvalidArgumentException;
use RuntimeException;

/**
 * Holds a set of performance measurements and calculates statistics about them.
 * NB: When dealing with large data sets wrapping all the measurements up in objects
 * like this is going to add significant performance overheads and so might not be the
 * best approach, tests allow replacement of collection of PerformanceMeasurement with
 * a bare array later.
 */
class PerformanceSet
{
    const OUTLIER_FACTOR = 2;
    /**
     * @var array
     */
    private $measurements;

    /**
     * @param PerformanceMeasurement[] $measurements
     */
    public function __construct(array $measurements)
    {
        if (!count($measurements)) {
            throw new InvalidArgumentException(__CLASS__ . " requires at least one " . PerformanceMeasurement::class);
        }

        $this->measurements = $measurements;
    }

    public function getDateRangeStart(): Carbon
    {
        return array_reduce($this->getDimensionsOnly(), function (?Carbon $memo, Carbon $dimension) {
            if (is_null($memo)) {
                return $dimension;
            }

            return $dimension->min($memo);
        }, null);
    }

    public function getDateRangeEnd(): Carbon
    {
        return array_reduce($this->getDimensionsOnly(), function (?Carbon $memo, Carbon $dimension) {
            if (is_null($memo)) {
                return $dimension;
            }

            return $dimension->max($memo);
        }, null);
    }

    /**
     * Get the average (i.e mean) performance measurement from the set
     *
     * @return float performance in bytes per second
     */
    public function getAverage(): float
    {
        $sum = array_sum($this->getMetricsOnly());
        $count = count($this->measurements);

        return $sum / $count;
    }

    /**
     * Get the minimum performance measurement from the set
     *
     * @return float performance in bytes per second
     */
    public function getMinimum(): float
    {
        return min($this->getMetricsOnly());
    }

    /**
     * Get the maximum performance measurement from the set
     *
     * @return float performance in bytes per second
     */
    public function getMaximum()
    {
        return max($this->getMetricsOnly());
    }

    /**
     * Get the media performance measurement from the set
     *
     * @return float performance in bytes per second
     */
    public function getMedian()
    {
        $count = count($this->measurements);
        $middleKey = (int)floor(($count - 1) / 2);

        $set = array_values($this->getMetricsOnly());
        // Sorting here might also get ugly for very large sets
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

    /**
     * Get standard deviation of bytes per second measurements
     *
     * @return float
     */
    public function getStandardDeviation(): float
    {
        // Consider replacing with a statistics library
        $mean = $this->getAverage();
        $count = count($this->measurements);
        $sumOfSquaredDifferences = array_reduce($this->getMetricsOnly(), function (float $memo, float $bytesPerSecond) use ($mean) {
            return $memo + (($bytesPerSecond - $mean) ** 2);
        }, 0.0);
        $variance = $sumOfSquaredDifferences / $count;

        return sqrt($variance);
    }

    /**
     * Get PerformanceMeasurements with a bytesPerSecond outside OUTLIER_FACTOR * Standard Deviation of the mean.
     * Don't know if this is gonna work :)
     *
     * @return PerformanceMeasurement[]
     */
    public function getLowOutliers()
    {
        $mean = $this->getAverage();
        $std = $this->getStandardDeviation();
        $lower = $mean - (self::OUTLIER_FACTOR * $std);

        return array_values(array_filter($this->measurements, function (PerformanceMeasurement $measurement) use ($lower) {
            return $measurement->getBytesPerSecond() < $lower;
        }));
    }

    /**
     * Extract the dimensions (measurement dates) only for date range calculations
     *
     * @return Carbon[]
     */
    private function getDimensionsOnly(): array
    {
        return array_map(function (PerformanceMeasurement $measurement) {
            return $measurement->getDate();
        }, $this->measurements);
    }

    /**
     * Extract the metrics (bytes per second) only for mean/min/max etc calculations
     *
     * @return float[]
     */
    private function getMetricsOnly(): array
    {
        return array_map(function (PerformanceMeasurement $measurement) {
            return $measurement->getBytesPerSecond();
        }, $this->measurements);
    }
}
