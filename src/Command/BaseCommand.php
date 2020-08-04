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

namespace Sonata\MediaBundle\Command;

use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Console\Command\Command;

/**
 * This command can be used to re-generate the thumbnails for all uploaded medias.
 *
 * Useful if you have existing media content and added new formats.
 *
 * NEXT_MAJOR: Remove this class.
 *
 * @deprecated since sonata-project/media-bundle 3.26, to be removed in 4.0.
 */
abstract class BaseCommand extends Command
{
    /**
     * @var ManagerInterface
     */
    private $mediaManager;

    /**
     * @var Pool
     */
    private $pool;

    public function __construct(ManagerInterface $mediaManager, Pool $pool)
    {
        parent::__construct();

        $this->mediaManager = $mediaManager;
        $this->pool = $pool;
    }

    public function getMediaManager(): ManagerInterface
    {
        return $this->mediaManager;
    }

    public function getMediaPool(): Pool
    {
        return $this->pool;
    }
}
