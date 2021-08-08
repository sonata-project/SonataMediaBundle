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

        $mManager->expects(self::once())->method('getPager')->willReturn($pager);

        $mController = $this->createMediaController($mManager);

        $paramFetcher = $this->createMock(ParamFetcherInterface::class);
        $paramFetcher->expects(self::exactly(3))->method('get');
        $paramFetcher->expects(self::once())->method('all')->willReturn([]);

        self::assertSame($pager, $mController->getMediaAction($paramFetcher));
    }

    public function testGetMediumAction(): void
    {
        $media = $this->createMock(MediaInterface::class);

        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects(self::once())->method('find')->willReturn($media);

        $controller = $this->createMediaController($manager);

        self::assertSame($media, $controller->getMediumAction(1));
    }

    /**
     * @dataProvider getIdsForNotFound
     *
     * @param int|string $identifier
     */
    public function testGetMediumNotFoundExceptionAction($identifier, string $message): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage($message);

        $this->createMediaController()->getMediumAction($identifier);
    }

    /**
     * @phpstan-return iterable<array{int|string, string}>
     */
    public function getIdsForNotFound(): iterable
    {
        yield [42, 'Media not found for identifier 42.'];
        yield ['42', 'Media not found for identifier \'42\'.'];
        yield ['', 'Media not found for identifier \'\'.'];
    }

    public function testGetMediumFormatsAction(): void
    {
        $media = $this->createMock(MediaInterface::class);
        $media->method('getContext')->willReturn('context');
        $media->method('getProviderName')->willReturn('provider');

        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects(self::once())->method('find')->willReturn($media);

        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects(self::exactly(2))->method('getHelperProperties')->willReturn(['foo' => 'bar']);

        $pool = new Pool('default');
        $pool->addProvider('provider', $provider);
        $pool->addContext('context', [], ['format_name1' => [
            'width' => null,
            'height' => null,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => false,
            'resizer' => false,
            'resizer_options' => [],
        ]]);

        $controller = $this->createMediaController($manager, $pool);

        $expected = [
            'reference' => [
                'url' => '',
                'properties' => [
                    'foo' => 'bar',
                ],
            ],
            'format_name1' => [
                'url' => '',
                'properties' => [
                    'foo' => 'bar',
                ],
            ],
        ];
        self::assertSame($expected, $controller->getMediumFormatsAction(1)->getData());
    }

    public function testGetMediumBinariesAction(): void
    {
        $media = $this->createMock(MediaInterface::class);
        $media->method('getContext')->willReturn('default');
        $media->method('getProviderName')->willReturn('provider');

        $binaryResponse = $this->createMock(BinaryFileResponse::class);

        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects(self::once())->method('find')->willReturn($media);

        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects(self::once())->method('getDownloadResponse')->willReturn($binaryResponse);

        $pool = new Pool('default');
        $pool->addContext('default', [], [], ['mode' => 'mode']);
        $pool->addProvider('provider', $provider);

        $controller = $this->createMediaController($manager, $pool);

        self::assertSame($binaryResponse, $controller->getMediumBinaryAction(1, 'format', new Request()));
    }

    public function testDeleteMediumAction(): void
    {
        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects(self::once())->method('delete');
        $manager->expects(self::once())->method('find')->willReturn($this->createMock(MediaInterface::class));

        $controller = $this->createMediaController($manager);

        $expected = ['deleted' => true];

        self::assertSame($expected, $controller->deleteMediumAction(1)->getData());
    }

    public function testPutMediumAction(): void
    {
        $medium = $this->createMock(MediaInterface::class);
        $medium->method('getProviderName')->willReturn('provider');

        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects(self::once())->method('find')->willReturn($medium);

        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects(self::once())->method('getName');

        $pool = new Pool('default');
        $pool->addProvider('provider', $provider);

        $form = $this->createMock(Form::class);
        $form->expects(self::once())->method('handleRequest');
        $form->expects(self::once())->method('isValid')->willReturn(true);
        $form->expects(self::once())->method('getData')->willReturn($medium);

        $factory = $this->createMock(FormFactoryInterface::class);
        $factory->expects(self::once())->method('createNamed')->willReturn($form);

        $controller = $this->createMediaController($manager, $pool, $factory);

        self::assertInstanceOf(View::class, $controller->putMediumAction(1, new Request()));
    }

    public function testPutMediumInvalidFormAction(): void
    {
        $medium = $this->createMock(MediaInterface::class);
        $medium->method('getProviderName')->willReturn('provider');

        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects(self::once())->method('find')->willReturn($medium);

        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects(self::once())->method('getName');

        $pool = new Pool('default');
        $pool->addProvider('provider', $provider);

        $form = $this->createMock(Form::class);
        $form->expects(self::once())->method('handleRequest');
        $form->expects(self::once())->method('isValid')->willReturn(false);

        $factory = $this->createMock(FormFactoryInterface::class);
        $factory->expects(self::once())->method('createNamed')->willReturn($form);

        $controller = $this->createMediaController($manager, $pool, $factory);

        self::assertInstanceOf(Form::class, $controller->putMediumAction(1, new Request()));
    }

    public function testPostProviderMediumAction(): void
    {
        $medium = $this->createMock(MediaInterface::class);
        $medium->expects(self::once())->method('setProviderName');

        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects(self::once())->method('create')->willReturn($medium);

        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects(self::once())->method('getName');

        $pool = new Pool('default');
        $pool->addProvider('providerName', $provider);

        $form = $this->createMock(Form::class);
        $form->expects(self::once())->method('handleRequest');
        $form->expects(self::once())->method('isValid')->willReturn(true);
        $form->expects(self::once())->method('getData')->willReturn($medium);

        $factory = $this->createMock(FormFactoryInterface::class);
        $factory->expects(self::once())->method('createNamed')->willReturn($form);

        $controller = $this->createMediaController($manager, $pool, $factory);

        self::assertInstanceOf(View::class, $controller->postProviderMediumAction('providerName', new Request()));
    }

    public function testPostProviderActionNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $medium = $this->createMock(MediaInterface::class);
        $medium->expects(self::once())->method('setProviderName');

        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects(self::once())->method('create')->willReturn($medium);

        $pool = new Pool('default');

        $controller = $this->createMediaController($manager, $pool);
        $controller->postProviderMediumAction('non existing provider', new Request());
    }

    public function testPutMediumBinaryContentAction(): void
    {
        $media = $this->createMock(MediaInterface::class);
        $media->expects(self::once())->method('setBinaryContent');

        $manager = $this->createMock(MediaManagerInterface::class);
        $manager->expects(self::once())->method('find')->willReturn($media);

        $pool = new Pool('default');

        $controller = $this->createMediaController($manager, $pool);

        self::assertSame($media, $controller->putMediumBinaryContentAction(1, new Request()));
    }

    private function createMediaController(
        ?MediaManagerInterface $manager = null,
        ?Pool $pool = null,
        ?FormFactoryInterface $factory = null
    ): MediaController {
        if (null === $manager) {
            $manager = $this->createMock(MediaManagerInterface::class);
        }
        if (null === $pool) {
            $pool = new Pool('default');
        }
        if (null === $factory) {
            $factory = $this->createMock(FormFactoryInterface::class);
        }

        return new MediaController($manager, $pool, $factory);
    }
}
