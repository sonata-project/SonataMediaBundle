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

use Sonata\MediaBundle\CDN\CDNInterface;
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
 * This command can be used to update CDN status for media that are currently
 * in status flushing.
 *
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
#[AsCommand(name: 'sonata:media:update-cdn-status', description: 'Updates model media with the current CDN status')]
final class UpdateCdnStatusCommand extends Command
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
            ->setHelp(
                <<<'EOF'
                    The <info>%command.name%</info> command helps maintaining your model media in sync
                    with the CDN. Since the flush process in a CDN is not an immediate operation, the
                    media that is marked as flushable has the status <info>CDNInterface::STATUS_TO_FLUSH</info>
                    when it is updated. This command iterates over the media, retrieves the flush status
                    from the CDN and performs the update in your model based on the CDN response.

                    When you execute the command, it will prompt for a media provider and context:

                      <info>php %command.full_name%</info>

                    You can also pass the media provider and the context as arguments:

                      <info>php %command.full_name% sonata.media.provider.file default</info>

                    EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->quiet = $input->getOption('quiet');

        $provider = $this->getProvider($input, $output);
        $context = $this->getContext($input, $output);

        $medias = $this->mediaManager->findBy([
            'providerName' => $provider->getName(),
            'context' => $context,
            'cdnIsFlushable' => true,
        ]);

        $this->log($output, \sprintf('Loaded %s media for CDN status update (provider: %s, context: %s)', \count($medias), $provider->getName(), $context));

        foreach ($medias as $media) {
            $cdn = $provider->getCdn();
            $flushIdentifier = $media->getCdnFlushIdentifier();

            $this->log($output, \sprintf(
                'Refresh CDN status for media "%s" (%s) ',
                $media->getName() ?? '',
                $media->getId() ?? ''
            ), false);

            if (null === $flushIdentifier) {
                $this->log($output, '<error>Skipping since the medium does not have a pending flush.</error>');

                continue;
            }

            $previousStatus = $media->getCdnStatus();

            try {
                $cdnStatus = $cdn->getFlushStatus($flushIdentifier);

                if (\in_array($cdnStatus, [CDNInterface::STATUS_OK, CDNInterface::STATUS_ERROR], true)) {
                    $media->setCdnFlushIdentifier(null);

                    if (CDNInterface::STATUS_OK === $cdnStatus) {
                        $media->setCdnFlushAt(new \DateTime());
                    }
                }

                $media->setCdnStatus($cdnStatus);

                if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
                    if ($previousStatus === $cdnStatus) {
                        $this->log($output, \sprintf('No changes (%u)', $cdnStatus));
                    } elseif (CDNInterface::STATUS_OK === $cdnStatus) {
                        $this->log($output, \sprintf('<info>Flush completed</info> (%u => %u)', $previousStatus ?? 'null', $cdnStatus));
                    } else {
                        $this->log($output, \sprintf('Updated status (%u => %u)', $previousStatus ?? 'null', $cdnStatus));
                    }
                }
            } catch (\Throwable $e) {
                $this->log($output, \sprintf(
                    '<error>Unable update CDN status, media: %s - %s </error>',
                    $media->getId() ?? '',
                    $e->getMessage()
                ));

                continue;
            }

            try {
                $this->mediaManager->save($media);
            } catch (\Throwable $e) {
                $this->log($output, \sprintf(
                    '<error>Unable to update medium: %s - %s </error>',
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
    private function log(OutputInterface $output, string $message, bool $newLine = true): void
    {
        if (false === $this->quiet) {
            if ($newLine) {
                $output->writeln($message);
            } else {
                $output->write($message);
            }
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
