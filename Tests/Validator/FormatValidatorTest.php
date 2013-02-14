<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Validator;

use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Validator\FormatValidator;
use Sonata\MediaBundle\Validator\Constraints\ValidMediaFormat;

class FormatThumbnailTest extends \PHPUnit_Framework_TestCase
{
    public function testValidate()
    {
        $pool = new Pool('defaultContext');
        $pool->addContext('test', array(), array('format1' => array()));

        $gallery = $this->getMock('Sonata\MediaBundle\Model\GalleryInterface');
        $gallery->expects($this->once())->method('getDefaultFormat')->will($this->returnValue('format1'));
        $gallery->expects($this->once())->method('getContext')->will($this->returnValue('test'));

        $context = $this->getMock('Symfony\Component\Validator\ExecutionContext', array(), array(), '', false);
        $context->expects($this->never())->method('addViolation');

        $validator = new FormatValidator($pool);
        $validator->initialize($context);

        $validator->validate($gallery, new ValidMediaFormat);
    }

    public function testValidateWithValidContext()
    {
        $pool = new Pool('defaultContext');
        $pool->addContext('test');

        $gallery = $this->getMock('Sonata\MediaBundle\Model\GalleryInterface');
        $gallery->expects($this->once())->method('getDefaultFormat')->will($this->returnValue('format1'));
        $gallery->expects($this->once())->method('getContext')->will($this->returnValue('test'));

        $context = $this->getMock('Symfony\Component\Validator\ExecutionContext', array(), array(), '', false);
        $context->expects($this->once())->method('addViolation');

        $validator = new FormatValidator($pool);
        $validator->initialize($context);

        $validator->validate($gallery, new ValidMediaFormat);
    }
}
