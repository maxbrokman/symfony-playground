<?php declare(strict_types=1);

namespace App\Command;

use App\Metrics\Megabits;
use App\Metrics\PerformanceDataParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;

/**
 * Class AppAnalyseMetricsCommand
 *
 * @package App\Command
 */
class AppAnalyseMetricsCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:analyse-metrics';

    /**
     * @var Environment
     */
    private $twig;

    public function __construct(Environment $twig)
    {
        parent::__construct();
        $this->twig = $twig;
    }

    /**
     * Configure the command.
     */
    protected function configure(): void
    {
        $this->setDescription('Analyses the metrics to generate a report.');
        $this->addOption('input', null, InputOption::VALUE_REQUIRED, 'The location of the test input');
    }

    /**
     * Detect slow-downs in the data and output them to stdout.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $filename = $input->getOption("input");

        $parser = new PerformanceDataParser();
        $set = $parser->parse($filename);

        $average = round(Megabits::fromBytes($set->getAverage()), 2);
        $min = round(Megabits::fromBytes($set->getMinimum()), 2);
        $max = round(Megabits::fromBytes($set->getMaximum()), 2);
        $median = round(Megabits::fromBytes($set->getMedian()), 2);

        $lowOutlierSets = $set->getLowOutlierSets();

        $content = $this->twig->render(
            "analysis.twig",
            compact("set", "average", "min", "max", "median", "lowOutlierSets")
        );
        $output->write($content);
    }
}
