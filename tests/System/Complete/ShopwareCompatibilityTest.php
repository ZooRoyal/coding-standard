<?php declare(strict_types = 1);

namespace Zooroyal\CodingStandard\Tests\System\Complete;

use PHPUnit\Framework\TestCase;
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
        $environment = TestEnvironmentInstallation::getInstance();
        $environment->addComposerJson(
            dirname(__DIR__)
            . '/fixtures/complete/shopware-composer-template.json'
        )->installComposerInstance();
    }
}
