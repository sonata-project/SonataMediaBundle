<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Test\Entity;

use Sonata\CoreBundle\Test\EntityManagerMockFactory;
use Sonata\MediaBundle\Entity\MediaManager;

/**
 * Class MediaManagerTest.
 *
 *
 * @author Benoit de Jacobet <benoit.de-jacobet@ekino.com>
 */
class MediaManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetPager()
    {
        $self = $this;
        $this
            ->getMediaManager(function ($qb) use ($self) {
                $qb->expects($self->never())->method('andWhere');
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(array()));
            })
            ->getPager(array(), 1);
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Invalid sort field 'invalid' in 'Sonata\MediaBundle\Entity\BaseMedia' class
     */
    public function testGetPagerWithInvalidSort()
    {
        $self = $this;
        $this
            ->getMediaManager(function ($qb) use ($self) {
            })
            ->getPager(array(), 1, 10, array('invalid' => 'ASC'));
    }

    public function testGetPagerWithMultipleSort()
    {
        $self = $this;
        $this
            ->getMediaManager(function ($qb) use ($self) {
                $qb->expects($self->never())->method('andWhere');
                $qb->expects($self->exactly(2))->method('orderBy')->with(
                    $self->logicalOr(
                        $self->equalTo('m.name'),
                        $self->equalTo('m.description')
                    ),
                    $self->logicalOr(
                        $self->equalTo('ASC'),
                        $self->equalTo('DESC')
                    )
                );
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(array()));
            })
            ->getPager(array(), 1, 10, array(
                'name' => 'ASC',
                'description' => 'DESC',
            ));
    }

    public function testGetPagerWithEnabledMedia()
    {
        $self = $this;
        $this
            ->getMediaManager(function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('m.enabled = :enabled'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(array('enabled' => true)));
            })
            ->getPager(array('enabled' => true), 1);
    }

    public function testGetPagerWithNoEnabledMedias()
    {
        $self = $this;
        $this
            ->getMediaManager(function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('m.enabled = :enabled'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(array('enabled' => false)));
            })
            ->getPager(array('enabled' => false), 1);
    }

    protected function getMediaManager($qbCallback)
    {
        $em = EntityManagerMockFactory::create($this, $qbCallback, array(
            'name',
            'description',
            'enabled',
        ));

        $registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $registry->expects($this->any())->method('getManagerForClass')->will($this->returnValue($em));

        return new MediaManager('Sonata\MediaBundle\Entity\BaseMedia', $registry);
    }
}
