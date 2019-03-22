<?php
namespace Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\PHPCopyPasteDetectorAdapter;

class PHPCopyPasteDetectorCommand extends Command
{
    /** @var PHPCopyPasteDetectorAdapter */
    private $toolAdapter;

    /**
     * PHPParallelLintCommand constructor.
     *
     * @param PHPCopyPasteDetectorAdapter $toolAdapter
     */
    public function __construct(PHPCopyPasteDetectorAdapter $toolAdapter)
    {
        $this->toolAdapter = $toolAdapter;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('sca:copy-paste-detect');
        $this->setDescription('Run PHP-CPD on PHP files.');
        $this->setHelp('This tool executes PHP-CPD on a certain set of PHP files of this Project. It ignores '
            . 'files which are in directories with a .dontCopyPasteDetectPHP file. Subdirectories are ignored too.');
    }


    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->toolAdapter->writeViolationsToOutput();
    }
}
