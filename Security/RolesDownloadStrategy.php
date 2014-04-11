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
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;

class RolesDownloadStrategy implements DownloadStrategyInterface
{
    protected $roles;

    protected $security;

    protected $translator;

    /**
     * @param \Symfony\Component\Translation\TranslatorInterface        $translator
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $security
     * @param array                                                     $roles
     */
    public function __construct(TranslatorInterface $translator, SecurityContextInterface $security, array $roles = array())
    {
        $this->roles      = $roles;
        $this->security   = $security;
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
        return $this->security->getToken() && $this->security->isGranted($this->roles);
    }

    /**
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->translator->trans('description.roles_download_strategy', array('%roles%' => '<code>'.implode('</code>, <code>', $this->roles).'</code>'), 'SonataMediaBundle');
    }
}
