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

namespace Sonata\MediaBundle\Tests\Listener\ORM;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\Events;
use PHPUnit\Framework\TestCase;
use Sonata\ClassificationBundle\Model\CategoryInterface;
use Sonata\MediaBundle\Listener\ORM\MediaEventSubscriber;
use Sonata\MediaBundle\Model\CategoryManagerInterface;
use Sonata\MediaBundle\Model\Media;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Mathieu Lemoine <mlemoine@mlemoine.name>
 */
class MediaEventSubscriberTest extends TestCase
{
    /**
     * @see https://github.com/sonata-project/SonataClassificationBundle/issues/60
     * @see https://github.com/sonata-project/SonataMediaBundle/pull/780
     */
    public function testRefetchCategoriesAfterClear(): void
    {
        $provider = $this->createMock(MediaProviderInterface::class);

        $pool = $this->getMockBuilder(Pool::class)
            ->setMethods(['getProvider'])
            ->setConstructorArgs(['default'])
            ->getMock();

        $pool->method('getProvider')->willReturnMap([['provider', $provider]]);

        $category = $this->createMock(CategoryInterface::class);

        $catManager = $this->createMock(CategoryManagerInterface::class);

        $container = $this->createMock(ContainerInterface::class);

        $container->method('get')->willReturnMap([
            ['sonata.media.pool', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $pool],
            ['sonata.media.manager.category', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $catManager],
        ]);

        $container->method('has')
            ->with($this->equalTo('sonata.media.manager.category'))
            ->willReturn(true);

        $catManager->expects($this->exactly(2))
            ->method('getRootCategories')
            ->willReturn(['context' => $category]);

        $subscriber = new MediaEventSubscriber($container);

        $this->assertContains(Events::onClear, $subscriber->getSubscribedEvents());

        $media1 = $this->getMockBuilder(Media::class)
            ->setMethods(['getId', 'getCategory', 'getProvider', 'getContext'])
            ->getMock();
        $media1->method('getProvider')->willReturn('provider');
        $media1->method('getContext')->willReturn('context');

        $args1 = $this->getMockBuilder(EventArgs::class)
            ->setMethods(['getEntity'])
            ->getMock();
        $args1->method('getEntity')->willReturn($media1);

        $subscriber->prePersist($args1);

        $subscriber->onClear();

        $media2 = $this->getMockBuilder(Media::class)
            ->setMethods(['getId', 'getCategory', 'getProvider', 'getContext'])
            ->getMock();
        $media2->method('getProvider')->willReturn('provider');
        $media2->method('getContext')->willReturn('context');

        $args2 = $this->getMockBuilder(EventArgs::class)
            ->setMethods(['getEntity'])
            ->getMock();
        $args2->method('getEntity')->willReturn($media2);

        $subscriber->prePersist($args2);
    }
}
