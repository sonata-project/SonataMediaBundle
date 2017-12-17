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
use Symfony\Component\Form\FormBuilder;

/**
 * @author Hugo Briand <briand@ekino.com>
 */
class ApiMediaTypeTest extends AbstractTypeTest
{
    public function testBuildForm(): void
    {
        parent::testBuildForm();
        $provider = $this->createMock(MediaProviderInterface::class);

        $mediaPool = $this->createMock(Pool::class);
        $mediaPool->expects($this->once())->method('getProvider')->will($this->returnValue($provider));

        $type = new ApiMediaType($mediaPool, 'testclass');

        $builder = $this->createMock(FormBuilder::class);
        $builder->expects($this->once())->method('addModelTransformer');

        $type->buildForm($builder, ['provider_name' => 'sonata.media.provider.image']);
    }

    protected function getTestedInstance()
    {
        return new ApiMediaType($this->mediaPool, 'testclass');
    }
}
