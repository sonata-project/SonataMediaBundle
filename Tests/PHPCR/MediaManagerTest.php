<?php

namespace Sonata\MediaBundle\Tests\PHPCR;

use Sonata\MediaBundle\PHPCR\MediaManager;

/**
 * @group document
 * @group PHPCR
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
        if (!class_exists('Doctrine\\ODM\\PHPCR\\DocumentManager', true)) {
            $this->markTestSkipped('Sonata\\MediaBundle\\PHPCR\\MediaManager requires "Doctrine\\ODM\\PHPCR" lib.');
        }

        $this->manager = new MediaManager('Sonata\MediaBundle\Model\MediaInterface', $this->createRegistryMock());
    }

    /**
     * Returns mock of doctrine document manager.
     *
     * @return \Sonata\DoctrinePHPCRAdminBundle\Model\ModelManager
     */
    protected function createRegistryMock()
    {
        $dm = $this->getMockBuilder('Doctrine\ODM\PHPCR\DocumentManager')->disableOriginalConstructor()->getMock();

        $registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $registry->expects($this->any())->method('getManagerForClass')->will($this->returnValue($dm));

        return $registry;
    }
}
