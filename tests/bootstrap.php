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

use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;
use Sonata\MediaBundle\Tests\App\AppKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*
 * fix encoding issue while running text on different host with different locale configuration
 */
setlocale(LC_ALL, 'en_US.UTF-8');

if (file_exists($file = __DIR__.'/autoload.php')) {
    require_once $file;
} elseif (file_exists($file = __DIR__.'/autoload.php.dist')) {
    require_once $file;
}

/*
 * try to get Symfony's PHPUnit Bridge
 */
$files = array_filter([
    __DIR__.'/../vendor/symfony/symfony/src/Symfony/Bridge/PhpUnit/bootstrap.php',
    __DIR__.'/../vendor/symfony/phpunit-bridge/bootstrap.php',
    __DIR__.'/../../../../vendor/symfony/symfony/src/Symfony/Bridge/PhpUnit/bootstrap.php',
    __DIR__.'/../../../../vendor/symfony/phpunit-bridge/bootstrap.php',
], 'file_exists');

if ($files) {
    require_once current($files);
}

$kernel = new AppKernel();
$kernel->boot();
$doctrine = $kernel->getContainer()->get('doctrine');
$application = new Application($kernel);
$application->setAutoExit(false);

$interfaces = class_implements('Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand');

if (isset($interfaces['Symfony\Component\DependencyInjection\ContainerAwareInterface'])) {
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
