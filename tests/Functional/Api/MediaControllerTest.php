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

namespace Sonata\MediaBundle\Tests\Functional;

use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;
use Sonata\MediaBundle\Tests\App\AppKernel;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

final class MediaControllerTest extends WebTestCase
{
    public function testGetMediaAction(): void
    {
        $kernel = new AppKernel();
        $kernel->boot();
        $doctrine = $kernel->getContainer()->get('doctrine');
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $interfaces = class_implements(CreateDatabaseDoctrineCommand::class);
        if (isset($interfaces[ContainerAwareInterface::class])) {
            $application->add(new DropDatabaseDoctrineCommand());
            $application->add(new CreateDatabaseDoctrineCommand());
        } else {
            $application->add(new DropDatabaseDoctrineCommand($doctrine));
            $application->add(new CreateDatabaseDoctrineCommand($doctrine));
        }

        $application->run(new ArrayInput([
            'command' => 'doctrine:database:drop',
            '--force' => '1',
        ]));

        $application->run(new ArrayInput([
            'command' => 'doctrine:database:create',
        ]));

        $command = new CreateSchemaDoctrineCommand();
        $application->add($command);
        $input = new ArrayInput([
            'command' => 'doctrine:schema:create',
        ]);
        $input->setInteractive(false);

        $command->run($input, new ConsoleOutput());

        $client = new Client($kernel);
        $client->request('GET', '/api/media');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }
}
