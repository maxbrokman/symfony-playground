<?php declare(strict_types=1);


namespace App\Tests\Unit\Metrics;

use App\Util\Megabits;
use App\Metrics\PerformanceDataParser;
use App\Metrics\PerformanceMeasurement;
use App\Metrics\PerformanceStatistics;
use Cake\Chronos\Chronos;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class PerformanceDataParserTest extends TestCase
{
    public function testParse()
    {
        $filename = \realpath(__DIR__ . "/../../../resources/fixtures/1.json");
        $parser = new PerformanceDataParser();
        $set = $parser->parse($filename);

        $this->assertInstanceOf(PerformanceStatistics::class, $set);
        $this->assertEquals(
            Chronos::createFromDate(2018, 1, 29)->format("Y-m-d"),
            $set->getDateRangeStart()->format("Y-m-d")
        );
        $this->assertEquals(
            Chronos::createFromDate(2018, 2, 27)->format("Y-m-d"),
            $set->getDateRangeEnd()->format("Y-m-d")
        );

        // This tests PerformanceSet more than anything for current implementation
        $this->assertEquals(102.7, round(Megabits::fromBytes($set->getAverage()), 1));
        $this->assertEquals(101.25, round(Megabits::fromBytes($set->getMinimum()), 2));
        $this->assertEquals(104.08, round(Megabits::fromBytes($set->getMaximum()), 2));
        $this->assertEquals(102.93, round(Megabits::fromBytes($set->getMedian()), 2));
    }

    public function testParseWithOutliers()
    {
        $filename = \realpath(__DIR__ . "/../../../resources/fixtures/2.json");
        $parser = new PerformanceDataParser();
        $set = $parser->parse($filename);

        $this->assertInstanceOf(PerformanceStatistics::class, $set);
        $this->assertEquals(
            Chronos::createFromDate(2018, 1, 29)->format("Y-m-d"),
            $set->getDateRangeStart()->format("Y-m-d")
        );
        $this->assertEquals(
            Chronos::createFromDate(2018, 2, 27)->format("Y-m-d"),
            $set->getDateRangeEnd()->format("Y-m-d")
        );

        // This tests PerformanceSet more than anything for current implementation
        $this->assertEquals(95.5, round(Megabits::fromBytes($set->getAverage()), 1));
        $this->assertEquals(27.63, round(Megabits::fromBytes($set->getMinimum()), 2));
        $this->assertEquals(104.08, round(Megabits::fromBytes($set->getMaximum()), 2));
        $this->assertEquals(102.91, round(Megabits::fromBytes($set->getMedian()), 2));
        $this->assertCount(3, $set->getLowOutliers());

        $feb5Seen = false;
        $feb6Seen = false;
        $feb7Seen = false;

        foreach ($set->getLowOutliers() as $outlier) {
            $this->assertInstanceOf(PerformanceMeasurement::class, $outlier);
            switch ($outlier->getDate()->format("Y-m-d")) {
                case "2018-02-05":
                    $feb5Seen = true;
                    break;
                case "2018-02-06":
                    $feb6Seen = true;
                    break;
                case "2018-02-07":
                    $feb7Seen = true;
                    break;
            }
        }

        $this->assertTrue($feb5Seen, "2018-02-05 was not seen in outliers");
        $this->assertTrue($feb6Seen, "2018-02-06 was not seen in outliers");
        $this->assertTrue($feb7Seen, "2018-02-07 was not seen in outliers");
    }
}
