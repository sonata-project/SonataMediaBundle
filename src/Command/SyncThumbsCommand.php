<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Command;

use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * This command can be used to re-generate the thumbnails for all uploaded medias.
 *
 * Useful if you have existing media content and added new formats.
 */
/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class SyncThumbsCommand extends BaseCommand
{
    /**
     * @var bool
     */
    protected $quiet = false;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('sonata:media:sync-thumbnails')
            ->setDescription('Sync uploaded image thumbs with new media formats')
            ->setDefinition(
                [
                new InputArgument('providerName', InputArgument::OPTIONAL, 'The provider'),
                new InputArgument('context', InputArgument::OPTIONAL, 'The context'),
                new InputOption('batchSize', null, InputOption::VALUE_REQUIRED, 'Media batch size (100 by default)', 100),
                new InputOption('batchesLimit', null, InputOption::VALUE_REQUIRED, 'Media batches limit (0 by default)', 0),
                new InputOption('startOffset', null, InputOption::VALUE_REQUIRED, 'Medias start offset (0 by default)', 0),
            ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $providerName = $input->getArgument('providerName');
        if (null === $providerName) {
            $providers = array_keys($this->getMediaPool()->getProviders());
            $question = new ChoiceQuestion('Please select the provider', $providers, 0);
            $question->setErrorMessage('Provider %s is invalid.');

            $providerName = $helper->ask($input, $output, $question);
        }

        $context = $input->getArgument('context');
        if (null === $context) {
            $contexts = array_keys($this->getMediaPool()->getContexts());
            $question = new ChoiceQuestion('Please select the context', $contexts, 0);
            $question->setErrorMessage('Context %s is invalid.');

            $context = $helper->ask($input, $output, $question);
        }

        $this->quiet = $input->getOption('quiet');
        $this->output = $output;

        $provider = $this->getMediaPool()->getProvider($providerName);

        $filesystem = $provider->getFilesystem();
        $fsReflection = new \ReflectionClass($filesystem);
        $fsRegister = $fsReflection->getProperty('fileRegister');
        $fsRegister->setAccessible(true);

        $batchCounter = 0;
        $batchSize = (int) $input->getOption('batchSize');
        $batchesLimit = (int) $input->getOption('batchesLimit');
        $startOffset = (int) $input->getOption('startOffset');
        $totalMediasCount = 0;
        do {
            ++$batchCounter;

            try {
                $batchOffset = $startOffset + ($batchCounter - 1) * $batchSize;
                $medias = $this->getMediaManager()->findBy(
                    [
                        'providerName' => $providerName,
                        'context' => $context,
                    ],
                    [
                        'id' => 'ASC',
                    ],
                    $batchSize,
                    $batchOffset
                );
            } catch (\Exception $e) {
                $this->log('Error: '.$e->getMessage());

                break;
            }

            $batchMediasCount = \count($medias);
            if (0 === $batchMediasCount) {
                break;
            }

            $totalMediasCount += $batchMediasCount;
            $this->log(
                sprintf(
                    'Loaded %s medias (batch #%d, offset %d) for generating thumbs (provider: %s, context: %s)',
                    $batchMediasCount,
                    $batchCounter,
                    $batchOffset,
                    $providerName,
                    $context
                )
            );

            foreach ($medias as $media) {
                if (!$this->processMedia($media, $provider)) {
                    continue;
                }
                //clean filesystem registry for saving memory
                $fsRegister->setValue($filesystem, []);
            }

            //clear entity manager for saving memory
            $this->getMediaManager()->getObjectManager()->clear();

            if ($batchesLimit > 0 && $batchCounter === $batchesLimit) {
                break;
            }
        } while (true);

        $this->log("Done (total medias processed: {$totalMediasCount}).");

        return 0;
    }

    /**
     * @param MediaInterface         $media
     * @param MediaProviderInterface $provider
     *
     * @return bool
     */
    protected function processMedia($media, $provider)
    {
        $this->log('Generating thumbs for '.$media->getName().' - '.$media->getId());

        try {
            $provider->removeThumbnails($media);
        } catch (\Exception $e) {
            $this->log(sprintf(
                '<error>Unable to remove old thumbnails, media: %s - %s </error>',
                $media->getId(),
                $e->getMessage()
            ));

            return false;
        }

        try {
            $provider->generateThumbnails($media);
        } catch (\Exception $e) {
            $this->log(sprintf(
                '<error>Unable to generate new thumbnails, media: %s - %s </error>',
                $media->getId(),
                $e->getMessage()
            ));

            return false;
        }

        return true;
    }

    /**
     * Write a message to the output.
     *
     * @param string $message
     */
    protected function log($message)
    {
        if (false === $this->quiet) {
            $this->output->writeln($message);
        }
    }
}
