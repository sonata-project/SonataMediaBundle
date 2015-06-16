<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\PHPCR;

use Sonata\CoreBundle\Model\BasePHPCRManager;

class MediaManager extends BasePHPCRManager
{
    /**
     * {@inheritdoc}
     */
    public function save($entity, $andFlush = true)
    {
        // BC compatibility for $context parameter
        if ($andFlush && is_string($andFlush)) {
            @trigger_error(sprintf('Since version 2.2.7, passing context in argument 2 for %s() is deprecated and support for it will be removed in 3.0. Use %s::setContext() instead.', __METHOD__, $this->class), E_USER_DEPRECATED);
            $entity->setContext($andFlush);
        }

        // BC compatibility for $providerName parameter
        if (3 == func_num_args()) {
            @trigger_error(sprintf('Since version 2.2.7, passing provider name in argument 3 for %s() is deprecated and support for it will be removed in 3.0. Use %s::setProviderName() instead.', __METHOD__, $this->class), E_USER_DEPRECATED);
            $entity->setProviderName(func_get_arg(2));
        }

        if ($andFlush && is_bool($andFlush)) {
            parent::save($entity, $andFlush);
        } else {
            // BC compatibility with previous signature
            parent::save($entity, true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPager(array $criteria, $page, $limit = 10, array $sort = array())
    {
        throw new \RuntimeException('Not Implemented yet');
    }
}
