<?php


namespace JobBoy\Bundle\JobBoyBundle\DependencyInjection\Compiler;

use Assert\Assertion;
use JobBoy\Bundle\JobBoyBundle\DependencyInjection\Compiler\Util\CompilerPassUtil;
use JobBoy\Process\Domain\Entity\Infrastructure\TouchCallback\HydratableProcess as TouchCallbackHydratableProcess;
use JobBoy\Process\Domain\Entity\Infrastructure\TouchCallback\Process as TouchCallbackProcess;
use JobBoy\Process\Domain\Entity\Process;
use JobBoy\Process\Domain\Repository\Infrastructure\Doctrine\ProcessRepository as DoctrineProcessRepository;
use JobBoy\Process\Domain\Repository\Infrastructure\InMemory\ProcessRepository as InMemoryProcessRepository;
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
    const PROCESS_CLASS = 'jobboy.process.class';

    public function process(ContainerBuilder $container)
    {

        $this->loadInMemoryProcessRepository($container);
        $this->loadDoctrineProcessRepository($container);
        $this->loadRedisProcessRepository($container);

        $this->setProcessClassIfMissing($container);

        $this->createProcessRepositoryAlias($container);
    }


    protected function loadInMemoryProcessRepository(ContainerBuilder $container)
    {
        $serviceId = $container->getParameter(self::PROCESS_REPOSITORY_SERVICE_ID);

        if ($serviceId === 'in_memory') {
            $container->setParameter(self::PROCESS_REPOSITORY_SERVICE_ID, InMemoryProcessRepository::class);
            $serviceId = $container->getParameter(self::PROCESS_REPOSITORY_SERVICE_ID);
        }

        if ($serviceId !== InMemoryProcessRepository::class) {
            return;
        }

        $this->setProcessClass($container, Process::class);
    }


    protected function loadDoctrineProcessRepository(ContainerBuilder $container)
    {

        $serviceId = $container->getParameter(self::PROCESS_REPOSITORY_SERVICE_ID);

        if ($serviceId === 'doctrine') {
            $container->setParameter(self::PROCESS_REPOSITORY_SERVICE_ID, DoctrineProcessRepository::class);
            $serviceId = $container->getParameter(self::PROCESS_REPOSITORY_SERVICE_ID);
        }

        if ($serviceId !== DoctrineProcessRepository::class) {
            return;
        }

        if (!$container->hasDefinition('doctrine.dbal.default_connection')) {
            throw new \InvalidArgumentException(sprintf(
                'To use %s as ProcessRepository you need the `doctrine.dbal.default_connection` service',
                DoctrineProcessRepository::class
            ));
        }

        $this->setProcessClass($container, TouchCallbackHydratableProcess::class);

        $locator = new FileLocator(__DIR__ . '/../../Resources/config/process_repositories');

        $loader = new YamlFileLoader($container, $locator);

        $loader->load('doctrine.yaml');
    }

    protected function loadRedisProcessRepository(ContainerBuilder $container)
    {
        $serviceParameter = RegisterProcessRepositoryPass::PROCESS_REPOSITORY_SERVICE_ID;

        $serviceId = $container->getParameter($serviceParameter);

        if ($serviceId === 'redis') {
            $container->setParameter(self::PROCESS_REPOSITORY_SERVICE_ID, RedisProcessRepository::class);
            $serviceId = $container->getParameter(self::PROCESS_REPOSITORY_SERVICE_ID);
        }

        if ($serviceId !== RedisProcessRepository::class) {
            return;
        }


        if (!$container->hasParameter('jobboy.process_repository.redis.host')
            && !$container->hasParameter('jobboy.process_repository.redis.port')
        ) {
            throw new \InvalidArgumentException(sprintf(
                'To use %s you need to set `job_boy.redis.host` config',
                RedisProcessRepository::class
            ));
        }


        $this->setProcessClass($container, TouchCallbackProcess::class);

        $locator = new FileLocator(__DIR__ . '/../../Resources/config/process_repositories');

        $loader = new YamlFileLoader($container, $locator);

        $loader->load('redis.yaml');
    }

    protected function setProcessClassIfMissing(ContainerBuilder $container)
    {
        $this->setProcessClass($container, Process::class);
    }


    protected function createProcessRepositoryAlias(ContainerBuilder $container)
    {
        $serviceId = $container->getParameter(self::PROCESS_REPOSITORY_SERVICE_ID);

        CompilerPassUtil::assertDefinitionImplementsInterface($container, $serviceId, ProcessRepositoryInterface::class);

        $container->setAlias(ProcessRepositoryInterface::class, new Alias($serviceId, true));
    }



    protected function setProcessClass(ContainerBuilder $container, string $processClass)
    {
        if (!$container->hasParameter(self::PROCESS_CLASS) || !$container->getParameter(self::PROCESS_CLASS)) {
            $container->setParameter(self::PROCESS_CLASS, $processClass);
            return;
        }

        Assertion::subclassOf($container->getParameter(self::PROCESS_CLASS), $processClass);
    }


}
