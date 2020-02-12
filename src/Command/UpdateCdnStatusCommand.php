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
 * This command can be used to update CDN status for medias that are currently
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

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('sonata:media:update-cdn-status')
            ->setDescription('Refresh CDN status for medias that are in status flushing')
            ->setDefinition(
                [
                new InputArgument('providerName', InputArgument::OPTIONAL, 'The provider'),
                new InputArgument('context', InputArgument::OPTIONAL, 'The context'),
            ]
            );
    }

    /**
     * {@inheritdoc}
     */
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

        $this->log(sprintf('Loaded %s medias for updating CDN status (provider: %s, context: %s)', \count($medias), $provider->getName(), $context));

        foreach ($medias as $media) {
            $cdn = $provider->getCdn();

            $this->log(sprintf('Refresh CDN status for media "%s" (%d) ', $media->getName(), $media->getId()), false);

            if (!$media->getCdnFlushIdentifier()) {
                $this->log('<error>Skiping while empty flush identifier</error>');

                continue;
            }

            try {
                $previousStatus = $media->getCdnStatus();
                if (CDNInterface::STATUS_OK === ($cdnStatus = $cdn->getFlushStatus($media->getCdnFlushIdentifier()))) {
                    $media->setCdnIsFlushable(false);
                    $media->setCdnFlushIdentifier(null);
                    $media->setCdnFlushAt(new \DateTime());
                }
                $media->setCdnStatus($cdnStatus);

                if (OutputInterface::VERBOSITY_VERBOSE <= $this->output->getVerbosity()) {
                    if ($previousStatus === $cdnStatus) {
                        $this->log(sprintf('No changes (%d)', $cdnStatus));
                    } elseif (CDNInterface::STATUS_OK === $cdnStatus) {
                        $this->log(sprintf('<info>Flush completed</info> (%d => %d)', $previousStatus, $cdnStatus));
                    } else {
                        $this->log(sprintf('Updated status (%d => %d)', $previousStatus, $cdnStatus));
                    }
                }
            } catch (\Exception $e) {
                $this->log(sprintf('<error>Unable update CDN status, media: %s - %s </error>', $media->getId(), $e->getMessage()));

                continue;
            }

            try {
                $this->getMediaManager()->save($media);
            } catch (\Exception $e) {
                $this->log(sprintf('<error>Unable saving media, media: %s - %s </error>', $media->getId(), $e->getMessage()));

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
