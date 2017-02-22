<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Document;

use Sonata\MediaBundle\Document\MediaManager;
use Sonata\MediaBundle\Tests\Helpers\PHPUnit_Framework_TestCase;

/**
 * @group document
 * @group mongo
 */
class MediaManagerTest extends PHPUnit_Framework_TestCase
{
    /** @var MediaManager */
    private $manager;

    protected function setUp()
    {
        $this->manager = new MediaManager('Sonata\MediaBundle\Model\MediaInterface', $this->createRegistryMock());
    }

    public function testSave()
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

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSaveException()
    {
        $this->manager->save(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDeleteException()
    {
        $this->manager->delete(null);
    }

    /**
     * Returns mock of doctrine document manager.
     *
     * @return \Sonata\DoctrinePHPCRAdminBundle\Model\ModelManager
     */
    protected function createRegistryMock()
    {
        $dm = $this->getMockBuilder('Doctrine\ODM\MongoDB\DocumentManager')
            ->setMethods(array('persist', 'flush'))
            ->disableOriginalConstructor()
            ->getMock();
        $registry = $this->createMock('Doctrine\Common\Persistence\ManagerRegistry');

        $dm->expects($this->any())->method('persist');
        $dm->expects($this->any())->method('flush');
        $registry->expects($this->any())->method('getManagerForClass')->will($this->returnValue($dm));

        return $registry;
    }
}
