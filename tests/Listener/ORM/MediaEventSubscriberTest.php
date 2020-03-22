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

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use PHPUnit\Framework\TestCase;
use Sonata\ClassificationBundle\Model\CategoryInterface;
use Sonata\MediaBundle\Listener\ORM\MediaEventSubscriber;
use Sonata\MediaBundle\Model\CategoryManagerInterface;
use Sonata\MediaBundle\Model\Media;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\DependencyInjection\Container;

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
            ->onlyMethods(['getProvider'])
            ->setConstructorArgs(['default'])
            ->getMock();

        $pool->method('getProvider')->willReturnMap([['provider', $provider]]);

        $category = $this->createMock(CategoryInterface::class);

        $catManager = $this->createMock(CategoryManagerInterface::class);

        $container = new Container();

        $container->set('sonata.media.pool', $pool);
        $container->set('sonata.media.manager.category', $catManager);

        $catManager->expects($this->exactly(2))
            ->method('getRootCategories')
            ->willReturn(['context' => $category]);

        $subscriber = new MediaEventSubscriber($container);

        $this->assertContains(Events::onClear, $subscriber->getSubscribedEvents());

        $media1 = $this->createMock(Media::class);
        $media1->method('getContext')->willReturn('context');

        $args1 = $this->createMock(LifecycleEventArgs::class);
        $args1->method('getEntity')->willReturn($media1);

        $subscriber->prePersist($args1);

        $subscriber->onClear();

        $media2 = $this->createMock(Media::class);
        $media2->method('getContext')->willReturn('context');

        $args2 = $this->createMock(LifecycleEventArgs::class);
        $args2->method('getEntity')->willReturn($media2);

        $subscriber->prePersist($args2);
    }
}
