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

namespace Sonata\MediaBundle\Tests\Form\Type;

use Sonata\MediaBundle\Form\Type\ApiMediaType;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Tests\App\Entity\Media;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormTypeInterface;

/**
 * @author Hugo Briand <briand@ekino.com>
 */
class ApiMediaTypeTest extends AbstractTypeTest
{
    public function testBuildForm(): void
    {
        $provider = $this->createMock(MediaProviderInterface::class);

        $mediaPool = new Pool('default');
        $mediaPool->addProvider('sonata.media.provider.image', $provider);

        $type = new ApiMediaType($mediaPool, Media::class);

        $builder = $this->createMock(FormBuilder::class);
        $builder->expects(self::once())->method('addModelTransformer');

        $type->buildForm($builder, ['provider_name' => 'sonata.media.provider.image']);
    }

    protected function getTestedInstance(): FormTypeInterface
    {
        return new ApiMediaType($this->mediaPool, Media::class);
    }
}
