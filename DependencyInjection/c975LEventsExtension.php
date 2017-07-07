<?php
/*
 * (c) 2017: 975l <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\EventsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class c975LEventsExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yml');

        $configuration = new Configuration();
        $processedConfig = $this->processConfiguration($configuration, $configs);

        $container->setParameter('c975_l_events.roleNeeded', $processedConfig['roleNeeded']);
        $container->setParameter('c975_l_events.folderPictures', $processedConfig['folderPictures']);
        $container->setParameter('c975_l_events.sitemapBaseUrl', $processedConfig['sitemapBaseUrl']);
        $container->setParameter('c975_l_events.sitemapLanguages', $processedConfig['sitemapLanguages']);
        $container->setParameter('c975_l_events.tinymceApiKey', $processedConfig['tinymceApiKey']);
        $container->setParameter('c975_l_events.tinymceLanguage', $processedConfig['tinymceLanguage']);
        $container->setParameter('c975_l_events.signoutRoute', $processedConfig['signoutRoute']);
        $container->setParameter('c975_l_events.dashboardRoute', $processedConfig['dashboardRoute']);
    }
}
