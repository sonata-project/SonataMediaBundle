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

namespace Sonata\MediaBundle\Tests\Controller\Api;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use PHPUnit\Framework\TestCase;
use Sonata\DatagridBundle\Pager\PagerInterface;
use Sonata\MediaBundle\Controller\Api\MediaController;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Hugo Briand <briand@ekino.com>
 */
class MediaControllerTest extends TestCase
{
    public function testGetMediaAction(): void
    {
        $pager = $this->createStub(PagerInterface::class);
        $mManager = $this->createMock(MediaManagerInterface::class);

        $mManager->expects($this->once())->method('getPager')->willReturn($pager);

        $mController = $this->createMediaController($mManager);

        $paramFetcher = $this->createMock(ParamFetcherInterface::class);
        $paramFetcher->expects($this->exactly(3))->method('get');
        $paramFetcher->expects($this->once())->method('all')->willReturn([]);

        $this->assertSame($pager, $mController->getMediaAction($paramFetcher));
    }

    public function testGetMediumAction(): void
    {
        $media = $this->createMock(MediaInterface::class);

        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects($this->once())->method('find')->willReturn($media);

        $controller = $this->createMediaController($manager);

        $this->assertSame($media, $controller->getMediumAction(1));
    }

    public function testGetMediumNotFoundExceptionAction(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Media (42) was not found');

        $this->createMediaController()->getMediumAction(42);
    }

    public function testGetMediumFormatsAction(): void
    {
        $media = $this->createMock(MediaInterface::class);

        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects($this->once())->method('find')->willReturn($media);

        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects($this->exactly(2))->method('getHelperProperties')->willReturn(['foo' => 'bar']);

        $pool = $this->createMock(Pool::class);
        $pool->method('getProvider')->willReturn($provider);
        $pool->expects($this->once())->method('getFormatNamesByContext')->willReturn(['format_name1' => 'value1']);

        $controller = $this->createMediaController($manager, $pool);

        $expected = [
            'reference' => [
                'url' => null,
                'properties' => [
                    'foo' => 'bar',
                ],
            ],
            'format_name1' => [
                'url' => null,
                'properties' => [
                    'foo' => 'bar',
                ],
            ],
        ];
        $this->assertSame($expected, $controller->getMediumFormatsAction(1));
    }

    public function testGetMediumBinariesAction(): void
    {
        $media = $this->createMock(MediaInterface::class);

        $binaryResponse = $this->createMock(BinaryFileResponse::class);

        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects($this->once())->method('find')->willReturn($media);

        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects($this->once())->method('getDownloadResponse')->willReturn($binaryResponse);

        $pool = $this->createMock(Pool::class);
        $pool->expects($this->once())->method('getProvider')->willReturn($provider);

        $controller = $this->createMediaController($manager, $pool);

        $this->assertSame($binaryResponse, $controller->getMediumBinaryAction(1, 'format', new Request()));
    }

    public function testDeleteMediumAction(): void
    {
        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects($this->once())->method('delete');
        $manager->expects($this->once())->method('find')->willReturn($this->createMock(MediaInterface::class));

        $controller = $this->createMediaController($manager);

        $expected = ['deleted' => true];

        $this->assertSame($expected, $controller->deleteMediumAction(1));
    }

    public function testPutMediumAction(): void
    {
        $medium = $this->createMock(MediaInterface::class);

        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects($this->once())->method('find')->willReturn($medium);

        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects($this->once())->method('getName');

        $pool = $this->createMock(Pool::class);
        $pool->expects($this->once())->method('getProvider')->willReturn($provider);

        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->willReturn(true);
        $form->expects($this->once())->method('getData')->willReturn($medium);

        $factory = $this->createMock(FormFactoryInterface::class);
        $factory->expects($this->once())->method('createNamed')->willReturn($form);

        $controller = $this->createMediaController($manager, $pool, $factory);

        $this->assertInstanceOf(View::class, $controller->putMediumAction(1, new Request()));
    }

    public function testPutMediumInvalidFormAction(): void
    {
        $medium = $this->createMock(MediaInterface::class);

        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects($this->once())->method('find')->willReturn($medium);

        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects($this->once())->method('getName');

        $pool = $this->createMock(Pool::class);
        $pool->expects($this->once())->method('getProvider')->willReturn($provider);

        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->willReturn(false);

        $factory = $this->createMock(FormFactoryInterface::class);
        $factory->expects($this->once())->method('createNamed')->willReturn($form);

        $controller = $this->createMediaController($manager, $pool, $factory);

        $this->assertInstanceOf(Form::class, $controller->putMediumAction(1, new Request()));
    }

    public function testPostProviderMediumAction(): void
    {
        $medium = $this->createMock(MediaInterface::class);
        $medium->expects($this->once())->method('setProviderName');

        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects($this->once())->method('create')->willReturn($medium);

        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects($this->once())->method('getName');

        $pool = $this->createMock(Pool::class);
        $pool->expects($this->once())->method('getProvider')->willReturn($provider);

        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->willReturn(true);
        $form->expects($this->once())->method('getData')->willReturn($medium);

        $factory = $this->createMock(FormFactoryInterface::class);
        $factory->expects($this->once())->method('createNamed')->willReturn($form);

        $controller = $this->createMediaController($manager, $pool, $factory);

        $this->assertInstanceOf(View::class, $controller->postProviderMediumAction('providerName', new Request()));
    }

    public function testPostProviderActionNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $medium = $this->createMock(MediaInterface::class);
        $medium->expects($this->once())->method('setProviderName');

        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects($this->once())->method('create')->willReturn($medium);

        $pool = $this->createMock(Pool::class);
        $pool->expects($this->once())->method('getProvider')->will($this->throwException(new \RuntimeException('exception on getProvder')));

        $controller = $this->createMediaController($manager, $pool);
        $controller->postProviderMediumAction('non existing provider', new Request());
    }

    public function testPutMediumBinaryContentAction(): void
    {
        $media = $this->createMock(MediaInterface::class);
        $media->expects($this->once())->method('setBinaryContent');

        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects($this->once())->method('find')->willReturn($media);

        $pool = $this->createMock(Pool::class);

        $controller = $this->createMediaController($manager, $pool);

        $this->assertSame($media, $controller->putMediumBinaryContentAction(1, new Request()));
    }

    protected function createMediaController(
        ?MediaManagerInterface $manager = null,
        ?Pool $pool = null,
        ?FormFactoryInterface $factory = null
    ): MediaController {
        if (null === $manager) {
            $manager = $this->createMock(MediaManagerInterface::class);
        }
        if (null === $pool) {
            $pool = $this->createMock(Pool::class);
        }
        if (null === $factory) {
            $factory = $this->createMock(FormFactoryInterface::class);
        }

        return new MediaController($manager, $pool, $factory);
    }
}
