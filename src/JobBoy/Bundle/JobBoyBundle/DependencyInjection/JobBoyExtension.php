<?php

namespace JobBoy\Bundle\JobBoyBundle\DependencyInjection;

use JobBoy\Process\Domain\Repository\Infrastructure\Redis\RedisUtil;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class JobBoyExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {

        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        $this->readProcessRepository($config, $container);
        $this->readProcessClass($config, $container);
        $this->readRedis($config, $container);
        $this->loadServices($container);


    }

    protected function readProcessRepository(array $config, ContainerBuilder $container)
    {
        $container->setParameter('jobboy.process_repository.service_id', $config['process_repository']);
    }


    protected function readProcessClass(array $config, ContainerBuilder $container)
    {
        if (isset($config['process_class'])) {
            $container->setParameter('jobboy.process.class', $config['process_class']);
        }
    }


    protected function readRedis(array $config, ContainerBuilder $container)
    {
        if (isset($config['redis']['host'])) {
            $container->setParameter('jobboy.process_repository.redis.host', $config['redis']['host']);
            if (isset($config['redis']['port'])) {
                $container->setParameter('jobboy.process_repository.redis.port', $config['redis']['port']);
            } else {
                $container->setParameter('jobboy.process_repository.redis.port', RedisUtil::DEFAULT_PORT);
            }
        }
    }

    protected function loadServices(ContainerBuilder $container)
    {

        $locator = new FileLocator(__DIR__ . '/../Resources/config');

        $loader = new DirectoryLoader($container, $locator);
        $resolver = new LoaderResolver([
            new YamlFileLoader($container, $locator),
            $loader,
        ]);
        $loader->setResolver($resolver);

        $loader->load('services');
    }

}