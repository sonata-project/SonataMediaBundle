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

use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Console\Command\Command;
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
final class RemoveThumbsCommand extends Command
{
    protected static $defaultName = 'sonata:media:remove-thumbnails';
    protected static $defaultDescription = 'Remove uploaded image thumbs';

    /**
     * @var Pool
     */
    private $mediaPool;

    /**
     * @var ManagerInterface<MediaInterface>
     */
    private $mediaManager;

    /**
     * @var bool
     */
    private $quiet = false;

    /**
     * @var InputInterface|null
     */
    private $input;

    /**
     * @var OutputInterface|null
     */
    private $output;

    /**
     * @param ManagerInterface<MediaInterface> $mediaManager
     */
    public function __construct(Pool $mediaPool, ManagerInterface $mediaManager)
    {
        parent::__construct();

        $this->mediaPool = $mediaPool;
        $this->mediaManager = $mediaManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(static::$defaultDescription)
            ->addArgument('providerName', InputArgument::OPTIONAL, 'The provider')
            ->addArgument('context', InputArgument::OPTIONAL, 'The context')
            ->addArgument('format', InputArgument::OPTIONAL, 'The format (pass `all` to delete all thumbs)')
            ->addOption('batchSize', null, InputOption::VALUE_REQUIRED, 'Media batch size (100 by default)', '100')
            ->addOption('batchesLimit', null, InputOption::VALUE_REQUIRED, 'Media batches limit (0 by default)', '0')
            ->addOption('startOffset', null, InputOption::VALUE_REQUIRED, 'Medias start offset (0 by default)', '0');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $quiet = $input->getOption('quiet');
        \assert(\is_bool($quiet));

        $this->quiet = $quiet;
        $this->input = $input;
        $this->output = $output;

        $provider = $this->getProvider();
        $context = $this->getContext();
        $format = $this->getFormat($provider, $context);

        $batchCounter = 0;
        $batchSize = (int) $this->input->getOption('batchSize');
        $batchesLimit = (int) $this->input->getOption('batchesLimit');
        $startOffset = (int) $this->input->getOption('startOffset');
        $totalMediasCount = 0;
        do {
            ++$batchCounter;

            try {
                $batchOffset = $startOffset + ($batchCounter - 1) * $batchSize;
                $medias = $this->mediaManager->findBy(
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

    private function getProvider(): MediaProviderInterface
    {
        $providerName = $this->input->getArgument('providerName');

        if (null === $providerName) {
            $providerName = $this->getQuestionHelper()->ask(
                $this->input,
                $this->output,
                new ChoiceQuestion('Please select the provider', array_keys($this->mediaPool->getProviders()))
            );
        }

        return $this->mediaPool->getProvider($providerName);
    }

    private function getContext(): string
    {
        $context = $this->input->getArgument('context');

        if (null === $context) {
            $context = $this->getQuestionHelper()->ask(
                $this->input,
                $this->output,
                new ChoiceQuestion('Please select the context', array_keys($this->mediaPool->getContexts()))
            );
        }

        return $context;
    }

    private function getFormat(MediaProviderInterface $provider, string $context): string
    {
        $format = $this->input->getArgument('format');
        \assert(null === $format || \is_string($format));

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

    private function processMedia(MediaInterface $media, MediaProviderInterface $provider, string $context, string $format): bool
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
     */
    private function log(string $message): void
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
