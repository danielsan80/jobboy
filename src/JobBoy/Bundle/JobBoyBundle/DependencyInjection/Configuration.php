<?php

namespace JobBoy\Bundle\JobBoyBundle\DependencyInjection;

use JobBoy\Process\Domain\Repository\Infrastructure\Redis\RedisUtil;
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

        if (\method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('job_boy');
        }

        $rootNode
            ->children()
                ->scalarNode('process_repository')
                    ->defaultValue('in_memory')
                    ->info('a service definition id implementing JobBoy\Process\Domain\Repository\ProcessRepositoryInterface')
                ->end()
                ->scalarNode('process_class')
                    ->info('a FQCN of the Process class to use in the JobBoy\Process\Domain\Factory\ProcessFactory')
                ->end()
                ->arrayNode('redis')
                    ->info('used in case of RedisProcessRepository, ignored otherwise')
                    ->children()
                        ->scalarNode('host')
                            ->info('the Redis host')
                        ->end()
                        ->scalarNode('port')
                            ->info('the Redis port')
                            ->defaultValue(RedisUtil::DEFAULT_PORT)
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
