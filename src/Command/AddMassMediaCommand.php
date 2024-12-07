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

use Sonata\Doctrine\Model\ClearableManagerInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'sonata:media:add-multiple', description: 'Add medias in mass into the database')]
final class AddMassMediaCommand extends Command
{
    /**
     * @var string[]
     */
    private array $setters = [];

    /**
     * @internal This class should only be used through the console
     */
    public function __construct(private MediaManagerInterface $mediaManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('file', null, InputOption::VALUE_REQUIRED, 'The file to parse')
            ->addOption('delimiter', null, InputOption::VALUE_REQUIRED, 'Set the field delimiter (one character only)', ',')
            ->addOption('enclosure', null, InputOption::VALUE_REQUIRED, 'Set the field enclosure character (one character only).', '"')
            ->addOption('escape', null, InputOption::VALUE_REQUIRED, 'Set the escape character (one character only). Defaults as a backslash', '\\');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fp = $this->getFilePointer($input, $output);

        $delimiter = $input->getOption('delimiter');
        $enclosure = $input->getOption('enclosure');
        $escape = $input->getOption('escape');

        $readHeaders = false;

        while (!feof($fp)) {
            $data = fgetcsv($fp, 0, $delimiter, $enclosure, $escape);

            if (!\is_array($data)) {
                continue;
            }

            if (!$readHeaders) {
                $this->setters = $data;

                $readHeaders = true;

                continue;
            }

            $this->insertMedia($data, $output);

            if ($this->mediaManager instanceof ClearableManagerInterface) {
                $this->mediaManager->clear();
            }
        }

        $output->writeln('Done!');

        return 0;
    }

    /**
     * @return resource
     */
    private function getFilePointer(InputInterface $input, OutputInterface $output)
    {
        if (false !== ftell(\STDIN)) {
            return \STDIN;
        }

        $file = $input->getOption('file');

        if (null === $file) {
            throw new \RuntimeException('Please provide a CSV file argument or CSV input stream');
        }

        $filePointer = fopen($file, 'r');

        if (false === $filePointer) {
            throw new \RuntimeException(\sprintf('The provided CSV file %s could not be opened', $file));
        }

        return $filePointer;
    }

    /**
     * @param mixed[] $data
     */
    private function insertMedia(array $data, OutputInterface $output): void
    {
        $media = $this->mediaManager->create();

        foreach ($this->setters as $pos => $name) {
            $media->{'set'.$name}($data[$pos]);
        }

        try {
            $this->mediaManager->save($media);
            $output->writeln(\sprintf(' > %s - %s', $media->getId() ?? '', $media->getName() ?? ''));
        } catch (\Exception $e) {
            $output->writeln(\sprintf('<error>%s</error> : %s', $e->getMessage(), json_encode($data, \JSON_THROW_ON_ERROR)));
        }
    }
}
