<?php

/*
 * This file is part of the Sonata package.
*
* (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Sonata\MediaBundle\Command;
use Symfony\Component\Console\Input\InputOption;

use Symfony\Component\Console\Input\InputArgument;

use Sonata\MediaBundle\Provider\ImageProvider;
use Sonata\MediaBundle\Document\MediaManager;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * This command can be used to re-generate the thumbnails for all uploaded medias.
 * 
 * Useful if you have existing media content and added new formats.
 *
 */
class SyncThumbsCommand extends ContainerAwareCommand
{
    protected $quiet = false;
    protected $output;
    
    public function configure()
    {
        $this->setName('sonata:media:sync-thumbnails')
            ->setDescription('Sync uploaded image thumbs with new media formats')
            ->setDefinition(array(
                new InputArgument('providerName', InputArgument::REQUIRED, 'The provider'),
                new InputOption('context', '', InputOption::VALUE_OPTIONAL, 'The context', 'default'),
        ));
    }
    
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $provider = $input->getArgument('providerName');
        $context = $input->getOption('context');
        $this->quiet = $input->getOption('quiet');
        $this->output = $output;
        
        $container = $this->getContainer();
        $manager = $container->get('sonata.media.manager.media');
        $medias = $manager->findBy(array('providerName' => $provider, 'context' => $context));
        $this->log(sprintf("Loaded %s images for generating thumbs (provider: %s, context: %s)", count($medias), $provider, $context));
        
        foreach ($medias as $media) {
            $provider = $manager->getPool()->getProvider($media->getProviderName());
            $this->log("Generating thumbs for " . $media->getName() . ' - ' . $media->getId());
            $provider->removeThumbnails($media);
            $provider->generateThumbnails($media);
        }
        
        $this->log('Done.');
    }

    /**
     * Write a message to the output
     * 
     * @param string $message
     * @return void
     */
    protected function log($message)
    {
        if ($this->quiet === false) {
            $this->output->writeln($message);
        }
    }
}
