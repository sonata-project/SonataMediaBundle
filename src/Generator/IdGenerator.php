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

namespace Sonata\MediaBundle\Generator;

use Sonata\MediaBundle\Model\MediaInterface;

final class IdGenerator implements GeneratorInterface
{
    /**
     * @var int
     */
    private $firstLevel;

    /**
     * @var int
     */
    private $secondLevel;

    /**
     * @param int $firstLevel
     * @param int $secondLevel
     */
    public function __construct($firstLevel = 100000, $secondLevel = 1000)
    {
        $this->firstLevel = $firstLevel;
        $this->secondLevel = $secondLevel;
    }

    public function generatePath(MediaInterface $media)
    {
        $mediaId = (int) $media->getId();

        $rep_first_level = (int) ($mediaId / $this->firstLevel);
        $rep_second_level = (int) (($mediaId - ($rep_first_level * $this->firstLevel)) / $this->secondLevel);

        return sprintf('%s/%04s/%02s', $media->getContext(), $rep_first_level + 1, $rep_second_level + 1);
    }
}
