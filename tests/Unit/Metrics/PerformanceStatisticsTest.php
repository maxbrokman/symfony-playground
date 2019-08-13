<?php declare(strict_types=1);


namespace App\Tests\Unit\Metrics;

use App\Metrics\MeasurementRange;
use App\Metrics\PerformanceMeasurement;
use App\Metrics\PerformanceStatistics;
use Cake\Chronos\Chronos;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class PerformanceStatisticsTest extends TestCase
{
    public function testBasicBag()
    {
        $statistics = new PerformanceStatistics(
            new MeasurementRange([
                new PerformanceMeasurement(Chronos::createFromDate(2019, 1, 1), 100.0),
            ]),
            100.0,
            100.0,
            100.0,
            100.0,
            []
        );

        $this->assertEquals("2019-01-01", $statistics->getDateRangeStart()->format("Y-m-d"));
        $this->assertEquals("2019-01-01", $statistics->getDateRangeEnd()->format("Y-m-d"));
        $this->assertSame(100.0, $statistics->getMaximum());
        $this->assertSame(100.0, $statistics->getMinimum());
        $this->assertSame(100.0, $statistics->getAverage());
        $this->assertSame(100.0, $statistics->getMedian());
        $this->assertCount(0, $statistics->getLowOutlierSets());
    }
}
