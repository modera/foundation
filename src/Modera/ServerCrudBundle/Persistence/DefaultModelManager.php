<?php

namespace Modera\ServerCrudBundle\Persistence;

/**
 * @copyright 2013 Modera Foundation
 */
class DefaultModelManager implements ModelManagerInterface
{
    private function underscorizeWord(string $word): string
    {
        $result = \strtolower($word[0]);
        for ($i = 1; $i < \strlen($word); ++$i) {
            $char = $word[$i];

            if (\strtoupper($char) === $char) {
                $result .= '_';
            }

            $result .= \strtolower($char);
        }

        return $result;
    }

    public function generateModelIdFromEntityClass(string $entityClass): string
    {
        $result = [];

        foreach (\explode('\\', $entityClass) as $segment) {
            if ('Entity' === $segment) {
                continue;
            }

            $result[] = $this->underscorizeWord($segment);
        }

        return \implode('.', $result);
    }

    public function generateEntityClassFromModelId(string $modelId): string
    {
        $result = [];

        // modera.admin_generator.foo => Modera\AdminGenerator\Entity\Foo
        foreach (\explode('.', $modelId) as $i => $segment) {
            if (2 === $i) {
                $result[] = 'Entity';
            }

            $explodedSegment = \explode('_', $segment);
            $explodedSegment = \array_map(function ($v) {
                return \ucfirst($v);
            }, $explodedSegment);

            $segment = \implode('', $explodedSegment);
            if (1 == $i) {
                $segment .= 'Bundle';
            }

            $result[] = $segment;
        }

        return \implode('\\', $result);
    }
}
