<?php


namespace JobBoy\Bundle\JobBoyBundle\DependencyInjection\Compiler;

use JobBoy\Bundle\JobBoyBundle\DependencyInjection\Compiler\Util\CompilerPassUtil;
use JobBoy\Process\Domain\Entity\Infrastructure\TouchCallback\HydratableProcess as TouchCallbackHydratableProcess;
use JobBoy\Process\Domain\Entity\Infrastructure\TouchCallback\Process as TouchCallbackProcess;
use JobBoy\Process\Domain\Repository\Infrastructure\Doctrine\ProcessRepository as DoctrineProcessRepository;
use JobBoy\Process\Domain\Repository\Infrastructure\Redis\ProcessRepository as RedisProcessRepository;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class RegisterProcessRepositoryPass implements CompilerPassInterface
{
    const PROCESS_REPOSITORY_SERVICE_ID = 'jobboy.process_repository.service_id';

    public function process(ContainerBuilder $container)
    {

        $this->loadDoctrineProcessRepository($container);
        $this->loadRedisProcessRepository($container);

        $this->createProcessRepositoryAlias($container);
    }


    protected function createProcessRepositoryAlias(ContainerBuilder $container)
    {
        $serviceId = $container->getParameter(self::PROCESS_REPOSITORY_SERVICE_ID);

        CompilerPassUtil::assertDefinitionImplementsInterface($container, $serviceId, ProcessRepositoryInterface::class);

        $container->setAlias(ProcessRepositoryInterface::class, new Alias($serviceId, true));
    }


    protected function loadDoctrineProcessRepository(ContainerBuilder $container)
    {

        $serviceParameter = RegisterProcessRepositoryPass::PROCESS_REPOSITORY_SERVICE_ID;

        $serviceId = $container->getParameter($serviceParameter);

        if ($serviceId !== DoctrineProcessRepository::class) {
            return;
        }

        if (!$container->hasDefinition('doctrine.dbal.default_connection')) {
            throw new \InvalidArgumentException(sprintf(
                'To use %s as ProcessRepository you need the `doctrine.dbal.default_connection` service',
                DoctrineProcessRepository::class
            ));
        }

        $container->setParameter('jobboy.process.class', TouchCallbackHydratableProcess::class);


        $locator = new FileLocator(__DIR__ . '/../../Resources/config/process_repositories');

        $loader = new YamlFileLoader($container, $locator);

        $loader->load('doctrine.yaml');
    }

    protected function loadRedisProcessRepository(ContainerBuilder $container)
    {
        $serviceParameter = RegisterProcessRepositoryPass::PROCESS_REPOSITORY_SERVICE_ID;

        $serviceId = $container->getParameter($serviceParameter);

        if ($serviceId !== RedisProcessRepository::class) {
            return;
        }


        if (!$container->hasParameter('jobboy.redis.host')) {
            throw new \InvalidArgumentException(sprintf(
                'To use %s you need to set `job_boy.redis.host` config',
                RedisProcessRepository::class
            ));
        }

        $container->setParameter('jobboy.process.class', TouchCallbackProcess::class);

        $locator = new FileLocator(__DIR__ . '/../../Resources/config/process_repositories');

        $loader = new YamlFileLoader($container, $locator);

        $loader->load('redis.yaml');
    }


}
