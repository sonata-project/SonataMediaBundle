<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Security;

use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

class ForbiddenDownloadStrategy implements DownloadStrategyInterface
{
    protected $translator;

    /**
     * @param \Symfony\Component\Translation\TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface  $media
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return bool
     */
    public function isGranted(MediaInterface $media, Request $request)
    {
        return false;
    }

    /**
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->translator->trans('description.forbidden_download_strategy', array(), 'SonataMediaBundle');
    }
}
