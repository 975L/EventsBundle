<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\EventsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console command to create sitemap of events, executed with 'events:createSitemap'
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2017 975L <contact@975l.com>
 */
class SitemapCreateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('events:createSitemap')
            ->setDescription('Creates the sitemap of events managed via Events')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Gets the container
        $container = $this->getContainer();

        //Gets the manager
        $em = $container->get('doctrine')
            ->getManager();

        //Gets repository
        $repository = $em->getRepository('c975LEventsBundle:Event');

        //Gets the events
        $eventsList = $repository->findAllEventsNotFinished()->getQuery()->getResult();

        //Defines data related to events
        $events = array();
        $languages = $container->getParameter('c975_l_events.sitemapLanguages');

        foreach ($eventsList as $event) {
            //Defines data
            if (!empty($languages)) {
                foreach ($languages as $language) {
                    $url = $container->getParameter('c975_l_events.sitemapBaseUrl');
                    $url .= '/' . $language;
                    $url .= '/events/' . $event->getSlug() . '/' . $event->getId();
                    $events[]= array(
                        'url' => $url,
                        'changeFrequency' => 'monthly',
                        'priority' => '0.7',
                    );
                }
            } else {
                $url = $container->getParameter('c975_l_events.sitemapBaseUrl');
                $url .= '/events/' . $event->getSlug() . '/' . $event->getId();
                $events[]= array(
                    'url' => $url,
                    'changeFrequency' => 'monthly',
                    'priority' => '0.7',
                );
            }
        }

        //Writes file
        $sitemapContent = $container->get('templating')->render(
            '@c975LEvents/sitemap.xml.twig',
            array('events' => $events)
        );
        $sitemapFile = $container->getParameter('kernel.root_dir') . '/../web/sitemap-events.xml';
        file_put_contents($sitemapFile, $sitemapContent);

        //Ouputs message
        $output->writeln('Sitemap created!');
    }
}
