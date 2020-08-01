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

namespace Sonata\MediaBundle\Security;

use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class PublicDownloadStrategy implements DownloadStrategyInterface
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function isGranted(MediaInterface $media, Request $request)
    {
        return true;
    }

    public function getDescription()
    {
        return $this->translator->trans('description.public_download_strategy', [], 'SonataMediaBundle');
    }
}
