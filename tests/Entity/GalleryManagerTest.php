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

namespace Sonata\MediaBundle\Test\Entity;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\Doctrine\Test\EntityManagerMockFactoryTrait;
use Sonata\MediaBundle\Entity\BaseGallery;
use Sonata\MediaBundle\Entity\GalleryManager;

/**
 * @author Benoit de Jacobet <benoit.de-jacobet@ekino.com>
 */
class GalleryManagerTest extends TestCase
{
    use EntityManagerMockFactoryTrait;

    public function testGetPager(): void
    {
        $this
            ->getGalleryManager(static function (MockObject $qb): void {
                $qb->expects(self::once())->method('getRootAliases')->willReturn(['g']);
                $qb->expects(self::never())->method('andWhere');
                $qb->expects(self::once())->method('orderBy')->with('g.name', 'ASC');
                $qb->expects(self::once())->method('setParameters')->with([]);
            })
            ->getPager([], 1);
    }

    public function testGetPagerWithInvalidSort(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid sort field \'invalid\' in \'Sonata\\MediaBundle\\Entity\\BaseGallery\' class');

        $this
            ->getGalleryManager(static function (MockObject $qb): void {
            })
            ->getPager([], 1, 10, ['invalid' => 'ASC']);
    }

    public function testGetPagerWithMultipleSort(): void
    {
        $this
            ->getGalleryManager(static function (MockObject $qb): void {
                $qb->expects(self::once())->method('getRootAliases')->willReturn(['g']);
                $qb->expects(self::never())->method('andWhere');
                $qb->expects(self::exactly(2))->method('orderBy')->with(
                    self::logicalOr('g.name', 'g.context'),
                    self::logicalOr('ASC', 'DESC')
                );
                $qb->expects(self::once())->method('setParameters')->with([]);
            })
            ->getPager([], 1, 10, [
                'name' => 'ASC',
                'context' => 'DESC',
            ]);
    }

    public function testGetPagerWithEnabledGalleries(): void
    {
        $this
            ->getGalleryManager(static function (MockObject $qb): void {
                $qb->expects(self::once())->method('getRootAliases')->willReturn(['g']);
                $qb->expects(self::once())->method('andWhere')->with('g.enabled = :enabled');
                $qb->expects(self::once())->method('setParameters')->with(['enabled' => true]);
            })
            ->getPager(['enabled' => true], 1);
    }

    public function testGetPagerWithNoEnabledGalleries(): void
    {
        $this
            ->getGalleryManager(static function (MockObject $qb): void {
                $qb->expects(self::once())->method('getRootAliases')->willReturn(['g']);
                $qb->expects(self::once())->method('andWhere')->with('g.enabled = :enabled');
                $qb->expects(self::once())->method('setParameters')->with(['enabled' => false]);
            })
            ->getPager(['enabled' => false], 1);
    }

    private function getGalleryManager(\Closure $qbCallback): GalleryManager
    {
        $em = $this->createEntityManagerMock($qbCallback, [
            'name',
            'context',
            'enabled',
        ]);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($em);

        return new GalleryManager(BaseGallery::class, $registry);
    }
}
