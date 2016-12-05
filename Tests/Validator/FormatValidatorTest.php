<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Validator;

use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Tests\Helpers\PHPUnit_Framework_TestCase;
use Sonata\MediaBundle\Validator\Constraints\ValidMediaFormat;
use Sonata\MediaBundle\Validator\FormatValidator;

class FormatValidatorTest extends PHPUnit_Framework_TestCase
{
    public function testValidate()
    {
        $pool = new Pool('defaultContext');
        $pool->addContext('test', array(), array('format1' => array()));

        $gallery = $this->createMock('Sonata\MediaBundle\Model\GalleryInterface');
        $gallery->expects($this->once())->method('getDefaultFormat')->will($this->returnValue('format1'));
        $gallery->expects($this->once())->method('getContext')->will($this->returnValue('test'));

        // Prefer the Symfony 2.5+ API if available
        if (class_exists('Symfony\Component\Validator\Context\ExecutionContext')) {
            $contextClass = 'Symfony\Component\Validator\Context\ExecutionContext';
        } else {
            $contextClass = 'Symfony\Component\Validator\ExecutionContext';
        }

        $context = $this->getMockBuilder($contextClass)
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->never())->method('addViolation');

        $validator = new FormatValidator($pool);
        $validator->initialize($context);

        $validator->validate($gallery, new ValidMediaFormat());
    }

    public function testValidateNotValidDefaultFormat()
    {
        $pool = new Pool('defaultContext');
        $pool->addContext('test', array(), array('format1' => array()));

        $gallery = $this->createMock('Sonata\MediaBundle\Model\GalleryInterface');
        $gallery->expects($this->once())->method('getDefaultFormat')->will($this->returnValue('non_existing_format'));
        $gallery->expects($this->once())->method('getContext')->will($this->returnValue('test'));

        // Prefer the Symfony 2.5+ API if available
        if (class_exists('Symfony\Component\Validator\Context\ExecutionContext')) {
            $contextClass = 'Symfony\Component\Validator\Context\ExecutionContext';
        } else {
            $contextClass = 'Symfony\Component\Validator\ExecutionContext';
        }

        $context = $this->getMockBuilder($contextClass)
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->once())->method('addViolation');

        $validator = new FormatValidator($pool);
        $validator->initialize($context);

        $validator->validate($gallery, new ValidMediaFormat());
    }

    public function testValidateOnlyReferenceIsAllowedIfNotFormats()
    {
        $pool = new Pool('defaultContext');
        $pool->addContext('test');

        $gallery = $this->createMock('Sonata\MediaBundle\Model\GalleryInterface');
        $gallery->expects($this->once())->method('getDefaultFormat')->will($this->returnValue('format_that_is_not_reference'));
        $gallery->expects($this->once())->method('getContext')->will($this->returnValue('test'));

        // Prefer the Symfony 2.5+ API if available
        if (class_exists('Symfony\Component\Validator\Context\ExecutionContext')) {
            $contextClass = 'Symfony\Component\Validator\Context\ExecutionContext';
        } else {
            $contextClass = 'Symfony\Component\Validator\ExecutionContext';
        }

        $context = $this->getMockBuilder($contextClass)
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->once())->method('addViolation');

        $validator = new FormatValidator($pool);
        $validator->initialize($context);

        $validator->validate($gallery, new ValidMediaFormat());
    }
}
