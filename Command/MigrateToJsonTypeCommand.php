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

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

class MigrateToJsonTypeCommand extends BaseCommand
{
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
        $count = 0;
        $table = $input->getOption('table');
        $column = $input->getOption('column');
        $columnId = $input->getOption('column_id');
        $connection = $this->getContainer()->get('doctrine.orm.entity_manager')->getConnection();
        $medias = $connection->fetchAll("SELECT * FROM $table");

        foreach ($medias as $media) {
            // if the row need to migrate
            if (0 !== strpos($media[$column], '{') && $media[$column] !== '[]') {
                $media[$column] = json_encode(unserialize($media[$column]));
                $connection->update($table, array($column => $media[$column]), array($columnId => $media[$columnId]));
                $count++;
            }
        }

        $output->writeln("Migrated $count medias");
    }
}
