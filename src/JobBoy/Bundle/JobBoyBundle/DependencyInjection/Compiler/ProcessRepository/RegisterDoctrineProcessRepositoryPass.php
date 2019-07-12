<?php


namespace JobBoy\Bundle\JobBoyBundle\DependencyInjection\Compiler\ProcessRepository;

use JobBoy\Bundle\JobBoyBundle\DependencyInjection\Compiler\RegisterProcessRepositoryPass;
use JobBoy\Process\Domain\Entity\Infrastructure\TouchCallback\HydratableProcess;
use JobBoy\Process\Domain\Repository\Infrastructure\Doctrine\ProcessRepository;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class RegisterDoctrineProcessRepositoryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {

        $serviceParameter = RegisterProcessRepositoryPass::PROCESS_REPOSITORY_SERVICE_ID;

        $serviceId = $container->getParameter($serviceParameter);

        if ($serviceId !== ProcessRepository::class) {
            return;
        }


        if (!$container->hasDefinition('doctrine.dbal.connection')) {
            throw new \InvalidArgumentException(sprintf(
                'to use %s as ProcessRepository you need to add doctrine.dbal',
                ProcessRepository::class
            ));
        }

        $container->setParameter('jobboy.process.class', HydratableProcess::class);


        $locator = new FileLocator(__DIR__ . '/../../../Resources/config/process_repositories');

        $loader = new YamlFileLoader($container, $locator);

        $loader->load('doctrine.yaml');
    }
}
