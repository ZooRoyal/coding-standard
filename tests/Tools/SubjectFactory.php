<?php

namespace Zooroyal\CodingStandard\Tests\Tools;

use Mockery;
use Mockery\MockInterface;
use ReflectionClass;

class SubjectFactory
{
    /**
     * Builds Subject and it's constructor parameters.
     *
     * @param string $className
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
            $type = $parameter->getClass()->getName();
            $result['parameters'][$type] = Mockery::mock($type);
            $parameterInstances[] = $result['parameters'][$type];
        }

        $result['subject'] = $reflection->newInstanceArgs($parameterInstances);

        return $result;
    }
}
