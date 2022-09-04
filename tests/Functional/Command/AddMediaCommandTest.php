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

namespace Sonata\MediaBundle\Tests\Functional\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class AddMediaCommandTest extends KernelTestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        static::bootKernel();

        $this->commandTester = new CommandTester(
            (new Application(static::createKernel()))->find('sonata:media:add')
        );
    }

    public function testAddsMedia(): void
    {
        $this->commandTester->execute([
            'providerName' => 'sonata.media.provider.image',
            'context' => 'default',
            'binaryContent' => realpath(__DIR__.'/../../Fixtures/logo.png'),
        ]);

        static::assertStringContainsString('done!', $this->commandTester->getDisplay());
    }
}
