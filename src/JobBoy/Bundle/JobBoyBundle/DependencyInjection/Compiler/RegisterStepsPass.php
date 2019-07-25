<?php

namespace JobBoy\Bundle\JobBoyBundle\DependencyInjection\Compiler;

use JobBoy\Step\Domain\StepManager\Decorator\HasStepDataStepRegistryDecorator;
use Assert\Assertion;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterStepsPass implements CompilerPassInterface
{
    const DEFAULT_POSITION = 0;

    const REGISTRY = HasStepDataStepRegistryDecorator::class;
    const TAG = 'jobboy.step';

    public function process(ContainerBuilder $container)
    {
        if (!$container->has(self::REGISTRY)) {
            return;
        }

        $registry = $container->findDefinition(self::REGISTRY);

        $services = $container->findTaggedServiceIds(self::TAG);

        foreach ($services as $serviceId => $data) {
            Assertion::count($data, 1);
            $data = $data[0];
            if (!isset($data['position'])) {
                $data['position'] = self::DEFAULT_POSITION;
            }

            $registry->addMethodCall('addHasStepData', [new Reference($serviceId), $data['position']]);
        }
    }

}