<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Listener\ORM;

use Doctrine\ORM\Events;
use Sonata\MediaBundle\Listener\ORM\MediaEventSubscriber;
use Sonata\MediaBundle\Model\Media;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Mathieu Lemoine <mlemoine@mlemoine.name>
 */
class MediaEventSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @see https://github.com/sonata-project/SonataClassificationBundle/issues/60
     * @see https://github.com/sonata-project/SonataMediaBundle/pull/780
     */
    public function testRefetchCategoriesAfterClear()
    {
        $provider = $this->getMock('Sonata\\MediaBundle\\Provider\\MediaProviderInterface');

        $pool = $this->getMock('Sonata\\MediaBundle\\Provider\\Pool', array(), array('default'));

        $pool->method('getProvider')->will($this->returnValueMap(array(array('provider', $provider))));

        $category = $this->getMock('Sonata\\ClassificationBundle\\Model\\CategoryInterface');

        $catManager = $this->getMockBuilder('Sonata\\ClassificationBundle\\Entity\\CategoryManager', array('getRootCategories'))
                           ->disableOriginalConstructor()
                           ->getMock();

        $container = $this->getMock('Symfony\\Component\\DependencyInjection\\ContainerInterface');

        $container->method('get')->will($this->returnValueMap(array(
            array('sonata.media.pool', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $pool),
            array('sonata.classification.manager.category', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $catManager),
        )));

        $catManager->expects($this->exactly(2))
                   ->method('getRootCategories')
                   ->willReturn(array('context' => $category))
                       ;

        $subscriber = new MediaEventSubscriber($container);

        $this->assertContains(Events::onClear, $subscriber->getSubscribedEvents());

        $media1 = $this->getMock('Sonata\\MediaBundle\\Model\\Media', array('getId', 'getCategory', 'getProvider', 'getContext'));
        $media1->method('getProvider')->willReturn('provider');
        $media1->method('getContext')->willReturn('context');

        $args1 = $this->getMock('Doctrine\\Common\\EventArgs', array('getEntity'));
        $args1->method('getEntity')->willReturn($media1);

        $subscriber->prePersist($args1);

        $subscriber->onClear();

        $media2 = $this->getMock('Sonata\\MediaBundle\\Model\\Media', array('getId', 'getCategory', 'getProvider', 'getContext'));
        $media2->method('getProvider')->willReturn('provider');
        $media2->method('getContext')->willReturn('context');

        $args2 = $this->getMock('Doctrine\\Common\\EventArgs', array('getEntity'));
        $args2->method('getEntity')->willReturn($media2);

        $subscriber->prePersist($args2);
    }
}
