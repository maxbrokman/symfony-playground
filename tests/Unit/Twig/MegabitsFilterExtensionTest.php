<?php declare(strict_types=1);


namespace App\Tests\Unit\Twig;

use App\Twig\MegabitsFilterExtension;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Twig\TwigFilter;

class MegabitsFilterExtensionTest extends TestCase
{
    public function testReturnsFilter()
    {
        $extension = new MegabitsFilterExtension();
        $filters = $extension->getFilters();

        $this->assertCount(1, $filters);
        $this->assertInstanceOf(TwigFilter::class, $filters[0]);
    }

    public function testConvertsToMegabits()
    {
        $extension = new MegabitsFilterExtension();

        // Expected behaviour is to drop trailing 0s
        $this->assertSame("0", $extension->formatMegabitsFromBytes(1.0));
        $this->assertSame("0.8", $extension->formatMegabitsFromBytes(100000.0));
        $this->assertSame("1.24", $extension->formatMegabitsFromBytes(155000.0));
    }
}
