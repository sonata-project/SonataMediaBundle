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
use Sonata\MediaBundle\Provider\ImageProvider;
use Sonata\MediaBundle\Document\MediaManager;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class SyncThumbsCommand extends ContainerAwareCommand
{
    
    public function configure()
    {
        $this->setName('sonata:media:sync');
        $this->setDescription('Sync uploaded image thumbs with new media formats');
    }
    
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $manager = $container->get('sonata.media.manager.media');
        $medias = $manager->findBy(array('providerName' => 'sonata.media.provider.image'));
        
        $output->writeln("Loaded " . count($medias) . " images for generating thumbs...");
        
        foreach ($medias as $media) {
            $provider = $manager->getPool()->getProvider($media->getProviderName());
            $output->writeln("Generating thumbs for " . $media->getName());
            $provider->generateThumbnails($media);
            
        }
    }
}
