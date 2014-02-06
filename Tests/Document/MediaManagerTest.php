<?php

namespace Sonata\MediaBundle\Tests\Document;

use Sonata\MediaBundle\Document\MediaManager;

/**
 * @group document
 * @group mongo
 */
class MediaManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var MediaManager */
    private $manager;

    public function testSave()
    {
        $media = new Media();
        $this->manager->save($media, 'default', 'media.test');

        $this->assertEquals('default', $media->getContext());
        $this->assertEquals('media.test', $media->getProviderName());

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

    protected function setUp()
    {
        if (!class_exists('Doctrine\\ODM\MongoDB\\DocumentManager', true)) {
            $this->markTestSkipped('Sonata\\MediaBundle\\Document\\MediaManager requires "Doctrine\\ODM\\MongoDB" lib.');
        }

        $this->manager = new MediaManager($this->createPoolMock(), $this->createDocumentManagerMock(), null);
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
