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

use Sonata\MediaBundle\Provider\MediaProviderInterface;
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
/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class RefreshMetadataCommand extends BaseCommand
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
        $this->setName('sonata:media:refresh-metadata')
            ->setDescription('Refresh meta information')
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
        ]);

        $this->log(sprintf(
            'Loaded %s medias for generating thumbs (provider: %s, context: %s)',
            \count($medias),
            $provider->getName(),
            $context
        ));

        foreach ($medias as $media) {
            $this->log('Refresh media '.$media->getName().' - '.$media->getId());

            try {
                $provider->updateMetadata($media, false);
            } catch (\Exception $e) {
                $this->log(sprintf('<error>Unable to update metadata, media: %s - %s </error>', $media->getId(), $e->getMessage()));

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
     * @param string $message
     */
    protected function log($message)
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
