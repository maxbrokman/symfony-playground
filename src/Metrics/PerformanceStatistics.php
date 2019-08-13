<?php declare(strict_types=1);


namespace App\Metrics;

use Cake\Chronos\Chronos;
use InvalidArgumentException;
use RuntimeException;

/**
 * Holds a set of performance measurements and calculates statistics about them.
 */
class PerformanceStatistics
{
    use WorksWithPerformanceMeasurements;

    const OUTLIER_FACTOR = 2;

    /**
     * @var PerformanceMeasurement[]
     */
    private $measurements;

    /**
     * @var MeasurementRange
     */
    private $range;

    /**
     * @param PerformanceMeasurement[] $measurements
     */
    public function __construct(array $measurements)
    {
        if (!count($measurements)) {
            throw new InvalidArgumentException(__CLASS__ . " requires at least one " . PerformanceMeasurement::class);
        }

        $this->measurements = $measurements;
        $this->range = new MeasurementRange($measurements);
    }

    public function getDateRangeStart(): Chronos
    {
        return $this->range->getStart();
    }

    public function getDateRangeEnd(): Chronos
    {
        return $this->range->getEnd();
    }

    /**
     * Get the average (i.e mean) performance measurement from the set
     *
     * @return float performance in bytes per second
     */
    public function getAverage(): float
    {
        $sum = array_sum($this->getMetricsOnly($this->measurements));
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
        return (new MinimumCalculator())->calculate($this->range);
    }

    /**
     * Get the maximum performance measurement from the set
     *
     * @return float performance in bytes per second
     */
    public function getMaximum()
    {
        return max($this->getMetricsOnly($this->measurements));
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

        $set = array_values($this->getMetricsOnly($this->measurements));
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
        $sumOfSquaredDifferences = array_reduce($this->getMetricsOnly($this->measurements), function (float $memo, float $bytesPerSecond) use ($mean) {
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
     * Arrange low outliers into sets over continuous periods for presentation in results
     *
     * @return MeasurementRange[]
     */
    public function getLowOutlierSets(): array
    {
        $outliers = $this->getLowOutliers();
        if (!count($outliers)) {
            return [];
        }

        if (count($outliers) === 1) {
            return [new MeasurementRange($outliers)];
        }

        // Sort here to deal with unsorted metric sets
        $sorted = usort($outliers, function (PerformanceMeasurement $a, PerformanceMeasurement $b) {
            if ($a->getDate()->eq($b->getDate())) {
                return 0;
            }

            return $a->getDate()->lt($b->getDate()) ? -1 : 1;
        });
        if ($sorted === false) {
            throw new RuntimeException("Could not sort outliers");
        }

        $first = $outliers[0];
        $previous = $outliers[0];
        $set = [$outliers[0]];
        $outlierSets = [];

        // Start at 1 as we already handled 0
        for ($i = 1; $i < count($outliers); $i++) {
            if ($previous->getDate()->addDay()->eq($outliers[$i]->getDate())) {
                // We are continuous
                $set[] = $outliers[$i];
                $previous = $outliers[$i];
                continue;
            }

            // We are not continuous
            $outlierSets[] = new MeasurementRange($set);
            $first = $outliers[$i];
            $previous = $outliers[$i];
            $set = [$outliers[$i]];
        }

        // Finish off after the loop quits
        $outlierSets[] = new MeasurementRange($set);

        return $outlierSets;
    }
}
