<?php


namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

class PHPStanAdapter extends AbstractBlackAndWhitelistAdapter implements ToolAdapterInterface
{

    /** @var string */
    protected $blacklistToken = '.dontStanPHP';
    /** @var string */
    protected $filter = '.php';
    /** @var string */
    protected $blacklistGlue = ' ';
    /** @var string */
    protected $whitelistGlue = ' ';

    protected function init()
    {
        $rootDirectory = $this->environment->getRootDirectory();
        $phpstanConfig = $this->environment->getPackageDirectory() . '/config/phpstan/phpstan.neon';
        $this->commands['PHPStanBL'] = 'php ' . $rootDirectory . '/vendor/bin/phpstan -q analyse -c '.
            $phpstanConfig. ' '. $rootDirectory;
        $this->commands['PHPStanWL'] = 'php ' . $rootDirectory . '/vendor/bin/phpstan -q analyse %1$s';
    }

    /**
     * {@inheritdoc}
     */
    public function writeViolationsToOutput($targetBranch = '', bool $processIsolation = false)
    {
        $toolShortName = 'PHPStan';
        $prefix = $toolShortName . ' : ';
        $fullMessage = $prefix . 'Running full check';
        $diffMessage = $prefix . 'Running check on diff';

        return $this->runTool($targetBranch, $processIsolation, $fullMessage, $toolShortName, $diffMessage);
    }

}
