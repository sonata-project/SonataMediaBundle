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

use Sonata\ClassificationBundle\Model\ContextInterface;
use Sonata\ClassificationBundle\Model\ContextManagerInterface;
use Sonata\MediaBundle\Model\CategoryManagerInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class FixMediaContextCommand extends Command
{
    /**
     * @var Pool
     */
    private $mediaPool;

    /**
     * @var ContextManagerInterface|null
     */
    private $contextManager;

    /**
     * NEXT_MAJOR: Remove the argument 3 and use `?ContextManagerInterface $contextManager = null` in argument 2.
     */
    public function __construct(Pool $mediaPool, ?object $contextManagerOrCategoryManager = null, ?ContextManagerInterface $contextManager = null)
    {
        if (3 === \func_num_args()) {
            @trigger_error(sprintf(
                'The argument 3 in "%s()" is deprecated since sonata-project/media-bundle 3.x and will be removed in version 4.0.'
                .' Pass this value as argument 2 instead.',
                __METHOD__
            ), \E_USER_DEPRECATED);
        }

        if (null !== $contextManagerOrCategoryManager) {
            if (!$contextManagerOrCategoryManager instanceof ContextManagerInterface &&
                !$contextManagerOrCategoryManager instanceof CategoryManagerInterface
            ) {
                throw new \TypeError(sprintf(
                    'Argument 2 passed to "%s()" must be null or an instance of "%s" or "%s", instance of "%s" given.',
                    __METHOD__,
                    ContextManagerInterface::class,
                    CategoryManagerInterface::class,
                    \get_class($contextManagerOrCategoryManager)
                ));
            }

            if ($contextManagerOrCategoryManager instanceof ContextManagerInterface) {
                $contextManager = $contextManagerOrCategoryManager;
            } else {
                @trigger_error(sprintf(
                    'Passing other type than null or "%s" as argument 2 to "%s()" is deprecated since sonata-project/media-bundle 3.x'
                    .' and will be not allowed in version 4.0.',
                    ContextManagerInterface::class,
                    __METHOD__
                ), \E_USER_DEPRECATED);
            }
        }
        // NEXT_MAJOR: Remove the previous blocks.

        parent::__construct();

        $this->mediaPool = $mediaPool;
        $this->contextManager = $contextManager;
    }

    public function configure()
    {
        $this->setName('sonata:media:fix-media-context');
        $this->setDescription('Generate the default category for each media context');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (null === $this->contextManager) {
            throw new \LogicException(
                'This command could not be executed since some of its dependencies is missing.'
                .' Is the service "sonata.classification.manager.context" available?'
            );
        }

        foreach ($this->mediaPool->getContexts() as $context => $contextAttrs) {
            /** @var ContextInterface $defaultContext */
            $defaultContext = $this->contextManager->findOneBy([
                'id' => $context,
            ]);

            if (!$defaultContext) {
                $output->writeln(sprintf(" > default context for '%s' is missing, creating one", $context));
                $defaultContext = $this->contextManager->create();
                $defaultContext->setId($context);
                $defaultContext->setName(ucfirst($context));
                $defaultContext->setEnabled(true);

                $this->contextManager->save($defaultContext);
            }
        }

        $output->writeln('Done!');

        return 0;
    }
}
