<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Security;

use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Component\HttpFoundation\Request;

interface DownloadStrategyInterface
{
    /**
     * @param MediaInterface $media
     * @param Request        $request
     *
     * @return bool
     */
    public function isGranted(MediaInterface $media, Request $request);

    /**
     * @return string
     */
    public function getDescription();
}
