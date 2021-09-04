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

namespace Sonata\MediaBundle\Tests\PHPCR;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\PHPCR\MediaManager;

/**
 * @group document
 * @group PHPCR
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

        static::assertSame('default', $media->getContext());
        static::assertSame('media.test', $media->getProviderName());

        $media = new Media();
        $this->manager->save($media, true);

        static::assertNull($media->getContext());
        static::assertNull($media->getProviderName());
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
        $dm = $this->createStub(ObjectManager::class);
        $registry = $this->createMock(ManagerRegistry::class);

        $dm->method('persist');
        $dm->method('flush');
        $registry->method('getManagerForClass')->willReturn($dm);

        return $registry;
    }
}
