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

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\StorageAttributes;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\FileProvider;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
        private FilesystemOperator $filesystemLocal,
        private Pool $mediaPool,
        private MediaManagerInterface $mediaManager,
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

        $filesystem = $this->filesystemLocal;

        /** @var StorageAttributes[] $subdirectories */
        $subdirectories = $filesystem->listContents('.')->toArray();

        foreach ($this->mediaPool->getContexts() as $contextName => $context) {
            $contextDirectory = current(array_filter($subdirectories, static fn ($d) => $d->isDir() && $d->path() === $contextName));

            if (false === $contextDirectory) {
                $output->writeln(\sprintf("<info>'%s' does not exist</info>", $contextName));

                continue;
            }

            $output->writeln(\sprintf('<info>Context: %s</info>', $contextName));

            /** @var \Traversable<StorageAttributes> $files */
            $files = $filesystem->listContents($contextDirectory->path());

            foreach ($files as $file) {
                $filepath = $file->path();
                $filename = basename($filepath);

                if (!$this->mediaExists($filename, $contextName)) {
                    if ($dryRun) {
                        $output->writeln(\sprintf("<info>'%s' is orphaned</info>", $filename));
                    } else {
                        try {
                            $filesystem->delete($filepath);
                            $output->writeln(\sprintf("<info>'%s' was successfully removed</info>", $filename));
                        } catch (FilesystemException $ioe) {
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
