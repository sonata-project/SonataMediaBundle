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

use Sonata\MediaBundle\Provider\FileProvider;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class CleanMediaCommand extends ContainerAwareCommand
{
    /**
     * @var MediaProviderInterface[]|null
     */
    private $providers;

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('sonata:media:clean-uploads')
            ->setDescription('Find orphaned files in media upload directory')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Execute the cleanup as a dry run. This doesn\'t remove any files');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dryRun = (bool) $input->getOption('dry-run');
        $verbose = $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;

        $pool = $this->getContainer()->get('sonata.media.pool');
        $finder = Finder::create();
        $filesystem = new Filesystem();
        $baseDirectory = $this->getContainer()->get('sonata.media.adapter.filesystem.local')->getDirectory();

        $output->writeln(sprintf('<info>Scanning upload directory: %s</info>', $baseDirectory));

        foreach ($pool->getContexts() as $contextName => $context) {
            if (!$filesystem->exists($baseDirectory.'/'.$contextName)) {
                $output->writeln(sprintf("<info>'%s' does not exist</info>", $baseDirectory.'/'.$contextName));

                continue;
            }

            $output->writeln(sprintf('<info>Context: %s</info>', $contextName));

            $files = $finder->files()->in($baseDirectory.'/'.$contextName);

            foreach ($files as $file) {
                $filename = $file->getFilename();

                if (!$this->mediaExists($filename, $contextName)) {
                    if ($dryRun) {
                        $output->writeln(sprintf("<info>'%s' is orphanend</info>", $filename));
                    } else {
                        try {
                            $filesystem->remove($file->getRealPath());
                            $output->writeln(sprintf("<info>'%s' was successfully removed</info>", $filename));
                        } catch (IOException $ioe) {
                            $output->writeln(sprintf('<error>%s</error>', $ioe->getMessage()));
                        }
                    }
                } elseif ($verbose) {
                    $output->writeln(sprintf("'%s' found", $filename));
                }
            }
        }

        $output->writeln('<info>done!</info>');

        return 0;
    }

    /**
     * @return string[]
     */
    private function getProviders(): array
    {
        if (!$this->providers) {
            $this->providers = [];

            $pool = $this->getContainer()->get('sonata.media.pool');

            foreach ($pool->getProviders() as $provider) {
                if ($provider instanceof FileProvider) {
                    $this->providers[] = $provider->getName();
                }
            }
        }

        return $this->providers;
    }

    private function mediaExists(string $filename, string $context = null): bool
    {
        $mediaManager = $this->getContainer()->get('sonata.media.manager.media');

        $fileParts = explode('_', $filename);

        if (\count($fileParts) > 1 && 'thumb' === $fileParts[0]) {
            return null !== $mediaManager->findOneBy([
                'id' => $fileParts[1],
                'context' => $context,
            ]);
        }

        return \count($mediaManager->findBy([
            'providerReference' => $filename,
            'providerName' => $this->getProviders(),
        ])) > 0;
    }
}
