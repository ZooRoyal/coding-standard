<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Tools;

use Mockery;
use Mockery\MockInterface;
use ReflectionClass;

class SubjectFactory
{
    /**
     * Builds Subject and it's constructor parameters.
     *
     * @return array<string,object|array<MockInterface>>
     */
    public function buildSubject(string $className): array
    {
        $result = ['subject' => null];
        $parameterInstances = [];

        $reflection = new ReflectionClass($className);

        $parameters = $reflection->getConstructor()->getParameters();

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();
            $result['parameters'][(string) $type] = Mockery::mock((string) $type);
            $parameterInstances[] = $result['parameters'][(string) $type];
        }

        $result['subject'] = $reflection->newInstanceArgs($parameterInstances);

        return $result;
    }
}
