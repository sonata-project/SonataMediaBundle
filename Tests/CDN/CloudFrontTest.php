<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\CDN;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
class CloudFrontTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group legacy
     */
    public function testLegacyCloudFront()
    {
        $client = $this->getMock(
            'Sonata\MediaBundle\Tests\CDN\CloudFrontClientSpy',
            array('createInvalidation'),
            array(),
            '',
            false
        );
        $client->expects($this->exactly(3))->method('createInvalidation')->will($this->returnValue(new CloudFrontResultSpy()));

        $cloudFront = $this->getMockBuilder('Sonata\MediaBundle\CDN\CloudFront')
                    ->setConstructorArgs(array('/foo', 'secret', 'key', 'xxxxxxxxxxxxxx'))
                    ->setMethods(null)
                    ->getMock();
        $cloudFront->setClient($client);

        $this->assertSame('/foo/bar.jpg', $cloudFront->getPath('bar.jpg', true));

        $path = '/mypath/file.jpg';

        $cloudFront->flushByString($path);
        $cloudFront->flush($path);
        $cloudFront->flushPaths(array($path));
    }

    /**
     * @group legacy
     */
    public function testLegacyException()
    {
        $this->setExpectedException('\RuntimeException', 'Unable to flush : ');
        $client = $this->getMock(
            'Sonata\MediaBundle\Tests\CDN\CloudFrontClientSpy',
            array('createInvalidation'),
            array(),
            '',
            false
        );
        $client->expects($this->exactly(1))->method('createInvalidation')->will($this->returnValue(new CloudFrontResultSpy(true)));
        $cloudFront = $this->getMockBuilder('Sonata\MediaBundle\CDN\CloudFront')
                    ->setConstructorArgs(array('/foo', 'secret', 'key', 'xxxxxxxxxxxxxx'))
                    ->setMethods(null)
                    ->getMock();
        $cloudFront->setClient($client);
        $cloudFront->flushPaths(array('boom'));
    }
}

class CloudFrontClientSpy
{
    public function createInvalidation()
    {
        return new CloudFrontResultSpy();
    }
}

class CloudFrontResultSpy
{
    protected $fail = false;

    public function __construct($fail = false)
    {
        $this->fail = $fail;
    }

    public function get($data)
    {
        if ('Status' !== $data || $this->fail) {
            return;
        }

        return 'InProgress';
    }
}
