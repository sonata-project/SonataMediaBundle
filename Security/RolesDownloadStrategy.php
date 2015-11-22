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
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;

class RolesDownloadStrategy implements DownloadStrategyInterface
{
    /**
     * @var string[]
     */
    protected $roles;

    /**
     * @var SecurityContextInterface
     */
    protected $security;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TranslatorInterface      $translator
     * @param SecurityContextInterface $security
     * @param string[]                 $roles
     */
    public function __construct(TranslatorInterface $translator, SecurityContextInterface $security, array $roles = array())
    {
        $this->roles      = $roles;
        $this->security   = $security;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(MediaInterface $media, Request $request)
    {
        return $this->security->getToken() && $this->security->isGranted($this->roles);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->translator->trans('description.roles_download_strategy', array('%roles%' => '<code>'.implode('</code>, <code>', $this->roles).'</code>'), 'SonataMediaBundle');
    }
}
