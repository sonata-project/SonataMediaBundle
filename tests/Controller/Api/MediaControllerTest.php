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

use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use PHPUnit\Framework\TestCase;
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
    public function testGetMediaAction()
    {
        $mManager = $this->createMock(MediaManagerInterface::class);
        $media = $this->createMock(MediaInterface::class);

        $mManager->expects($this->once())->method('getPager')->will($this->returnValue([$media]));

        $mController = $this->createMediaController($mManager);

        $paramFetcher = $this->createMock(ParamFetcher::class);
        $paramFetcher->expects($this->exactly(3))->method('get');
        $paramFetcher->expects($this->once())->method('all')->will($this->returnValue([]));

        $this->assertSame([$media], $mController->getMediaAction($paramFetcher));
    }

    public function testGetMediumAction()
    {
        $media = $this->createMock(MediaInterface::class);

        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects($this->once())->method('findOneBy')->will($this->returnValue($media));

        $controller = $this->createMediaController($manager);

        $this->assertSame($media, $controller->getMediumAction(1));
    }

    public function testGetMediumNotFoundExceptionAction()
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Media (42) was not found');

        $this->createMediaController()->getMediumAction(42);
    }

    public function testGetMediumFormatsAction()
    {
        $media = $this->createMock(MediaInterface::class);

        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects($this->once())->method('findOneBy')->will($this->returnValue($media));

        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects($this->exactly(2))->method('getHelperProperties')->will($this->returnValue(['foo' => 'bar']));

        $pool = $this->createMock(Pool::class);
        $pool->expects($this->any())->method('getProvider')->will($this->returnValue($provider));
        $pool->expects($this->once())->method('getFormatNamesByContext')->will($this->returnValue(['format_name1' => 'value1']));

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

    public function testGetMediumBinariesAction()
    {
        $media = $this->createMock(MediaInterface::class);

        $binaryResponse = $this->createMock(BinaryFileResponse::class);

        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects($this->once())->method('findOneBy')->will($this->returnValue($media));

        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects($this->once())->method('getDownloadResponse')->will($this->returnValue($binaryResponse));

        $pool = $this->createMock(Pool::class);
        $pool->expects($this->once())->method('getProvider')->will($this->returnValue($provider));

        $controller = $this->createMediaController($manager, $pool);

        $this->assertSame($binaryResponse, $controller->getMediumBinaryAction(1, 'format', new Request()));
    }

    public function testDeleteMediumAction()
    {
        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects($this->once())->method('delete');
        $manager->expects($this->once())->method('findOneBy')->will($this->returnValue($this->createMock(MediaInterface::class)));

        $controller = $this->createMediaController($manager);

        $expected = ['deleted' => true];

        $this->assertSame($expected, $controller->deleteMediumAction(1));
    }

    public function testPutMediumAction()
    {
        $medium = $this->createMock(MediaInterface::class);

        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects($this->once())->method('findOneBy')->will($this->returnValue($medium));

        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects($this->once())->method('getName');

        $pool = $this->createMock(Pool::class);
        $pool->expects($this->once())->method('getProvider')->will($this->returnValue($provider));

        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $form->expects($this->once())->method('getData')->will($this->returnValue($medium));

        $factory = $this->createMock(FormFactoryInterface::class);
        $factory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $controller = $this->createMediaController($manager, $pool, $factory);

        $this->assertInstanceOf(View::class, $controller->putMediumAction(1, new Request()));
    }

    public function testPutMediumInvalidFormAction()
    {
        $medium = $this->createMock(MediaInterface::class);

        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects($this->once())->method('findOneBy')->will($this->returnValue($medium));

        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects($this->once())->method('getName');

        $pool = $this->createMock(Pool::class);
        $pool->expects($this->once())->method('getProvider')->will($this->returnValue($provider));

        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(false));

        $factory = $this->createMock(FormFactoryInterface::class);
        $factory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $controller = $this->createMediaController($manager, $pool, $factory);

        $this->assertInstanceOf(Form::class, $controller->putMediumAction(1, new Request()));
    }

    public function testPostProviderMediumAction()
    {
        $medium = $this->createMock(MediaInterface::class);
        $medium->expects($this->once())->method('setProviderName');

        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects($this->once())->method('create')->will($this->returnValue($medium));

        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects($this->once())->method('getName');

        $pool = $this->createMock(Pool::class);
        $pool->expects($this->once())->method('getProvider')->will($this->returnValue($provider));

        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $form->expects($this->once())->method('getData')->will($this->returnValue($medium));

        $factory = $this->createMock(FormFactoryInterface::class);
        $factory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $controller = $this->createMediaController($manager, $pool, $factory);

        $this->assertInstanceOf(View::class, $controller->postProviderMediumAction('providerName', new Request()));
    }

    public function testPostProviderActionNotFound()
    {
        $this->expectException(NotFoundHttpException::class);

        $medium = $this->createMock(MediaInterface::class);
        $medium->expects($this->once())->method('setProviderName');

        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects($this->once())->method('create')->will($this->returnValue($medium));

        $pool = $this->createMock(Pool::class);
        $pool->expects($this->once())->method('getProvider')->will($this->throwException(new \RuntimeException('exception on getProvder')));

        $controller = $this->createMediaController($manager, $pool);
        $controller->postProviderMediumAction('non existing provider', new Request());
    }

    public function testPutMediumBinaryContentAction()
    {
        $media = $this->createMock(MediaInterface::class);
        $media->expects($this->once())->method('setBinaryContent');

        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects($this->once())->method('findOneBy')->will($this->returnValue($media));

        $pool = $this->createMock(Pool::class);

        $controller = $this->createMediaController($manager, $pool);

        $this->assertSame($media, $controller->putMediumBinaryContentAction(1, new Request()));
    }

    protected function createMediaController($manager = null, $pool = null, $factory = null)
    {
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
