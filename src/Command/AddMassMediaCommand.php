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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class AddMassMediaCommand extends BaseCommand
{
    /**
     * @var string[]
     */
    protected $setters;

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('sonata:media:add-multiple')
            ->setDescription('Add medias in mass into the database')
            ->setDefinition([
                new InputOption('file', null, InputOption::VALUE_OPTIONAL, 'The file to parse'),
                new InputOption('delimiter', null, InputOption::VALUE_OPTIONAL, 'Set the field delimiter (one character only)', ','),
                new InputOption('enclosure', null, InputOption::VALUE_OPTIONAL, 'Set the field enclosure character (one character only).', '"'),
                new InputOption('escape', null, InputOption::VALUE_OPTIONAL, 'Set the escape character (one character only). Defaults as a backslash', '\\'),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
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
        if (false !== ftell(STDIN)) {
            return STDIN;
        }

        if (!$input->getOption('file')) {
            throw new \RuntimeException('Please provide a CSV file argument or CSV input stream');
        }

        return fopen($input->getOption('file'), 'r');
    }

    protected function insertMedia(array $data, OutputInterface $output)
    {
        $media = $this->getMediaManager()->create();

        foreach ($this->setters as $pos => $name) {
            $media->{'set'.$name}($data[$pos]);
        }

        try {
            $this->getMediaManager()->save($media);
            $output->writeln(sprintf(' > %s - %s', $media->getId(), $media->getName()));
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>%s</error> : %s', $e->getMessage(), json_encode($data)));
        }
    }

    protected function optimize()
    {
        if ($this->getContainer()->has('doctrine')) {
            $this->getContainer()->get('doctrine')->getManager()->getUnitOfWork()->clear();
        }
    }
}
