<?php
namespace c975L\EventsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        //Gets the manager
        $em = $this->getContainer()
            ->get('doctrine')
            ->getManager();

        //Gets repository
        $repository = $em->getRepository('c975LEventsBundle:Event');

        //Gets the events
        $eventsList = $repository->findAllEvents()->getQuery()->getResult();

        //Defines data related to events
        $events = array();
        $languages = $container->getParameter('c975_l_events.sitemapLanguages');

        foreach ($eventsList as $event) {
            //Defines data
            if (!empty($languages)) {
                foreach($languages as $language) {
                    $url = $container->getParameter('c975_l_events.sitemapBaseUrl');
                    $url .= '/' . $language;
                    $url .= '/events/' . $event->getSlug() . '/' . $event->getId();
                    $events[]= array(
                        'url' => $url,
                        'changeFrequency' => 'monthly',
                        'priority' => '0.7',
                    );
                }
            }
            else {
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
