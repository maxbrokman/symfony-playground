<?php declare(strict_types=1);


namespace App\Tests\Unit\Metrics;

use App\Metrics\MeasurementRange;
use App\Metrics\PerformanceMeasurement;
use App\Metrics\PerformanceStatistics;
use Cake\Chronos\Chronos;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class PerformanceStatisticsTest extends TestCase
{
    public function testDateRangeStart()
    {
        $set = new PerformanceStatistics([
            new PerformanceMeasurement(Chronos::createFromDate(2019, 1, 1), 50.0),
            new PerformanceMeasurement(Chronos::createFromDate(2019, 1, 2), 50.0),
            new PerformanceMeasurement(Chronos::createFromDate(2019, 1, 3), 50.0),
        ]);

        $this->assertEquals(Chronos::createFromDate(2019, 1, 1)->setTime(0, 0, 0), $set->getDateRangeStart());
    }

    public function testDateRangeEnd()
    {
        $set = new PerformanceStatistics([
            new PerformanceMeasurement(Chronos::createFromDate(2019, 1, 1), 50.0),
            new PerformanceMeasurement(Chronos::createFromDate(2019, 1, 2), 50.0),
            new PerformanceMeasurement(Chronos::createFromDate(2019, 1, 3), 50.0),
        ]);

        $this->assertEquals(Chronos::createFromDate(2019, 1, 3)->setTime(0, 0, 0), $set->getDateRangeEnd());
    }

    public function testAverage()
    {
        $set = new PerformanceStatistics([
            new PerformanceMeasurement(Chronos::now(), 50.0),
            new PerformanceMeasurement(Chronos::now(), 100.0),
            new PerformanceMeasurement(Chronos::now(), 150.0),
        ]);

        $this->assertSame(100.0, $set->getAverage());
    }

    public function testMin()
    {
        $set = new PerformanceStatistics([
            new PerformanceMeasurement(Chronos::now(), 50.0),
            new PerformanceMeasurement(Chronos::now(), 100.0),
            new PerformanceMeasurement(Chronos::now(), 150.0),
        ]);

        $this->assertSame(50.0, $set->getMinimum());
    }

    public function testMaximum()
    {
        $set = new PerformanceStatistics([
            new PerformanceMeasurement(Chronos::now(), 50.0),
            new PerformanceMeasurement(Chronos::now(), 100.0),
            new PerformanceMeasurement(Chronos::now(), 150.0),
        ]);

        $this->assertSame(150.0, $set->getMaximum());
    }

    public function testMedian()
    {
        // Odd set
        $set = new PerformanceStatistics([
            new PerformanceMeasurement(Chronos::now(), 50.0),
            new PerformanceMeasurement(Chronos::now(), 100.0),
            new PerformanceMeasurement(Chronos::now(), 150.0),
        ]);

        $this->assertSame(100.0, $set->getMedian());

        // Even set
        $set = new PerformanceStatistics([
            new PerformanceMeasurement(Chronos::now(), 50.0),
            new PerformanceMeasurement(Chronos::now(), 100.0),
            new PerformanceMeasurement(Chronos::now(), 150.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
        ]);

        $this->assertSame(125.0, $set->getMedian());

        // Unsorted even set
        $set = new PerformanceStatistics([
            new PerformanceMeasurement(Chronos::now(), 100.0),
            new PerformanceMeasurement(Chronos::now(), 150.0),
            new PerformanceMeasurement(Chronos::now(), 50.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
        ]);

        $this->assertSame(125.0, $set->getMedian());
    }

    public function testStandardDeviation()
    {
        $set = new PerformanceStatistics([
            new PerformanceMeasurement(Chronos::now(), 50.0),
            new PerformanceMeasurement(Chronos::now(), 100.0),
            new PerformanceMeasurement(Chronos::now(), 150.0),
        ]);

        $this->assertEquals(40.82482905, $set->getStandardDeviation(), null, 0.00000001);
    }

    public function testLowOutliers()
    {
        $set = new PerformanceStatistics([
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

    public function testOutlierSets()
    {
        $set = new PerformanceStatistics([
            $outlier = new PerformanceMeasurement(Chronos::createFromDate(2019, 1, 1), 1.0),
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

        $outliers = $set->getLowOutlierSets();
        $this->assertCount(1, $outliers);
        $this->assertInstanceOf(MeasurementRange::class, $outliers[0]);
        $this->assertEquals("2019-01-01", $outliers[0]->getStart()->format("Y-m-d"));
        $this->assertEquals("2019-01-01", $outliers[0]->getEnd()->format("Y-m-d"));
        $this->assertCount(1, $outliers[0]->getMeasurements());
        $this->assertContains($outlier, $outliers[0]->getMeasurements());
    }

    public function testMultipleOutlierSets()
    {
        $set = new PerformanceStatistics([
            $outlier = new PerformanceMeasurement(Chronos::createFromDate(2019, 1, 1), 1.0),
            $outlier2 = new PerformanceMeasurement(Chronos::createFromDate(2019, 1, 2), 1.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            $outlier3 = new PerformanceMeasurement(Chronos::createFromDate(2025, 1, 1), 1.0),
        ]);

        $this->assertCount(3, $set->getLowOutliers());

        $outliers = $set->getLowOutlierSets();
        $this->assertCount(2, $outliers);

        $this->assertInstanceOf(MeasurementRange::class, $outliers[0]);
        $this->assertEquals("2019-01-01", $outliers[0]->getStart()->format("Y-m-d"));
        $this->assertEquals("2019-01-02", $outliers[0]->getEnd()->format("Y-m-d"));
        $this->assertCount(2, $outliers[0]->getMeasurements());
        $this->assertContains($outlier, $outliers[0]->getMeasurements());
        $this->assertContains($outlier2, $outliers[0]->getMeasurements());

        $this->assertInstanceOf(MeasurementRange::class, $outliers[1]);
        $this->assertEquals("2025-01-01", $outliers[1]->getStart()->format("Y-m-d"));
        $this->assertEquals("2025-01-01", $outliers[1]->getEnd()->format("Y-m-d"));
        $this->assertCount(1, $outliers[1]->getMeasurements());
        $this->assertContains($outlier3, $outliers[1]->getMeasurements());
    }
}
