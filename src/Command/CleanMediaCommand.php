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

use Sonata\MediaBundle\Filesystem\Local;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\FileProvider;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

#[AsCommand(name: 'sonata:media:clean-uploads', description: 'Find orphaned files in media upload directory')]
final class CleanMediaCommand extends Command
{
    /**
     * @var string[]|null
     */
    private ?array $providers = null;

    /**
     * @internal This class should only be used through the console
     */
    public function __construct(
        private Local $filesystemLocal,
        private Pool $mediaPool,
        private MediaManagerInterface $mediaManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Execute the cleanup as a dry run. This doesn\'t remove any files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryRun = $input->getOption('dry-run');
        $verbose = $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;

        $finder = Finder::create();
        $filesystem = new Filesystem();
        $baseDirectory = $this->filesystemLocal->getDirectory();

        if (false === $baseDirectory) {
            throw new \RuntimeException('Unable to find upload directory, did you configure it?');
        }

        $output->writeln(\sprintf('<info>Scanning upload directory: %s</info>', $baseDirectory));

        foreach ($this->mediaPool->getContexts() as $contextName => $context) {
            if (!$filesystem->exists($baseDirectory.'/'.$contextName)) {
                $output->writeln(\sprintf("<info>'%s' does not exist</info>", $baseDirectory.'/'.$contextName));

                continue;
            }

            $output->writeln(\sprintf('<info>Context: %s</info>', $contextName));

            $files = $finder->files()->in($baseDirectory.'/'.$contextName);

            foreach ($files as $file) {
                $filename = $file->getFilename();

                if (!$this->mediaExists($filename, $contextName)) {
                    if ($dryRun) {
                        $output->writeln(\sprintf("<info>'%s' is orphanend</info>", $filename));
                    } else {
                        try {
                            $realPath = $file->getRealPath();

                            if (false !== $realPath) {
                                $filesystem->remove($realPath);
                            }

                            $output->writeln(\sprintf("<info>'%s' was successfully removed</info>", $filename));
                        } catch (IOException $ioe) {
                            $output->writeln(\sprintf('<error>%s</error>', $ioe->getMessage()));
                        }
                    }
                } elseif ($verbose) {
                    $output->writeln(\sprintf("'%s' found", $filename));
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
        if (null === $this->providers) {
            $this->providers = [];

            foreach ($this->mediaPool->getProviders() as $provider) {
                if ($provider instanceof FileProvider) {
                    $this->providers[] = $provider->getName();
                }
            }
        }

        return $this->providers;
    }

    private function mediaExists(string $filename, ?string $context = null): bool
    {
        $mediaManager = $this->mediaManager;

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
