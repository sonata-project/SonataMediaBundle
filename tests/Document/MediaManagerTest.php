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

namespace Sonata\MediaBundle\Tests\Document;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Document\MediaManager;
use Sonata\MediaBundle\Model\MediaInterface;

/**
 * @group document
 * @group mongo
 */
class MediaManagerTest extends TestCase
{
    /**
     * @var MediaManager
     */
    private $manager;

    protected function setUp(): void
    {
        $this->manager = new MediaManager(MediaInterface::class, $this->createRegistryMock());
    }

    public function testPagerException(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $this->manager->getPager([], 1);
    }

    private function createRegistryMock(): ManagerRegistry
    {
        $dm = $this->createStub(ObjectManager::class);
        $registry = $this->createMock(ManagerRegistry::class);

        $registry->method('getManagerForClass')->willReturn($dm);

        return $registry;
    }
}
