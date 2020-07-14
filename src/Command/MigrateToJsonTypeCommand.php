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
use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class MigrateToJsonTypeCommand extends BaseCommand
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(ManagerInterface $mediaManager, Pool $pool, ?EntityManagerInterface $entityManager = null)
    {
        parent::__construct($mediaManager, $pool);

        $this->entityManager = $entityManager;
    }

    public function configure()
    {
        $this->setName('sonata:media:migrate-json');
        $this->addOption('table', null, InputOption::VALUE_OPTIONAL, 'Media table', 'media__media');
        $this->addOption('column', null, InputOption::VALUE_OPTIONAL, 'Column name for provider_metadata', 'provider_metadata');
        $this->addOption('column_id', null, InputOption::VALUE_OPTIONAL, 'Column name for id', 'id');
        $this->setDescription('Migrate all media provider metadata to the doctrine JsonType');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (null === $this->entityManager) {
            throw new \LogicException(
                'This command could not be executed since one of its dependencies is missing.'
                .' Is the service "doctrine.orm.entity_manager" available?'
            );
        }

        $count = 0;
        $table = $input->getOption('table');
        $column = $input->getOption('column');
        $columnId = $input->getOption('column_id');
        $medias = $this->entityManager->getConnection()->fetchAll("SELECT * FROM $table");

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
