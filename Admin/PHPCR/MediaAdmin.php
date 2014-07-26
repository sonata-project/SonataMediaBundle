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
     * Path to the root node of media documents.
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
        $query = $this->getModelManager()->createQuery($this->getClass(), 'a', $this->root);

        foreach ($this->extensions as $extension) {
            $extension->configureQuery($this, $query, $context);
        }

        return $query;
    }

    public function id($object)
    {
        return $this->getUrlsafeIdentifier($object);
    }

    /**
     * @param  \Sonata\AdminBundle\Datagrid\DatagridMapper $datagridMapper
     * @return void
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        // TODO disabled filter due to no attached service for filter types: string, checkbox
//        $datagridMapper
//            ->add('name')
//            ->add('providerReference')
//            ->add('enabled')
//            ->add('context')
//        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        // Allow path in id parameter
        $collection->add('view', $this->getRouterIdParameter() . '/view', array(), array('id' => '.+', '_method' => 'GET'));
        $collection->add('show', $this->getRouterIdParameter() . '/show', array(
                '_controller' => sprintf('%s:%s', $this->baseControllerName, 'view')
            ),
            array('id' => '.+', '_method' => 'GET')
        );
    }
}
