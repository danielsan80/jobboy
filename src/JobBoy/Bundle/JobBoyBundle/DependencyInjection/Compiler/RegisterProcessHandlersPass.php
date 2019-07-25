<?php

namespace JobBoy\Bundle\JobBoyBundle\DependencyInjection\Compiler;

use Assert\Assertion;
use JobBoy\Process\Domain\ProcessHandler\ProcessHandlerRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterProcessHandlersPass implements CompilerPassInterface
{

    const DEFAULT_PRIORITY = 100;

    const REGISTRY = ProcessHandlerRegistry::class;
    const TAG = 'jobboy.process_handler';

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
            if (!isset($data['priority'])) {
                $data['priority'] = ProcessHandlerRegistry::DEFAULT_PRIORITY;
            }
            if (!isset($data['channel'])) {
                $data['channel'] = ProcessHandlerRegistry::DEFAULT_CHANNEL;
            }

            $registry->addMethodCall('add', [new Reference($serviceId), $data['priority'], $data['channel']]);
        }
    }

}