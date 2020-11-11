<?php

namespace Zooroyal\CodingStandard\Tests\Tools;

use http\Exception\RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

class TestEnvironmentInstaller
{
    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $tempDir;

    /** @var string */
    private $composerJsonPath;
    /** @var string */
    private $composerPath;

    public function __construct()
    {
        $this->fileSystem = new Filesystem();

        $dirname = random_int(74, 93485798397);
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $dirname;

        $this->composerPath = dirname(__DIR__,);
    }

    /**
     * @param string $composerTemplatePath
     */
    public function addComposerJson(string $composerTemplatePath): void
    {
        if (!is_file($composerTemplatePath)) {
            throw new RuntimeException($composerTemplatePath . ' is not a valid path.', 1605083728);
        }
        $this->composerJsonPath = $composerTemplatePath;
    }

    public function getComposerJson(): string
    {
        if (!isset($this->composerJsonPath)) {
            throw new RuntimeException('Please set a valid path by using addComposerJson', 1605083729);
        }

        return $this->composerJsonPath;
    }

    public function installComposerInstance():void
    {
        $this->fileSystem->mkdir($this->tempDir);
        $composerTemplate = json_decode(file_get_contents($this->getComposerJson), true);
        $composerTemplate['repositories'][0]['url'] = $this->composerPath;
        $renderedComposerFile = json_encode($composerTemplate);
        file_put_contents($this->tempDir . DIRECTORY_SEPARATOR . 'composer.json', $renderedComposerFile);

        (new Process(['composer', 'install'], $this->tempDir))->mustRun();
        $this->fileSystem->remove($this->tempDir . '/vendor/zooroyal/coding-standard/node_modules');
        (new Process(['npm', 'install', 'vendor/zooroyal/coding-standard'], $this->tempDir))->mustRun();
    }

}
