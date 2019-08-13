<?php declare(strict_types=1);


namespace App\Metrics;

use RuntimeException;

class LowOutlierIdentifier
{
    use WorksWithPerformanceMeasurements;

    /**
     * Outliers identified as being outsider this many standard deviations of the mean
     */
    const OUTLIER_FACTOR = 2;

    /**
     * Identifies low outliers within the range (outliers defined as outisde OUTLIER_FACTOR std deviations of the set's
     * mean) and groups them into continuous ranges
     *
     * @param MeasurementRange $measurementRange
     * @return MeasurementRange[] the grouped ranges
     */
    public function identifyLowOutliers(MeasurementRange $measurementRange): array
    {
        return $this->groupOutliers($measurementRange);
    }

    /**
     * @param MeasurementRange $measurementRange
     * @return MeasurementRange[] low outliers grouped into continuous ranges
     */
    private function groupOutliers(MeasurementRange $measurementRange): array
    {
        $outliers = $this->getOutliers($measurementRange);
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

    /**
     * @param MeasurementRange $measurementRange
     * @return PerformanceMeasurement[]
     */
    private function getOutliers(MeasurementRange $measurementRange): array
    {
        $mean = (new AverageCalculator())->calculate($measurementRange);
        $std = $this->calculateStandardDeviation($measurementRange, $mean);
        $lower = $mean - (self::OUTLIER_FACTOR * $std);

        return array_values(array_filter($measurementRange->getMeasurements(), function (PerformanceMeasurement $measurement) use ($lower) {
            return $measurement->getBytesPerSecond() < $lower;
        }));
    }

    /**
     * Get standard deviation of provided metrics
     *
     * @param MeasurementRange $measurementRange
     * @return float the standard deviation
     */
    private function calculateStandardDeviation(MeasurementRange $measurementRange, float $mean): float
    {
        // Consider replacing with a statistics library
        $count = count($measurementRange->getMeasurements());
        $sumOfSquaredDifferences = array_reduce($this->getMetricsOnly($measurementRange->getMeasurements()), function (float $memo, float $bytesPerSecond) use ($mean) {
            return $memo + (($bytesPerSecond - $mean) ** 2);
        }, 0.0);
        $variance = $sumOfSquaredDifferences / $count;

        return sqrt($variance);
    }
}
