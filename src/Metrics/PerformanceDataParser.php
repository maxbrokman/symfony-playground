<?php declare(strict_types=1);


namespace App\Metrics;

use Cake\Chronos\Chronos;
use InvalidArgumentException;
use RuntimeException;

class PerformanceDataParser
{
    public function parse(string $file): PerformanceSet
    {
        // Could stream this off disk but we're going to load it all into our objects anyway
        $fileContents = file_get_contents($file);
        if ($fileContents === false) {
            throw new InvalidArgumentException("Could not open $file");
        }

        $response = json_decode($fileContents, true, 512, JSON_NUMERIC_CHECK);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Could not decode contents of $file, bad json?");
        }

        // Not sure what expected format of input is but this feels not great to say ["data"][0]["metricData"]
        // Should potentially walk the set to find this element, or maybe there could be many?
        $metricData = $response["data"][0]["metricData"] ?? null;
        if (is_null($metricData)) {
            throw new InvalidArgumentException("$file does not conform to expected format (data[0].metricData)");
        }

        $measurements = [];
        foreach ($metricData as $metric) {
            // Parsing dates here will add a lot of overhead as well
            $measurements[] = new PerformanceMeasurement(Chronos::parse($metric["dtime"]), (float)$metric["metricValue"]);
        }

        return new PerformanceSet($measurements);
    }
}
