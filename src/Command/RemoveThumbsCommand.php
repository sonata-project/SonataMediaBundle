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
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
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
#[AsCommand(name: 'sonata:media:remove-thumbnails', description: 'Remove uploaded image thumbs')]
final class RemoveThumbsCommand extends Command
{
    private bool $quiet = false;

    /**
     * @internal This class should only be used through the console
     */
    public function __construct(
        private Pool $mediaPool,
        private MediaManagerInterface $mediaManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('providerName', InputArgument::OPTIONAL, 'The provider')
            ->addArgument('context', InputArgument::OPTIONAL, 'The context')
            ->addArgument('format', InputArgument::OPTIONAL, 'The format (pass `all` to delete all thumbs)')
            ->addOption('batchSize', null, InputOption::VALUE_REQUIRED, 'Media batch size (100 by default)', '100')
            ->addOption('batchesLimit', null, InputOption::VALUE_REQUIRED, 'Media batches limit (0 by default)', '0')
            ->addOption('startOffset', null, InputOption::VALUE_REQUIRED, 'Medias start offset (0 by default)', '0');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->quiet = $input->getOption('quiet');

        $provider = $this->getProvider($input, $output);
        $context = $this->getContext($input, $output);
        $format = $this->getFormat($input, $output, $provider, $context);

        $batchCounter = 0;
        $batchSize = (int) $input->getOption('batchSize');
        $batchesLimit = (int) $input->getOption('batchesLimit');
        $startOffset = (int) $input->getOption('startOffset');
        $totalMediasCount = 0;

        while (true) {
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
                $this->log($output, \sprintf('Error: %s', $e->getMessage()));

                break;
            }

            $batchMediasCount = \count($medias);
            if (0 === $batchMediasCount) {
                break;
            }

            $totalMediasCount += $batchMediasCount;
            $this->log($output, \sprintf(
                'Loaded %s medias (batch #%d, offset %d) for removing thumbs (provider: %s, format: %s)',
                $batchMediasCount,
                $batchCounter,
                $batchOffset,
                $provider->getName(),
                $format
            ));

            foreach ($medias as $media) {
                if (!$this->processMedia($output, $media, $provider, $context, $format)) {
                    continue;
                }
                // clean filesystem registry for saving memory
                $provider->getFilesystem()->clearFileRegister();
            }

            if ($batchesLimit > 0 && $batchCounter === $batchesLimit) {
                break;
            }
        }

        $this->log($output, \sprintf('Done (total medias processed: %s).', $totalMediasCount));

        return 0;
    }

    private function getProvider(InputInterface $input, OutputInterface $output): MediaProviderInterface
    {
        $providerName = $input->getArgument('providerName');

        if (null === $providerName) {
            $providerName = $this->getHelper('question')->ask(
                $input,
                $output,
                new ChoiceQuestion('Please select the provider', array_keys($this->mediaPool->getProviders()))
            );
        }

        return $this->mediaPool->getProvider($providerName);
    }

    private function getContext(InputInterface $input, OutputInterface $output): string
    {
        $context = $input->getArgument('context');

        if (null === $context) {
            $context = $this->getHelper('question')->ask(
                $input,
                $output,
                new ChoiceQuestion('Please select the context', array_keys($this->mediaPool->getContexts()))
            );
        }

        return $context;
    }

    private function getFormat(InputInterface $input, OutputInterface $output, MediaProviderInterface $provider, string $context): string
    {
        $format = $input->getArgument('format');

        if (null === $format) {
            $formats = array_keys($provider->getFormats());
            $formats[] = '<ALL THUMBNAILS>';

            $format = $this->getHelper('question')->ask(
                $input,
                $output,
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

    private function processMedia(OutputInterface $output, MediaInterface $media, MediaProviderInterface $provider, string $context, string $format): bool
    {
        $this->log($output, \sprintf(
            'Deleting thumbs for %s - %s',
            $media->getName() ?? '',
            $media->getId() ?? ''
        ));

        try {
            if ($format === $context.'_all') {
                $format = null;
            }

            $provider->removeThumbnails($media, $format);
        } catch (\Exception $e) {
            $this->log($output, \sprintf(
                '<error>Unable to remove thumbnails, media: %s - %s </error>',
                $media->getId() ?? '',
                $e->getMessage()
            ));

            return false;
        }

        return true;
    }

    /**
     * Write a message to the output.
     */
    private function log(OutputInterface $output, string $message): void
    {
        if (false === $this->quiet) {
            $output->writeln($message);
        }
    }
}
