<?php


namespace JobBoy\Bundle\JobBoyBundle\DependencyInjection\Compiler\ProcessRepository;

use JobBoy\Bundle\JobBoyBundle\DependencyInjection\Compiler\RegisterProcessRepositoryPass;
use JobBoy\Process\Domain\Entity\Infrastructure\TouchCallback\Process;
use JobBoy\Process\Domain\Repository\Infrastructure\Redis\ProcessRepository;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class RegisterRedisProcessRepositoryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {

        $serviceParameter = RegisterProcessRepositoryPass::PROCESS_REPOSITORY_SERVICE_ID;

        $serviceId = $container->getParameter($serviceParameter);

        if ($serviceId !== ProcessRepository::class) {
            return;
        }


        if (!$container->hasDefinition('jobboy.redis.host')) {
            throw new \InvalidArgumentException(sprintf(
                'to use %s you need to set `jobboy.redis.host` parameters',
                ProcessRepository::class
            ));
        }

        $container->setParameter('jobboy.process.class', Process::class);

        $locator = new FileLocator(__DIR__ . '/../Resources/config/process_repositories');

        $loader = new YamlFileLoader($container, $locator);

        $loader->load('redis.yaml');
    }
}
