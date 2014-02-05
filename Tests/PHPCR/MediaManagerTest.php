<?php

namespace Sonata\MediaBundle\Tests\PHPCR;

use Sonata\MediaBundle\PHPCR\MediaManager;

class MediaManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testSave()
    {
        $manager = new MediaManager($this->createPoolMock(), $this->createDocumentManagerMock(), null);

        $media = new Media();
        $manager->save($media, 'default', 'media.test');

        $this->assertEquals('default', $media->getContext());
        $this->assertEquals('media.test', $media->getProviderName());

        $media = new Media();
        $manager->save($media, true);

        $this->assertNull($media->getContext());
        $this->assertNull($media->getProviderName());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSaveException()
    {
        $manager = new MediaManager($this->createPoolMock(), $this->createDocumentManagerMock(), null);

        $manager->save(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDeleteException()
    {
        $manager = new MediaManager($this->createPoolMock(), $this->createDocumentManagerMock(), null);

        $manager->delete(null);
    }

    /**
     * Returns mock of pool provider.
     *
     * @return \Sonata\MediaBundle\Provider\Pool
     */
    protected function createPoolMock()
    {
        return $this->getMockBuilder('Sonata\MediaBundle\Provider\Pool')->disableOriginalConstructor()->getMock();
    }

    /**
     * Returns mock of doctrine document manager.
     *
     * @return \Sonata\DoctrinePHPCRAdminBundle\Model\ModelManager
     */
    protected function createDocumentManagerMock()
    {
        $dm = $this->getMockBuilder('Doctrine\ODM\PHPCR\DocumentManager')->disableOriginalConstructor()->getMock();

        $manager = $this->getMockBuilder('Sonata\DoctrinePHPCRAdminBundle\Model\ModelManager')->disableOriginalConstructor()->getMock();
        $manager->expects($this->any())
            ->method('getDocumentManager')
            ->will($this->returnValue($dm));

        return $manager;
    }
}
