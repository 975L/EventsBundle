<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\EventsBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;
use Twig_Environment;
use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\EventsBundle\Entity\Event;

/**
 * Console command to create sitemap of events, executed with 'events:createSitemap'
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2017 975L <contact@975l.com>
 */
class SitemapCreateCommand extends ContainerAwareCommand
{
    /**
     * Stores ConfigServiceInterface
     * @var ConfigServiceInterface
     */
    private $configService;

    /**
     * Stores ContainerInterface
     * @var ContainerInterface
     */
    private $container;

    /**
     * Stores EntityManagerInterface
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * Stores Twig_Environment
     * @var Twig_Environment
     */
    private $templating;

    public function __construct(
        ConfigServiceInterface $configService,
        ContainerInterface $container,
        EntityManagerInterface $em,
        Twig_Environment $templating
    )
    {
        parent::__construct();
        $this->configService = $configService;
        $this->container = $container;
        $this->em = $em;
        $this->templating = $templating;
    }

    protected function configure()
    {
        $this
            ->setName('events:createSitemap')
            ->setDescription('Creates the sitemap of events managed via Events')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Gets the events
        $eventsList = $this->em
            ->getRepository('c975LEventsBundle:Event')
            ->findNotFinished()
        ;

        //Defines data related to events
        $events = array();
        $languages = $this->configService->getParameter('c975LEvents.sitemapLanguages');

        $urlRoot = $this->configService->getParameter('c975LEvents.sitemapBaseUrl');
        foreach ($eventsList as $event) {
            //Defines data
            if (null !== $languages) {
                foreach ($languages as $language) {
                    $url = $urlRoot;
                    $url .= '/' . $language;
                    $url .= '/events/' . $event->getSlug() . '/' . $event->getId();
                    $events[]= array(
                        'url' => $url,
                        'changeFrequency' => 'monthly',
                        'priority' => '0.7',
                    );
                }
            } else {
                $url = $urlRoot;
                $url .= '/events/' . $event->getSlug() . '/' . $event->getId();
                $events[]= array(
                    'url' => $url,
                    'changeFrequency' => 'monthly',
                    'priority' => '0.7',
                );
            }
        }

        //Writes file
        $sitemapContent = $this->templating->render('@c975LEvents/sitemap.xml.twig', array('events' => $events));

        $rootFolder = $this->container->getParameter('kernel.root_dir');
        $sitemapFile = '4' === substr(Kernel::VERSION, 0, 1) ? $rootFolder . '/../public/sitemap-events.xml' : $rootFolder . '/../web/sitemap-events.xml';
        file_put_contents($sitemapFile, $sitemapContent);

        //Ouputs message
        $output->writeln('Sitemap created!');
    }
}
