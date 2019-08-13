<?php declare(strict_types=1);

namespace App\Command;

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
    /**
     * @var PerformanceDataParser
     */
    private $parser;

    /**
     * @param Environment $twig
     * @param PerformanceDataParser $parser
     */
    public function __construct(Environment $twig, PerformanceDataParser $parser)
    {
        parent::__construct();
        $this->twig = $twig;
        $this->parser = $parser;
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

        $set = $this->parser->parse($filename);

        $content = $this->twig->render(
            "analysis.twig",
            compact("set")
        );
        $output->write($content);
    }
}
