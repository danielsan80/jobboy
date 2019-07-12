<?php

namespace JobBoy\Bundle\JobBoyBundle\DependencyInjection;

use JobBoy\Process\Domain\Entity\Process;
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

        $configs = $this->processConfiguration($configuration, $configs);

        $container->setParameter('jobboy.process_repository.service_id', $configs['process_repository']);

        if (isset($configs['process_class'])) {
            $container->setParameter('jobboy.process.class', $configs['process_class']);
        } else {
            if (!$container->hasParameter('jobboy.process.class')) {
                $container->setParameter('jobboy.process.class', Process::class);
            }
        }

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