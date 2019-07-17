<?php

namespace JobBoy\Bundle\JobBoyBundle\DependencyInjection\Compiler;

use JobBoy\Process\Domain\Event\EventBusInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterEventListenersPass implements CompilerPassInterface
{
    const EVENT_BUS_ID = EventBusInterface::class;
    const TAG = 'jobboy.process.event_listener';

    public function process(ContainerBuilder $container)
    {
        if (!$container->has(self::EVENT_BUS_ID)) {
            return;
        }

        $eventBus = $container->findDefinition(self::EVENT_BUS_ID);

        $services = $container->findTaggedServiceIds(self::TAG);

        foreach ($services as $serviceId => $data) {
            $eventBus->addMethodCall('subscribe', [new Reference($serviceId)]);
        }
    }
}