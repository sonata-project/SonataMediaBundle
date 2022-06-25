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

namespace Sonata\MediaBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\MediaBundle\Model\GalleryItemInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * @phpstan-extends AbstractAdmin<GalleryItemInterface>
 */
final class GalleryItemAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $linkParameters = [];

        if ($this->hasParentFieldDescription()) {
            /** @var array<string, mixed> $linkParameters */
            $linkParameters = $this->getParentFieldDescription()->getOption('link_parameters', []);
        }

        if ($this->hasRequest()) {
            $context = $this->getRequest()->query->get('context');

            if (null !== $context) {
                $linkParameters['context'] = $context;
            }
        }

        $form
            ->add('media', ModelListType::class, ['required' => false], [
                'link_parameters' => $linkParameters,
            ])
            ->add('enabled', null, ['required' => false])
            ->add('position', HiddenType::class);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('media')
            ->add('gallery')
            ->add('position')
            ->add('enabled');
    }
}
