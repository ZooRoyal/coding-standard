<?php

namespace Zooroyal\CodingStandard\Tests\Tools;

use BadMethodCallException;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class TestEnvironmentInstallation
{
    private static TestEnvironmentInstallation $instance;
    private Filesystem $filesystem;
    private string $installationPath;
    private string $composerJsonPath;
    private string $composerPath;
    private bool $isInstalled = false;

    /**
     * The Constructor is private because this is a Singleton.
     */
    private function __construct()
    {
        $this->filesystem = new Filesystem();

        $dirname = random_int(74, 93485798397);
        $this->installationPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $dirname;

        $this->composerPath = dirname(__DIR__, 2);
    }

    /**
     * Add the path of the composer-template.json to build your test environment.
     *
     * @param string $composerTemplatePath
     *
     * @return TestEnvironmentInstallation
     *
     * @throws RuntimeException
     * @throws BadMethodCallException
     */
    public function addComposerJson(string $composerTemplatePath): TestEnvironmentInstallation
    {
        if (!is_file($composerTemplatePath)) {
            throw new RuntimeException($composerTemplatePath . ' is not a valid path.', 1605083728);
        }
        if (isset($this->composerJsonPath)) {
            throw new BadMethodCallException('Composer.json is already set', 1605084542);
        }

        $this->composerJsonPath = $composerTemplatePath;

        return $this;
    }

    /**
     * Get the path of the current composer-template.json which will be used.
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function getComposerJson(): string
    {
        if (!isset($this->composerJsonPath)) {
            throw new RuntimeException('Please set a valid path by using addComposerJson', 1605083729);
        }

        return $this->composerJsonPath;
    }

    public function isInstalled(): bool
    {
        return $this->isInstalled;
    }

    public function getInstallationPath(): string
    {
        return $this->installationPath;
    }

    /**
     * Actually install the test environment.
     *
     * @return $this
     */
    public function installComposerInstance(): TestEnvironmentInstallation
    {
        $this->filesystem->mkdir($this->installationPath);
        $composerTemplate = json_decode(file_get_contents($this->getComposerJson()), true);
        $composerTemplate['repositories'][0]['url'] = $this->composerPath;
        $renderedComposerFile = json_encode($composerTemplate);
        file_put_contents($this->installationPath . DIRECTORY_SEPARATOR . 'composer.json', $renderedComposerFile);

        (new Process(['composer', 'install'], $this->installationPath))->mustRun();
        $this->filesystem->remove($this->installationPath . '/vendor/zooroyal/coding-standard/node_modules');
        (new Process(['npm', 'install', 'vendor/zooroyal/coding-standard'], $this->installationPath))->mustRun();
        $this->isInstalled = true;

        return $this;
    }

    /**
     * Removes the test environment completely
     *
     * @return $this
     */
    public function removeInstallation(): TestEnvironmentInstallation
    {
        $this->filesystem->remove($this->installationPath);
        $this->isInstalled = false;

        return $this;
    }

    /**
     * Because of the lack of dependency injection in PHPUnit I present to you the Singleton AntiPattern.
     *
     * @return TestEnvironmentInstallation
     */
    public static function getInstance(): TestEnvironmentInstallation
    {
        if (!isset(self::$instance)) {
            self::$instance = new TestEnvironmentInstallation();
        }
        return self::$instance;
    }

}
