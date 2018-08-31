<?php
namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;

class PHPCodeSnifferAdapter implements FixerSupportInterface
{
    /** @var OutputInterface */
    private $output;
    /** @var string[] */
    private $commands;
    /** @var Environment */
    private $environment;
    /** @var GenericCommandRunner */
    private $genericCommandRunner;
    /** @var string */
    private $stopword;
    /** @var string */
    private $filter;

    /**
     * PHPCodeSnifferAdapter constructor.
     *
     * @param Environment          $environment
     * @param OutputInterface      $output
     * @param GenericCommandRunner $genericCommandRunner
     */
    public function __construct(
        Environment $environment,
        OutputInterface $output,
        GenericCommandRunner $genericCommandRunner
    ) {
        $this->environment          = $environment;
        $this->output               = $output;
        $this->genericCommandRunner = $genericCommandRunner;

        $phpCodeSnifferConfig = $environment->getPackageDirectory() . '/src/config/phpcs/ZooroyalDefault/ruleset.xml';
        $rootDirectory        = $environment->getRootDirectory();

        $this->stopword = '.dontSniffPHP';
        $this->filter   = '.php';

        $sniffWhitelistCommand = 'php ' . $rootDirectory . '/vendor/bin/phpcs -s --extensions=php --standard='
            . $phpCodeSnifferConfig . ' %1$s';
        $cbfWhitelistCommand   = 'php ' . $rootDirectory . '/vendor/bin/phpcbf --extensions=php --standard='
            . $phpCodeSnifferConfig . ' %1$s';
        $sniffBlacklistCommand = 'php ' . $rootDirectory . '/vendor/bin/phpcs -s --extensions=php --standard='
            . $phpCodeSnifferConfig . ' --ignore=%1$s ' . $rootDirectory;
        $cbfBlacklistCommand   = 'php ' . $rootDirectory . '/vendor/bin/phpcbf --extensions=php --standard='
            . $phpCodeSnifferConfig . ' --ignore=%1$s ' . $rootDirectory;

        $this->commands = [
            'PHPCSWL'  => $sniffWhitelistCommand,
            'PHPCBFWL' => $cbfWhitelistCommand,
            'PHPCSBL'  => $sniffBlacklistCommand,
            'PHPCBFBL' => $cbfBlacklistCommand,
        ];
    }

    /**
     * Search for violations by using PHPCS and write finds to screen.
     *
     * @param string $targetBranch
     * @param bool   $processIsolation
     *
     * @return int|null
     */
    public function writeViolationsToOutput($targetBranch = '', $processIsolation = false)
    {
        $tool        = 'PHPCS';
        $prefix      = $tool . ': ';
        $fullMessage = $prefix . 'Running full check';
        $diffMessage = $prefix . 'Running check on diff';

        $exitCode = $this->runTool($targetBranch, $processIsolation, $fullMessage, $tool, $diffMessage);

        return $exitCode;
    }

    /**
     * Tries to fix violations by calling PHPCBF.
     *
     * @param string $targetBranch
     * @param bool   $processIsolation
     *
     * @return int|null
     */
    public function fixViolations($targetBranch = '', $processIsolation = false)
    {
        $tool        = 'PHPCBF';
        $prefix      = $tool . ': ';
        $fullMessage = $prefix . 'Fix all Files';
        $diffMessage = $prefix . 'Fix Files in diff';

        $exitCode = $this->runTool($targetBranch, $processIsolation, $fullMessage, $tool, $diffMessage);

        return $exitCode;
    }

    /**
     * Runs either PHPCS or PHPCBF with tool specific settings.
     *
     * @param string $targetBranch
     * @param bool   $processIsolation
     * @param string $fullMessage
     * @param string $tool
     * @param string $diffMessage
     *
     * @return int|null
     */
    private function runTool($targetBranch, $processIsolation, $fullMessage, $tool, $diffMessage)
    {
        if ($targetBranch === '' || $this->environment->isLocalBranchEqualTo('origin/master')) {
            $this->output->writeln($fullMessage, OutputInterface::VERBOSITY_NORMAL);
            $command  = $this->commands[$tool . 'BL'];
            $exitCode = $this->genericCommandRunner->runBlacklistCommand(
                $command,
                $this->stopword,
                '',
                ',',
                true
            );
        } else {
            $this->output->writeln($diffMessage, OutputInterface::VERBOSITY_NORMAL);
            $command  = $this->commands[$tool . 'WL'];
            $exitCode = $this->genericCommandRunner->runWhitelistCommand(
                $command,
                $targetBranch,
                $this->stopword,
                $this->filter,
                $processIsolation,
                ' '
            );
        }

        return $exitCode;
    }

}
