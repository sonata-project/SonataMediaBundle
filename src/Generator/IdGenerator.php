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

    public function __construct(int $firstLevel = 100000, int $secondLevel = 1000)
    {
        $this->firstLevel = $firstLevel;
        $this->secondLevel = $secondLevel;
    }

    public function generatePath(MediaInterface $media): string
    {
        $id = $media->getId();

        if (!is_numeric($id)) {
            throw new \InvalidArgumentException(sprintf(
                'Unable to generate path for media without numeric id using %s.',
                self::class
            ));
        }

        $context = $media->getContext();

        if (null === $context) {
            throw new \InvalidArgumentException(sprintf(
                'Unable to generate path for media without context using %s.',
                self::class
            ));
        }

        $repFirstLevel = (int) ($id / $this->firstLevel);
        $repSecondLevel = (int) (($id - ($repFirstLevel * $this->firstLevel)) / $this->secondLevel);

        return sprintf('%s/%04s/%02s', $context, $repFirstLevel + 1, $repSecondLevel + 1);
    }
}
