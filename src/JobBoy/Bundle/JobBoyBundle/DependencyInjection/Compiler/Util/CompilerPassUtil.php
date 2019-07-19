<?php

namespace JobBoy\Bundle\JobBoyBundle\DependencyInjection\Compiler\Util;

use Assert\Assertion;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CompilerPassUtil
{
    public static function assertContainerHasDefinition(ContainerBuilder $container, $definitionId)
    {
        if (!$container->hasDefinition($definitionId)) {
            throw new \InvalidArgumentException(
                sprintf('Service id "%s" could not be found in container', $definitionId)
            );
        }
    }

    public static function assertDefinitionImplementsInterface(ContainerBuilder $container, $definitionId, $interface)
    {
        self::assertContainerHasDefinition($container, $definitionId);

        $definition = $container->getDefinition($definitionId);
        $definitionClass = $container->getParameterBag()->resolveValue($definition->getClass());

        $reflectionClass = new \ReflectionClass($definitionClass);

        if (!$reflectionClass->implementsInterface($interface)) {
            throw new \InvalidArgumentException(
                sprintf('Service "%s" must implement interface "%s".', $definitionClass, $interface)
            );
        }
    }

}
