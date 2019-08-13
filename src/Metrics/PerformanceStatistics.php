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
        return (new AverageCalculator())->calculate($this->range);
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
        return (new MaximumCalculator())->calculate($this->range);
    }

    /**
     * Get the median performance measurement from the set
     *
     * @return float performance in bytes per second
     */
    public function getMedian()
    {
        return (new MedianCalculator())->calculate($this->range);
    }

    /**
     * Arrange low outliers into sets over continuous periods for presentation in results
     *
     * @return MeasurementRange[]
     */
    public function getLowOutlierSets(): array
    {
        return (new LowOutlierIdentifier())->identifyLowOutliers($this->range);
    }
}
