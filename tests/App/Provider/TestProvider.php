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

namespace Sonata\MediaBundle\Tests\App\Provider;

use Gaufrette\File;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\BaseProvider;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;

class TestProvider extends BaseProvider
{
    public ?string $prevReferenceImage = null;

    public function getHelperProperties(MediaInterface $media, string $format, array $options = []): array
    {
        return [];
    }

    public function postPersist(MediaInterface $media): void
    {
        // TODO: Implement postPersist() method.
    }

    public function buildEditForm(FormMapper $form): void
    {
        $form->add('foo');
    }

    public function buildCreateForm(FormMapper $form): void
    {
        $form->add('foo');
    }

    public function postUpdate(MediaInterface $media): void
    {
        // TODO: Implement postUpdate() method.
    }

    public function getAbsolutePath(MediaInterface $media): void
    {
        // TODO: Implement getAbsolutePath() method.
    }

    public function getReferenceImage(MediaInterface $media): string
    {
        // A copy of the code from \Sonata\MediaBundle\Provider\FileProvider::getReferenceImage()
        $providerReference = $media->getProviderReference();

        if (null === $providerReference) {
            throw new \InvalidArgumentException('Unable to generate reference image for media without provider reference.');
        }

        $this->prevReferenceImage = \sprintf('%s/%s', $this->generatePath($media), $providerReference);

        return $this->prevReferenceImage;
    }

    public function generatePrivateUrl(MediaInterface $media, string $format): string
    {
        return '';
    }

    public function generatePublicUrl(MediaInterface $media, string $format): string
    {
        return '';
    }

    public function getReferenceFile(MediaInterface $media): File
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function preUpdate(MediaInterface $media): void
    {
        // TODO: Implement preUpdate() method.
    }

    public function prePersist(MediaInterface $media): void
    {
        // TODO: Implement prePersist() method.
    }

    public function getDownloadResponse(MediaInterface $media, string $format, string $mode, array $headers = []): Response
    {
        return new Response();
    }

    public function buildMediaType(FormBuilderInterface $formBuilder): void
    {
        $formBuilder->add('foo');
    }

    public function updateMetadata(MediaInterface $media, bool $force = false): void
    {
        // TODO: Implement updateMetadata() method.
    }

    protected function doTransform(MediaInterface $media): void
    {
        // TODO: Implement doTransform() method.
    }
}
