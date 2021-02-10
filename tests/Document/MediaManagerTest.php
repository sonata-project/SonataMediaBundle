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

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Document\MediaManager;
use Sonata\MediaBundle\Model\MediaInterface;

/**
 * @group document
 * @group mongo
 */
class MediaManagerTest extends TestCase
{
    /** @var MediaManager */
    private $manager;

    protected function setUp(): void
    {
        $this->manager = new MediaManager(MediaInterface::class, $this->createRegistryMock());
    }

    public function testSave(): void
    {
        $media = new Media();
        $this->manager->save($media, 'default', 'media.test');

        $this->assertSame('default', $media->getContext());
        $this->assertSame('media.test', $media->getProviderName());

        $media = new Media();
        $this->manager->save($media, true);

        $this->assertNull($media->getContext());
        $this->assertNull($media->getProviderName());
    }

    public function testSaveException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->manager->save(null);
    }

    public function testDeleteException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->manager->delete(null);
    }

    protected function createRegistryMock(): ManagerRegistry
    {
        $dm = $this->getMockBuilder(DocumentManager::class)
            ->setMethods(['persist', 'flush'])
            ->disableOriginalConstructor()
            ->getMock();
        $registry = $this->createMock(ManagerRegistry::class);

        $dm->method('persist');
        $dm->method('flush');
        $registry->method('getManagerForClass')->willReturn($dm);

        return $registry;
    }
}
