<?php declare(strict_types=1);


namespace App\Tests\Unit\Metrics;

use App\Metrics\PerformanceMeasurement;
use App\Metrics\PerformanceSet;
use Cake\Chronos\Chronos;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class PerformanceSetTest extends TestCase
{
    public function testDateRangeStart()
    {
        $set = new PerformanceSet([
            new PerformanceMeasurement(Chronos::createFromDate(2019, 1, 1), 50.0),
            new PerformanceMeasurement(Chronos::createFromDate(2019, 1, 2), 50.0),
            new PerformanceMeasurement(Chronos::createFromDate(2019, 1, 3), 50.0),
        ]);

        $this->assertEquals(Chronos::createFromDate(2019, 1, 1)->setTime(0, 0, 0), $set->getDateRangeStart());
    }

    public function testDateRangeEnd()
    {
        $set = new PerformanceSet([
            new PerformanceMeasurement(Chronos::createFromDate(2019, 1, 1), 50.0),
            new PerformanceMeasurement(Chronos::createFromDate(2019, 1, 2), 50.0),
            new PerformanceMeasurement(Chronos::createFromDate(2019, 1, 3), 50.0),
        ]);

        $this->assertEquals(Chronos::createFromDate(2019, 1, 3)->setTime(0, 0, 0), $set->getDateRangeEnd());
    }

    public function testAverage()
    {
        $set = new PerformanceSet([
            new PerformanceMeasurement(Chronos::now(), 50.0),
            new PerformanceMeasurement(Chronos::now(), 100.0),
            new PerformanceMeasurement(Chronos::now(), 150.0),
        ]);

        $this->assertSame(100.0, $set->getAverage());
    }

    public function testMin()
    {
        $set = new PerformanceSet([
            new PerformanceMeasurement(Chronos::now(), 50.0),
            new PerformanceMeasurement(Chronos::now(), 100.0),
            new PerformanceMeasurement(Chronos::now(), 150.0),
        ]);

        $this->assertSame(50.0, $set->getMinimum());
    }

    public function testMaximum()
    {
        $set = new PerformanceSet([
            new PerformanceMeasurement(Chronos::now(), 50.0),
            new PerformanceMeasurement(Chronos::now(), 100.0),
            new PerformanceMeasurement(Chronos::now(), 150.0),
        ]);

        $this->assertSame(150.0, $set->getMaximum());
    }

    public function testMedian()
    {
        // Odd set
        $set = new PerformanceSet([
            new PerformanceMeasurement(Chronos::now(), 50.0),
            new PerformanceMeasurement(Chronos::now(), 100.0),
            new PerformanceMeasurement(Chronos::now(), 150.0),
        ]);

        $this->assertSame(100.0, $set->getMedian());

        // Even set
        $set = new PerformanceSet([
            new PerformanceMeasurement(Chronos::now(), 50.0),
            new PerformanceMeasurement(Chronos::now(), 100.0),
            new PerformanceMeasurement(Chronos::now(), 150.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
        ]);

        $this->assertSame(125.0, $set->getMedian());

        // Unsorted even set
        $set = new PerformanceSet([
            new PerformanceMeasurement(Chronos::now(), 100.0),
            new PerformanceMeasurement(Chronos::now(), 150.0),
            new PerformanceMeasurement(Chronos::now(), 50.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
        ]);

        $this->assertSame(125.0, $set->getMedian());
    }

    public function testStandardDeviation()
    {
        $set = new PerformanceSet([
            new PerformanceMeasurement(Chronos::now(), 50.0),
            new PerformanceMeasurement(Chronos::now(), 100.0),
            new PerformanceMeasurement(Chronos::now(), 150.0),
        ]);

        $this->assertEquals(40.82482905, $set->getStandardDeviation(), null, 0.00000001);
    }

    public function testLowOutliers()
    {
        $set = new PerformanceSet([
            $outlier = new PerformanceMeasurement(Chronos::now(), 1.0),
            new PerformanceMeasurement(Chronos::now(), 1000.0),
            new PerformanceMeasurement(Chronos::now(), 1000.0),
            new PerformanceMeasurement(Chronos::now(), 1000.0),
            new PerformanceMeasurement(Chronos::now(), 1000.0),
            new PerformanceMeasurement(Chronos::now(), 1000.0),
            new PerformanceMeasurement(Chronos::now(), 1000.0),
            new PerformanceMeasurement(Chronos::now(), 1000.0),
            new PerformanceMeasurement(Chronos::now(), 1000.0),
            new PerformanceMeasurement(Chronos::now(), 1000.0),
        ]);

        $outliers = $set->getLowOutliers();
        $this->assertCount(1, $outliers);
        $this->assertSame($outlier, $outliers[0]);
    }
}
