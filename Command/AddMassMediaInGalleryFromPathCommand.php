<?php

/*
 * This file is part of the Sonata package.
*
* (c) Nicolas Ricci <nicolas.ricci@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Application\Sonata\MediaBundle\Command;

use Application\Sonata\MediaBundle\Entity\GalleryHasMedia;
use Sonata\MediaBundle\Command\BaseCommand;
use Sonata\MediaBundle\Model\GalleryInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

class AddMassMediaInGalleryFromPathCommand extends BaseCommand
{
    protected $setters;

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('sonata:media:add-multiple-from-path')
            ->setDescription('Add medias in mass into the database from a specific path')
            ->setDefinition(array(
                new InputOption('path', null, InputOption::VALUE_REQUIRED, 'The path to the folder to analyze'),
                new InputOption('context', null, InputOption::VALUE_OPTIONAL, 'The context to import the images to', 'default'),
                new InputOption('gallery_id', null, InputOption::VALUE_OPTIONAL, 'The gallery id to import to', false),
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($handle = opendir($input->getOption('path'))) {
            $output->writeln(sprintf('Path: %s', $input->getOption('path')));
            $output->writeln('Importing:');

            // retrieve gallery from option
            if ($input->getOption('gallery_id') !== false) {
                $galleryManager = $this->getContainer()->get('sonata.media.manager.gallery');
                $gallery = $galleryManager->find($input->getOption('gallery_id'));
                if (!$gallery instanceof GalleryInterface) {
                    throw new \RuntimeException('This gallery id does not exist');
                }
                // get last position in gallery
                $pos = $this->getLastPosition($gallery);
            }

            $this->setters = array('providerName', 'context', 'binaryContent');
            while (false !== ($entry = readdir($handle))) {

                $file_path = $input->getOption('path') . '/' . $entry;
                if (!$image = @getimagesize($file_path)) {
                    continue;
                }
                $data = array('sonata.media.provider.image', $input->getOption('context'), $file_path);
                $media = $this->insertMedia($data, $output);

                if (isset($gallery)) {
                    $galleryHasMedia = new GalleryHasMedia();
                    $galleryHasMedia->setGallery($gallery);
                    $galleryHasMedia->setMedia($media);
                    $galleryHasMedia->setEnabled(1);
                    $galleryHasMedia->setPosition($pos);
                    $this->getContainer()->get('doctrine.orm.entity_manager')->persist($galleryHasMedia);
                    $pos ++;
                }
            }

            $this->getContainer()->get('doctrine.orm.entity_manager')->flush();
            $this->optimize();
            closedir($handle);


        }

        $output->writeln("Done!");
    }


    /**
     * @param array $data
     * @param OutputInterface $output
     */
    protected function insertMedia(array $data, OutputInterface $output)
    {
        $media = $this->getMediaManager()->create();

        foreach ($this->setters as $pos => $name) {
            call_user_func(array($media, 'set' . $name), $data[$pos]);
        }

        try {
            $this->getMediaManager()->save($media);
            $output->writeln(sprintf(' > %s - %s', $media->getId(), $media->getName()));
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>%s</error> : %s', $e->getMessage(), json_encode($data)));
            return false;
        }

        return $media;
    }

    protected function optimize()
    {
        if ($this->getContainer()->has('doctrine')) {
            $this->getContainer()->get('doctrine')->getManager()->getUnitOfWork()->clear();
        }
    }

    protected function getLastPosition(GalleryInterface $gallery)
    {
        $galleryMedias = $gallery->getGalleryHasMedias();
        $pos = 0;
        foreach($galleryMedias as $galleryMedia){
            if($galleryMedia->getPosition() > $pos){
                $pos = $galleryMedia->getPosition();
            }
        }
        $pos++;
        return $pos;
    }
}
