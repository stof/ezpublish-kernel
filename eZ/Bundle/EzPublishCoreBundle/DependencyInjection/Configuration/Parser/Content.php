<?php
/**
 * File containing the Content class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser,
    Symfony\Component\Config\Definition\Builder\NodeBuilder,
    Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Configuration parser handling content related config
 */
class Content implements Parser
{
    /**
     * Adds semantic configuration definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $nodeBuilder Node just under ezpublish.system.<siteaccess>
     * @return void
     */
    public function addSemanticConfig( NodeBuilder $nodeBuilder )
    {
        $nodeBuilder
            ->arrayNode( 'content' )
                ->info( 'Content related configuration' )
                ->children()
                    ->booleanNode( 'view_cache' )->defaultValue( true )->end()
                    ->booleanNode( 'ttl_cache' )->defaultValue( false )->end()
                    ->scalarNode( 'default_ttl' )->info( 'Default value for TTL cache, in seconds' )->defaultValue( 30 )->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Translates parsed semantic config values from $config to internal key/value pairs.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array $config
     * @param array $siteAccessGroupDefinition
     * @return mixed
     */
    public function registerInternalConfig(ContainerBuilder $container, array $config, array $siteAccessGroupDefinition)
    {
        // TODO: Implement registerInternalConfig() method.
    }
}
