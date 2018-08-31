<?php
namespace Zooroyal\CodingStandard\CommandLine\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Factories\BlacklistFactory;
use Zooroyal\CodingStandard\CommandLine\FileFinders\AllCheckableFileFinder;
use Zooroyal\CodingStandard\CommandLine\FileFinders\DiffCheckableFileFinder;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;

class FindFilesToCheckCommand extends Command
{
    /** @var DiffCheckableFileFinder */
    private $diffCheckableFileFinder;
    /** @var BlacklistFactory */
    private $blacklistFactory;
    /** @var AllCheckableFileFinder */
    private $allCheckableFileFinder;
    /** @var Environment */
    private $environment;

    /**
     * FindFilesToCheckCommand constructor.
     *
     * @param DiffCheckableFileFinder $diffCheckableFileFinder
     * @param BlacklistFactory        $blacklistFactory
     * @param AllCheckableFileFinder  $allCheckableFileFinder
     * @param Environment             $environment
     */
    public function __construct(
        DiffCheckableFileFinder $diffCheckableFileFinder,
        BlacklistFactory $blacklistFactory,
        AllCheckableFileFinder $allCheckableFileFinder,
        Environment $environment
    ) {
        $this->diffCheckableFileFinder = $diffCheckableFileFinder;
        $this->blacklistFactory        = $blacklistFactory;
        $this->allCheckableFileFinder  = $allCheckableFileFinder;
        $this->environment             = $environment;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('find-files');
        $this->setDescription('Finds files for code style checks.');
        $this->setHelp('This tool finds files, which should be considered for code style checks.');
        $this->setDefinition($this->buildInputDefinition());
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $stopword     = $input->getOption('stopword');
        $filter       = $input->getOption('filter');
        $targetBranch = $input->getOption('target');
        $exclusive    = $input->getOption('exclusionList');

        if ($exclusive === true) {
            $result = $this->blacklistFactory->build($stopword);
        } elseif (empty($targetBranch) || $this->environment->isLocalBranchEqualTo('origin/master')) {
            $result = $this->allCheckableFileFinder->findFiles($filter, $stopword)->getFiles();
        } else {
            $result = $this->diffCheckableFileFinder->findFiles($filter, $stopword, $targetBranch)->getFiles();
        }

        $output->writeln(implode("\n", array_values($result)));
    }

    /**
     * Builds InputDefinition for Command
     *
     * @return InputDefinition
     */
    private function buildInputDefinition()
    {
        return new InputDefinition(
            [
                new InputOption(
                    'target',
                    't',
                    InputOption::VALUE_OPTIONAL,
                    'Finds PHP-Files which have changed since the current branch parted from the target branch '
                    . 'only. If no branch is set Coding-Standard tries to find the parent branch by automagic. '
                        . 'The Value, if set, has to be a commit-ish.',
                    ''
                ),
                new InputOption(
                    'stopword',
                    's',
                    InputOption::VALUE_REQUIRED,
                    'Name of the file which triggers the exclusion of the path',
                    ''
                ),
                new InputOption(
                    'filter',
                    'f',
                    InputOption::VALUE_REQUIRED,
                    'Filters the Filename. For example .php for PHP-Files',
                    ''
                ),
                new InputOption(
                    'exclusionList',
                    'e',
                    InputOption::VALUE_NONE,
                    'Gathers list of directories which should be excluded'
                ),
            ]
        );
    }
}
