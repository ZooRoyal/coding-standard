<?php

namespace Zooroyal\CodingStandard\Plugin;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    /** @var Process */
    private $process;

    /** @var Composer */
    private $composer;

    /** @var IOInterface */
    private $inputOutput;

    /**
     * Returns an array of event names this subscriber wants to listen to.
     * The array keys are event names and the value can be:
     * * The method name to call (priority defaults to 0)
     * * An array composed of the method name to call and the priority
     * * An array of arrays composed of the method names to call and respective
     *   priorities, or 0 if unset
     * For instance:
     * * array('eventName' => 'methodName')
     * * array('eventName' => array('methodName', $priority))
     * * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => [
                ['npmInstall', 0],
            ],
            ScriptEvents::POST_UPDATE_CMD => [
                ['npmInstall', 0],
            ],
        ];
    }

    /**
     * Apply plugin modifications to Composer
     *
     * @param Composer    $composer
     * @param IOInterface $inputOutput
     *
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @throws \RuntimeException
     */
    public function activate(Composer $composer, IOInterface $inputOutput)
    {
        $this->composer = $composer;
        $this->inputOutput = $inputOutput;

        $packageDirectory = $this->composer->getConfig()->get('vendor-dir')
            . DIRECTORY_SEPARATOR . 'zooroyal' . DIRECTORY_SEPARATOR . 'coding-standard';
        $this->process = new Process('npm install --prefix ' . $packageDirectory);
        $this->process->setTimeout(300);
    }

    /**
     * This method is for testing purposes only
     *
     * @param Process $process
     */
    public function overwriteProcess(Process $process)
    {
        $this->process = $process;
    }

    /**
     * Calls NPM on the command line to install package.json into vendor directory
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     *
     * @throws LogicException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @throws ProcessFailedException
     */
    public function npmInstall()
    {
        $inputOutput = $this->inputOutput;

        if ($inputOutput->isVerbose()) {
            $inputOutput->write(sprintf('<info>%s</info>', 'Installing NPM-Packages for Coding-Standard'));
        }

        if ($inputOutput->isVeryVerbose()) {
            $inputOutput->write(sprintf('Executed Command: <info>%s</info>', $this->process->getCommandLine()));
        }

        $inputOutput->write('<info>NPM install</info> for zooroyal/coding-standard:');
        $this->process->run(
            function ($type, $buffer) use ($inputOutput) {
                $inputOutput->write($buffer);
            }
        );

        $this->process->wait();

        $inputOutput->write('<info>NPM packages installed</info> for zooroyal/coding-standard');
    }
}
