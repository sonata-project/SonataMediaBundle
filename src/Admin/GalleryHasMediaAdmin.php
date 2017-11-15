<?php

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

class GalleryHasMediaAdmin extends AbstractAdmin
{
    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $link_parameters = [];

        if ($this->hasParentFieldDescription()) {
            $link_parameters = $this->getParentFieldDescription()->getOption('link_parameters', []);
        }

        if ($this->hasRequest()) {
            $context = $this->getRequest()->get('context', null);

            if (null !== $context) {
                $link_parameters['context'] = $context;
            }
        }

        // NEXT_MAJOR: Keep FQCN when bumping Symfony requirement to 2.8+
        $modelListType = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
            ? 'Sonata\AdminBundle\Form\Type\ModelListType'
            : 'sonata_type_model_list';

        // NEXT_MAJOR: Keep FQCN when bumping Symfony requirement to 2.8+.
        $hiddenType = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
            ? 'Symfony\Component\Form\Extension\Core\Type\HiddenType'
            : 'hidden';

        $formMapper
            ->add('media', $modelListType, ['required' => false], [
                'link_parameters' => $link_parameters,
            ])
            ->add('enabled', null, ['required' => false])
            ->add('position', $hiddenType)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('media')
            ->add('gallery')
            ->add('position')
            ->add('enabled')
        ;
    }
}
