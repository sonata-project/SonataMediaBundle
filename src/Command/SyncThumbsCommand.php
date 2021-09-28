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

use Sonata\Doctrine\Model\ClearableManagerInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
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
final class SyncThumbsCommand extends Command
{
    protected static $defaultName = 'sonata:media:sync-thumbnails';
    protected static $defaultDescription = 'Sync uploaded image thumbs with new media formats';

    /**
     * @var Pool
     */
    private $mediaPool;

    /**
     * @var MediaManagerInterface
     */
    private $mediaManager;

    /**
     * @var bool
     */
    private $quiet = false;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @internal This class should only be used through the console
     */
    public function __construct(Pool $mediaPool, MediaManagerInterface $mediaManager)
    {
        parent::__construct();

        $this->mediaPool = $mediaPool;
        $this->mediaManager = $mediaManager;
    }

    protected function configure(): void
    {
        \assert(null !== static::$defaultDescription);

        $this
            ->setDescription(static::$defaultDescription)
            ->addArgument('providerName', InputArgument::OPTIONAL, 'The provider')
            ->addArgument('context', InputArgument::OPTIONAL, 'The context')
            ->addOption('batchSize', null, InputOption::VALUE_REQUIRED, 'Media batch size (100 by default)', '100')
            ->addOption('batchesLimit', null, InputOption::VALUE_REQUIRED, 'Media batches limit (0 by default)', '0')
            ->addOption('startOffset', null, InputOption::VALUE_REQUIRED, 'Medias start offset (0 by default)', '0');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        $providerName = $input->getArgument('providerName');
        if (null === $providerName) {
            $providers = array_keys($this->mediaPool->getProviders());
            $question = new ChoiceQuestion('Please select the provider', $providers, 0);
            $question->setErrorMessage('Provider %s is invalid.');

            $providerName = $helper->ask($input, $output, $question);
        }

        $context = $input->getArgument('context');
        if (null === $context) {
            $contexts = array_keys($this->mediaPool->getContexts());
            $question = new ChoiceQuestion('Please select the context', $contexts, 0);
            $question->setErrorMessage('Context %s is invalid.');

            $context = $helper->ask($input, $output, $question);
        }

        $this->quiet = $input->getOption('quiet');
        $this->output = $output;

        $provider = $this->mediaPool->getProvider($providerName);

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
                $medias = $this->mediaManager->findBy(
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
                $this->log(sprintf('Error: %s', $e->getMessage()));

                break;
            }

            $batchMediasCount = \count($medias);
            if (0 === $batchMediasCount) {
                break;
            }

            $totalMediasCount += $batchMediasCount;
            $this->log(sprintf(
                'Loaded %s medias (batch #%d, offset %d) for generating thumbs (provider: %s, context: %s)',
                $batchMediasCount,
                $batchCounter,
                $batchOffset,
                $providerName,
                $context
            ));

            foreach ($medias as $media) {
                if (!$this->processMedia($media, $provider)) {
                    continue;
                }

                // Clean filesystem registry for saving memory
                $fsRegister->setValue($filesystem, []);
            }

            // Clear entity manager for saving memory
            if ($this->mediaManager instanceof ClearableManagerInterface) {
                $this->mediaManager->clear();
            }

            if ($batchesLimit > 0 && $batchCounter === $batchesLimit) {
                break;
            }
        } while (true);

        $this->log(sprintf('Done (total medias processed: %s).', $totalMediasCount));

        return 0;
    }

    private function processMedia(MediaInterface $media, MediaProviderInterface $provider): bool
    {
        $this->log(sprintf(
            'Generating thumbs for %s - %s',
            $media->getName() ?? '',
            $media->getId() ?? ''
        ));

        try {
            $provider->removeThumbnails($media);
        } catch (\Exception $e) {
            $this->log(sprintf(
                '<error>Unable to remove old thumbnails, media: %s - %s </error>',
                $media->getId() ?? '',
                $e->getMessage()
            ));

            return false;
        }

        try {
            $provider->generateThumbnails($media);
        } catch (\Exception $e) {
            $this->log(sprintf(
                '<error>Unable to generate new thumbnails, media: %s - %s </error>',
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
    private function log(string $message): void
    {
        if (false === $this->quiet) {
            $this->output->writeln($message);
        }
    }
}
