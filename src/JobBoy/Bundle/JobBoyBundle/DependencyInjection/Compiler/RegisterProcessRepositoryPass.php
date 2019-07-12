<?php


namespace JobBoy\Bundle\JobBoyBundle\DependencyInjection\Compiler;

use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterProcessRepositoryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $serviceParameter = 'jobboy.process_repository.service_id';

        $serviceId = $container->getParameter($serviceParameter);

        CompilerPassUtil::assertDefinitionImplementsInterface($container, $serviceId, ProcessRepositoryInterface::class);

        $container->setAlias(ProcessRepositoryInterface::class, new Alias($serviceId, true));
    }
}
