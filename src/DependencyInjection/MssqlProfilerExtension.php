<?php

declare(strict_types = 1);

namespace Dekcz\MssqlProfiler\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages the bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class MssqlProfilerExtension extends Extension implements PrependExtensionInterface
{

    private ContainerBuilder $globContainer;

    public function prepend(ContainerBuilder $container)
    {
        $this->globContainer = $container;
    }

    /**
     * @param array<int, array> $configs
     * @param ContainerBuilder $container
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        if ($config['web_profiler']) {
            $loader->load('datacollector.yml');
            if ($config['client_definition'] !== null) {
                $parentDef = $this->globContainer->getDefinition($config['client_definition']);
                $parentDef->addMethodCall('setMssqlCollector', ['@mssql_collector']);
            }
        }
    }

}
