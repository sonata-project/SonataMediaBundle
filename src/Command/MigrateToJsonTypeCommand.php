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

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class MigrateToJsonTypeCommand extends Command
{
    protected static $defaultName = 'sonata:media:migrate-json';
    protected static $defaultDescription = 'Migrate all media provider metadata to the doctrine JsonType';

    /**
     * @var EntityManagerInterface|null
     */
    private $entityManager;

    public function __construct(?EntityManagerInterface $entityManager = null)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(static::$defaultDescription)
            ->addOption('table', null, InputOption::VALUE_OPTIONAL, 'Media table', 'media__media')
            ->addOption('column', null, InputOption::VALUE_OPTIONAL, 'Column name for provider_metadata', 'provider_metadata')
            ->addOption('column_id', null, InputOption::VALUE_OPTIONAL, 'Column name for id', 'id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (null === $this->entityManager) {
            throw new \LogicException(
                'This command could not be executed since one of its dependencies is missing.'
                .' Is the service "doctrine.orm.entity_manager" available?'
            );
        }

        $count = 0;

        $table = $input->getOption('table');
        \assert(\is_string($table));
        $column = $input->getOption('column');
        \assert(\is_string($column));
        $columnId = $input->getOption('column_id');
        \assert(\is_string($columnId));

        $medias = $this->entityManager->getConnection()->fetchAllAssociative("SELECT * FROM $table");

        foreach ($medias as $media) {
            // if the row need to migrate
            if (0 !== strpos($media[$column], '{') && '[]' !== $media[$column]) {
                $media[$column] = json_encode(unserialize($media[$column]));
                $this->entityManager->getConnection()->update($table, [$column => $media[$column]], [$columnId => $media[$columnId]]);
                ++$count;
            }
        }

        $output->writeln("Migrated $count medias");

        return 0;
    }
}
