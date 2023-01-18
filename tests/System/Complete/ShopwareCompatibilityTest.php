<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\System\Complete;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Zooroyal\CodingStandard\Tests\Tools\TestEnvironmentInstallation;

class ShopwareCompatibilityTest extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        TestEnvironmentInstallation::getInstance()->removeInstallation();
    }

    /**
     * @test
     * @Large
     *
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     * @coversNothing
     */
    public function installingCodingStandardInShopwareContext(): void
    {
        $filesystem = new Filesystem();
        $environment = TestEnvironmentInstallation::getInstance();
        $environment->addComposerJson(
            dirname(__DIR__)
            . '/fixtures/complete/shopware-composer-template.json',
        )->installComposerInstance();

        $environmentDirectory = $environment->getInstallationPath();

        $fixtureDirectory = dirname(__DIR__) . '/fixtures';

        $filesystem->copy($fixtureDirectory . '/complete/GoodPhp.php', $environmentDirectory . '/GoodPhp.php');

        $process = new Process(
            [$environmentDirectory . '/vendor/bin/coding-standard', 'sca:all'],
            $environmentDirectory,
        );

        $exitCode = $process->mustRun()->getExitCode();

        self::assertEquals(0, $exitCode);
    }
}
