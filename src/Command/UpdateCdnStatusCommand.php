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
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * This command can be used to update CDN status for media that are currently
 * in status flushing.
 *
 * @final since sonata-project/media-bundle 3.21.0
 *
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
class UpdateCdnStatusCommand extends BaseCommand
{
    /**
     * @var bool
     */
    protected $quiet = false;

    /**
     * @var OutputInterface
     */
    protected $output;

    protected static $defaultName = 'sonata:media:update-cdn-status';

    /**
     * @var InputInterface
     */
    private $input;

    public function configure()
    {
        $this
            ->setDefinition([
                new InputArgument('providerName', InputArgument::OPTIONAL, 'The provider'),
                new InputArgument('context', InputArgument::OPTIONAL, 'The context'),
            ])
            ->setDescription('Updates model media with the current CDN status')
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

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->quiet = $input->getOption('quiet');

        $this->input = $input;
        $this->output = $output;

        $provider = $this->getProvider();
        $context = $this->getContext();

        $medias = $this->getMediaManager()->findBy([
            'providerName' => $provider->getName(),
            'context' => $context,
            'cdnIsFlushable' => true,
        ]);

        $this->log(sprintf('Loaded %s media for CDN status update (provider: %s, context: %s)', \count($medias), $provider->getName(), $context));

        foreach ($medias as $media) {
            $cdn = $provider->getCdn();

            $this->log(sprintf('Refresh CDN status for media "%s" (%s) ', $media->getName(), $media->getId()), false);

            if (!$media->getCdnFlushIdentifier()) {
                $this->log('<error>Skipping since the medium does not have a pending flush.</error>');

                continue;
            }

            $previousStatus = $media->getCdnStatus();

            try {
                $cdnStatus = $cdn->getFlushStatus($media->getCdnFlushIdentifier());
                if (\in_array($cdnStatus, [CDNInterface::STATUS_OK, CDNInterface::STATUS_ERROR], true)) {
                    $media->setCdnFlushIdentifier(null);

                    if (CDNInterface::STATUS_OK === $cdnStatus) {
                        $media->setCdnFlushAt(new \DateTime());
                    }
                }
                $media->setCdnStatus($cdnStatus);

                if (OutputInterface::VERBOSITY_VERBOSE <= $this->output->getVerbosity()) {
                    if ($previousStatus === $cdnStatus) {
                        $this->log(sprintf('No changes (%u)', $cdnStatus));
                    } elseif (CDNInterface::STATUS_OK === $cdnStatus) {
                        $this->log(sprintf('<info>Flush completed</info> (%u => %u)', $previousStatus, $cdnStatus));
                    } else {
                        $this->log(sprintf('Updated status (%u => %u)', $previousStatus, $cdnStatus));
                    }
                }
            } catch (\Throwable $e) {
                $this->log(sprintf(
                    '<error>Unable update CDN status, media: %s - %s </error>',
                    $media->getId(),
                    $e->getMessage()
                ), ['exception' => $e]);

                continue;
            }

            try {
                $this->getMediaManager()->save($media);
            } catch (\Throwable $e) {
                $this->log(sprintf(
                    '<error>Unable to update medium: %s - %s </error>',
                    $media->getId(),
                    $e->getMessage()
                ), ['exception' => $e]);

                continue;
            }
        }

        $this->log('Done!');

        return 0;
    }

    /**
     * Write a message to the output.
     *
     * @param string    $message
     * @param bool|true $newLine
     */
    protected function log($message, $newLine = true)
    {
        if (false === $this->quiet) {
            if ($newLine) {
                $this->output->writeln($message);
            } else {
                $this->output->write($message);
            }
        }
    }

    private function getProvider(): MediaProviderInterface
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

    private function getContext(): string
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

    private function getQuestionHelper(): QuestionHelper
    {
        return $this->getHelper('question');
    }
}
