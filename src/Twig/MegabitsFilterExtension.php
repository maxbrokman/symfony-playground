<?php declare(strict_types=1);


namespace App\Twig;

use App\Util\Megabits;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MegabitsFilterExtension extends AbstractExtension
{
    /**
     * @return TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('megabitsFromBytes', [$this, 'formatMegabitsFromBytes']),
        ];
    }

    /** @noinspection PhpUnused */
    /**
     * @param float $bytes
     * @return string rounded representation of bytes in megabits
     */
    public function formatMegabitsFromBytes(float $bytes): string
    {
        return (string)round(Megabits::fromBytes($bytes), 2);
    }
}
