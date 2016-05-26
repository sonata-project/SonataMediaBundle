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

use Sonata\MediaBundle\Provider\FileProvider;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class CleanMediaCommand extends ContainerAwareCommand
{
    /**
     * @var MediaProviderInterface[]|false
     */
    private $providers = false;

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
        $verbose = (bool) $input->getOption('verbose');

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
    }

    /**
     * @return string[]
     */
    private function getProviders()
    {
        if (!$this->providers) {
            $this->providers = array();

            $pool = $this->getContainer()->get('sonata.media.pool');

            foreach ($pool->getProviders() as $provider) {
                if ($provider instanceof FileProvider) {
                    $this->providers[] = $provider->getName();
                }
            }
        }

        return $this->providers;
    }

    /**
     * @param $filename
     * @param $context
     *
     * @return bool
     */
    private function mediaExists($filename, $context = null)
    {
        $mediaManager = $this->getContainer()->get('sonata.media.manager.media');

        $fileParts = explode('_', $filename);

        if (count($fileParts) > 1 && $fileParts[0] == 'thumb') {
            return $mediaManager->findOneBy(array(
                    'id' => $fileParts[1],
                    'context' => $context,
                )) != null;
        }

        return count($mediaManager->findBy(array(
                'providerReference' => $filename,
                'providers' => $this->getProviders(),
            ))) > 0;
    }
}
