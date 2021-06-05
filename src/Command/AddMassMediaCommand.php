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

use Doctrine\Persistence\ManagerRegistry;
use Sonata\Doctrine\Model\ManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class AddMassMediaCommand extends Command
{
    protected static $defaultName = 'sonata:media:add-multiple';
    protected static $defaultDescription = 'Add medias in mass into the database';

    /**
     * @var ManagerInterface
     */
    private $mediaManager;

    /**
     * @var ManagerRegistry|null
     */
    private $managerRegistry;

    /**
     * @var string[]
     */
    private $setters;

    public function __construct(ManagerInterface $mediaManager, ?ManagerRegistry $managerRegistry = null)
    {
        parent::__construct();

        $this->mediaManger = $mediaManager;
        $this->managerRegistry = $managerRegistry;
    }

    public function configure(): void
    {
        $this
            ->setDescription(static::$defaultDescription)
            ->setDefinition([
                new InputOption('file', null, InputOption::VALUE_OPTIONAL, 'The file to parse'),
                new InputOption('delimiter', null, InputOption::VALUE_OPTIONAL, 'Set the field delimiter (one character only)', ','),
                new InputOption('enclosure', null, InputOption::VALUE_OPTIONAL, 'Set the field enclosure character (one character only).', '"'),
                new InputOption('escape', null, InputOption::VALUE_OPTIONAL, 'Set the escape character (one character only). Defaults as a backslash', '\\'),
            ]);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $fp = $this->getFilePointer($input, $output);
        $imported = -1;

        while (!feof($fp)) {
            $data = fgetcsv($fp, null, $input->getOption('delimiter'), $input->getOption('enclosure'), $input->getOption('escape'));

            if (-1 === $imported) {
                $this->setters = $data;

                ++$imported;

                continue;
            }

            if (!\is_array($data)) {
                continue;
            }

            ++$imported;

            $this->insertMedia($data, $output);
            $this->optimize();
        }

        $output->writeln('Done!');

        return 0;
    }

    /**
     * @return resource
     */
    protected function getFilePointer(InputInterface $input, OutputInterface $output)
    {
        if (false !== ftell(\STDIN)) {
            return \STDIN;
        }

        if (!$input->getOption('file')) {
            throw new \RuntimeException('Please provide a CSV file argument or CSV input stream');
        }

        return fopen($input->getOption('file'), 'r');
    }

    protected function insertMedia(array $data, OutputInterface $output): void
    {
        $media = $this->mediaManager->create();

        foreach ($this->setters as $pos => $name) {
            $media->{'set'.$name}($data[$pos]);
        }

        try {
            $this->mediaManager->save($media);
            $output->writeln(sprintf(' > %s - %s', $media->getId(), $media->getName()));
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>%s</error> : %s', $e->getMessage(), json_encode($data)));
        }
    }

    protected function optimize(): void
    {
        if (null !== $this->managerRegistry) {
            $this->managerRegistry->getManager()->getUnitOfWork()->clear();
        }
    }
}
