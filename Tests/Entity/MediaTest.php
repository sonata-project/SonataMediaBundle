<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\MediaBundle\Tests\Entity;


class MediaTest extends \PHPUnit_Framework_TestCase
{
    public function testMetadata()
    {

        $media = new Media;

        $media->setProviderMetadata(array('thumbnail_url' => 'http://pasloin.com/thumb.png'));

        $this->assertEquals($media->getMetadataValue('thumbnail_url'), 'http://pasloin.com/thumb.png', '::getMetadataValue() return the good value');
        $this->assertEquals($media->getMetadataValue('thumbnail_url1', 'default'), 'default', '::getMetadataValue() return the default');
        $this->assertEquals($media->getMetadataValue('thumbnail_url1'), null, '::getMetadataValue() return the null value');
    }

}