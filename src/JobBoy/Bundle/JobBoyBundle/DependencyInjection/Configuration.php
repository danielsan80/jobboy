<?php

namespace JobBoy\Bundle\JobBoyBundle\DependencyInjection;

use JobBoy\Process\Domain\Entity\Process;
use JobBoy\Process\Domain\Repository\Infrastructure\InMemory\ProcessRepository;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration definition.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('job_boy');

        if (\method_exists($treeBuilder, 'getRootNode')){
            $rootNode = $treeBuilder->getRootNode();
        }
        else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('job_boy');
        }

        $rootNode
            ->children()
                ->scalarNode('process_repository')
                    ->defaultValue(ProcessRepository::class)
                    ->info('a service definition id implementing JobBoy\Process\Domain\Repository\ProcessRepositoryInterface')
                ->end()
                ->scalarNode('process_class')
                    ->defaultValue(Process::class)
                    ->info('a FQCN of the Process class to use in the JobBoy\Process\Domain\Factory\ProcessFactory')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
