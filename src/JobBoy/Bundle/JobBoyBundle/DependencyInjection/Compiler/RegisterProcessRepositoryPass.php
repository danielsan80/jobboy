<?php


namespace JobBoy\Bundle\JobBoyBundle\DependencyInjection\Compiler;

use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterProcessRepositoryPass implements CompilerPassInterface
{
    const PROCESS_REPOSITORY_SERVICE_ID = 'jobboy.process_repository.service_id';

    public function process(ContainerBuilder $container)
    {

        $serviceId = $container->getParameter(self::PROCESS_REPOSITORY_SERVICE_ID);

        CompilerPassUtil::assertDefinitionImplementsInterface($container, $serviceId, ProcessRepositoryInterface::class);

        $container->setAlias(ProcessRepositoryInterface::class, new Alias($serviceId, true));
    }
}
