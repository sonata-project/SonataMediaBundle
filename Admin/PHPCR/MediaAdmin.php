<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Admin\PHPCR;

use Sonata\MediaBundle\Admin\BaseMediaAdmin as Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class MediaAdmin extends Admin
{
    /**
     * Path to the root node of simple pages.
     *
     * @var string
     */
    protected $root;

    public function setRoot($root)
    {
        $this->root = $root;
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $query = $this->getModelManager()->createQuery($this->getClass(), '', $this->root);

        foreach ($this->extensions as $extension) {
            $extension->configureQuery($this, $query, $context);
        }

        return $query;
    }

    public function generateObjectUrl($name, $object, array $parameters = array(), $absolute = false)
    {
        $parameters['id'] = $this->getUrlsafeIdentifier($object);
        return $this->generateUrl($name, $parameters, $absolute);
    }

    public function getUrlsafeIdentifier($object)
    {
        return $this->modelManager->getUrlsafeIdentifier($object);
    }

    public function id($object)
    {
        return $this->getUrlsafeIdentifier($object);
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\DatagridMapper $datagridMapper
     * @return void
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
//        $datagridMapper
//            ->add('name')
//            ->add('providerReference')
//            ->add('enabled')
//            ->add('context')
//        ;
    }
}