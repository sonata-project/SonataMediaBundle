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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * This command can be used to re-generate the thumbnails for all uploaded medias.
 *
 * Useful if you have existing media content and added new formats.
 */
final class RefreshMetadataCommand extends Command
{
    protected static $defaultName = 'sonata:media:refresh-metadata';
    protected static $defaultDescription = 'Refresh meta information';

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
     * @var OutputInterface
     */
    private $output;

    /**
     * @var InputInterface
     */
    private $input;

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
        \assert(null !== static::$defaultDescription);

        $this
            ->setDescription(static::$defaultDescription)
            ->addArgument('providerName', InputArgument::OPTIONAL, 'The provider')
            ->addArgument('context', InputArgument::OPTIONAL, 'The context');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->quiet = $input->getOption('quiet');
        $this->input = $input;
        $this->output = $output;

        $provider = $this->getProvider();
        $context = $this->getContext();

        $medias = $this->mediaManager->findBy([
            'providerName' => $provider->getName(),
            'context' => $context,
        ]);

        $this->log(sprintf(
            'Loaded %s medias for generating thumbs (provider: %s, context: %s)',
            \count($medias),
            $provider->getName(),
            $context
        ));

        foreach ($medias as $media) {
            $this->log(sprintf(
                'Refresh media %s - %s',
                $media->getName() ?? '',
                $media->getId() ?? ''
            ));

            try {
                $provider->updateMetadata($media, false);
            } catch (\Exception $e) {
                $this->log(sprintf(
                    '<error>Unable to update metadata, media: %s - %s </error>',
                    $media->getId() ?? '',
                    $e->getMessage()
                ));

                continue;
            }

            try {
                $this->mediaManager->save($media);
            } catch (\Exception $e) {
                $this->log(sprintf(
                    '<error>Unable saving media, media: %s - %s </error>',
                    $media->getId() ?? '',
                    $e->getMessage()
                ));

                continue;
            }
        }

        $this->log('Done!');

        return 0;
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

    private function getQuestionHelper(): QuestionHelper
    {
        return $this->getHelper('question');
    }
}
