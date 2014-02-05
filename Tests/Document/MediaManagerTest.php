<?php

namespace Sonata\MediaBundle\Tests\Document;

use Sonata\MediaBundle\Document\MediaManager;

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
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected function createDocumentManagerMock()
    {
        return $this->getMockBuilder('Doctrine\ODM\MongoDB\DocumentManager')->disableOriginalConstructor()->getMock();
    }
}
