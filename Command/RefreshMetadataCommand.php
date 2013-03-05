<?php

/*
 * This file is part of the Sonata package.
*
* (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Sonata\MediaBundle\Command;

use Symfony\Component\Console\Input\InputArgument;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

/**
 * This command can be used to re-generate the thumbnails for all uploaded medias.
 *
 * Useful if you have existing media content and added new formats.
 *
 */
class RefreshMetadataCommand extends BaseCommand
{
    protected $quiet = false;
    protected $output;

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('sonata:media:refresh-metadata')
            ->setDescription('Refresh meta information')
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

        $context  = $input->getArgument('context');
        if (null === $context) {
            $contexts = array_keys($this->getMediaPool()->getContexts());
            $contextKey = $this->getHelperSet()->get('dialog')->select($output, 'Please select the context', $contexts);
            $context = $providers[$contextKey];
        }

        $this->quiet = $input->getOption('quiet');
        $this->output = $output;

        $medias = $this->getMediaManager()->findBy(array(
            'providerName' => $provider,
            'context'      => $context
        ));

        $this->log(sprintf("Loaded %s medias for generating thumbs (provider: %s, context: %s)", count($medias), $provider, $context));

        foreach ($medias as $media) {
            $provider = $this->getMediaPool()->getProvider($media->getProviderName());

            $this->log("Refresh media " . $media->getName() . ' - ' . $media->getId());

            $provider->updateMetadata($media, false);
            $this->getMediaManager()->save($media);
        }

        $this->log('Done.');
    }

    /**
     * Write a message to the output
     *
     * @param string $message
     *
     * @return void
     */
    protected function log($message)
    {
        if (false === $this->quiet) {
            $this->output->writeln($message);
        }
    }
}
