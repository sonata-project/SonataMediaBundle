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
use Symfony\Component\Console\Helper\QuestionHelper;
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
class RemoveThumbsCommand extends BaseCommand
{
    /**
     * @var bool
     */
    protected $quiet = false;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('sonata:media:remove-thumbnails')
            ->setDescription('Remove uploaded image thumbs')
            ->setDefinition(
                [
                new InputArgument('providerName', InputArgument::OPTIONAL, 'The provider'),
                new InputArgument('context', InputArgument::OPTIONAL, 'The context'),
                new InputArgument('format', InputArgument::OPTIONAL, 'The format (pass `all` to delete all thumbs)'),
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
        $this->input = $input;
        $this->output = $output;

        $this->quiet = $this->input->getOption('quiet');

        $provider = $this->getProvider();
        $context = $this->getContext();
        $format = $this->getFormat($provider, $context);

        $batchCounter = 0;
        $batchSize = (int) ($this->input->getOption('batchSize'));
        $batchesLimit = (int) ($this->input->getOption('batchesLimit'));
        $startOffset = (int) ($this->input->getOption('startOffset'));
        $totalMediasCount = 0;
        do {
            ++$batchCounter;

            try {
                $batchOffset = $startOffset + ($batchCounter - 1) * $batchSize;
                $medias = $this->getMediaManager()->findBy(
                    [
                        'providerName' => $provider->getName(),
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
                    'Loaded %s medias (batch #%d, offset %d) for removing thumbs (provider: %s, format: %s)',
                    $batchMediasCount,
                    $batchCounter,
                    $batchOffset,
                    $provider->getName(),
                    $format
                )
            );

            foreach ($medias as $media) {
                if (!$this->processMedia($media, $provider, $context, $format)) {
                    continue;
                }
                //clean filesystem registry for saving memory
                $provider->getFilesystem()->clearFileRegister();
            }

            if ($batchesLimit > 0 && $batchCounter === $batchesLimit) {
                break;
            }
        } while (true);

        $this->log("Done (total medias processed: {$totalMediasCount}).");

        return 0;
    }

    /**
     * @return MediaProviderInterface
     */
    public function getProvider()
    {
        $providerName = $this->input->getArgument('providerName');

        if (null === $providerName) {
            $providerName = $this->getQuestionHelper()->ask(
                $this->input,
                $this->output,
                new ChoiceQuestion('Please select the provider', array_keys($this->getMediaPool()->getProviders()))
            );
        }

        return $this->getMediaPool()->getProvider($providerName);
    }

    /**
     * @return string
     */
    public function getContext()
    {
        $context = $this->input->getArgument('context');

        if (null === $context) {
            $context = $this->getQuestionHelper()->ask(
                $this->input,
                $this->output,
                new ChoiceQuestion('Please select the context', array_keys($this->getMediaPool()->getContexts()))
            );
        }

        return $context;
    }

    /**
     * @param string $context
     *
     * @return string
     */
    public function getFormat(MediaProviderInterface $provider, $context)
    {
        $format = $this->input->getArgument('format');

        if (null === $format) {
            $formats = array_keys($provider->getFormats());
            $formats[] = '<ALL THUMBNAILS>';

            $format = $this->getQuestionHelper()->ask(
                $this->input,
                $this->output,
                new ChoiceQuestion('Please select the format', $formats)
            );

            if ('<ALL THUMBNAILS>' === $format) {
                $format = $context.'_all';
            }
        } else {
            $format = $context.'_'.$format;
        }

        return $format;
    }

    /**
     * @param string $context
     * @param string $format
     *
     * @return bool
     */
    protected function processMedia(MediaInterface $media, MediaProviderInterface $provider, $context, $format)
    {
        $this->log('Deleting thumbs for '.$media->getName().' - '.$media->getId());

        try {
            if ($format === $context.'_all') {
                $format = null;
            }

            $provider->removeThumbnails($media, $format);
        } catch (\Exception $e) {
            $this->log(sprintf(
                '<error>Unable to remove thumbnails, media: %s - %s </error>',
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

    private function getQuestionHelper(): QuestionHelper
    {
        return $this->getHelper('question');
    }
}
