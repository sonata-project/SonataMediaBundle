<?php

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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command can be used to update CDN status for medias that are currently
 * in status flushing.
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
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('sonata:media:update-cdn-status')
            ->setDescription('Refresh CDN status for medias that are in status flushing')
            ->setDefinition(array(
                new InputArgument('providerName', InputArgument::OPTIONAL, 'The provider'),
                new InputArgument('context', InputArgument::OPTIONAL, 'The context'),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $provider = $input->getArgument('providerName');
        if (null === $provider) {
            $providers = array_keys($this->getMediaPool()->getProviders());
            $providerKey = $this->getHelperSet()->get('dialog')->select($output, 'Please select the provider', $providers);
            $provider = $providers[$providerKey];
        }

        $context = $input->getArgument('context');
        if (null === $context) {
            $contexts = array_keys($this->getMediaPool()->getContexts());
            $contextKey = $this->getHelperSet()->get('dialog')->select($output, 'Please select the context', $contexts);
            $context = $contexts[$contextKey];
        }

        $this->quiet = $input->getOption('quiet');
        $this->output = $output;

        $medias = $this->getMediaManager()->findBy(array(
            'providerName' => $provider,
            'context' => $context,
            'cdnIsFlushable' => true,
        ));

        $this->log(sprintf('Loaded %s medias for updating CDN status (provider: %s, context: %s)', count($medias), $provider, $context));

        foreach ($medias as $media) {
            $cdn = $this->getMediaPool()->getProvider($media->getProviderName())->getCdn();

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
                    if ($previousStatus == $cdnStatus) {
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
}
