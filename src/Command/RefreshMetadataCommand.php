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

use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * This command can be used to re-generate the thumbnails for all uploaded medias.
 *
 * Useful if you have existing media content and added new formats.
 */
#[AsCommand(name: 'sonata:media:refresh-metadata', description: 'Refresh meta information')]
final class RefreshMetadataCommand extends Command
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
            ->addArgument('context', InputArgument::OPTIONAL, 'The context');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->quiet = $input->getOption('quiet');

        $provider = $this->getProvider($input, $output);
        $context = $this->getContext($input, $output);

        $medias = $this->mediaManager->findBy([
            'providerName' => $provider->getName(),
            'context' => $context,
        ]);

        $this->log($output, \sprintf(
            'Loaded %s medias for generating thumbs (provider: %s, context: %s)',
            \count($medias),
            $provider->getName(),
            $context
        ));

        foreach ($medias as $media) {
            $this->log($output, \sprintf(
                'Refresh media %s - %s',
                $media->getName() ?? '',
                $media->getId() ?? ''
            ));

            try {
                $provider->updateMetadata($media, false);
            } catch (\Exception $e) {
                $this->log($output, \sprintf(
                    '<error>Unable to update metadata, media: %s - %s </error>',
                    $media->getId() ?? '',
                    $e->getMessage()
                ));

                continue;
            }

            try {
                $this->mediaManager->save($media);
            } catch (\Exception $e) {
                $this->log($output, \sprintf(
                    '<error>Unable saving media, media: %s - %s </error>',
                    $media->getId() ?? '',
                    $e->getMessage()
                ));

                continue;
            }
        }

        $this->log($output, 'Done!');

        return 0;
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
}
