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
     * @var CategoryManagerInterface|null
     */
    private $categoryManager;

    /**
     * @var ContextManagerInterface|null
     */
    private $contextManager;

    public function __construct(Pool $mediaPool, ?CategoryManagerInterface $categoryManager = null, ?ContextManagerInterface $contextManager = null)
    {
        parent::__construct();

        $this->mediaPool = $mediaPool;
        $this->categoryManager = $categoryManager;
        $this->contextManager = $contextManager;
    }

    public function configure()
    {
        $this->setName('sonata:media:fix-media-context');
        $this->setDescription('Generate the default category for each media context');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (null === $this->categoryManager || null === $this->contextManager) {
            throw new \LogicException(
                'This command could not be executed since some of its dependencies is missing.'
                .' Are the services "sonata.media.manager.category" and "sonata.classification.manager.context" available?'
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

            $defaultCategory = $this->categoryManager->getRootCategory($defaultContext);

            if (!$defaultCategory) {
                $output->writeln(sprintf(" > default category for '%s' is missing, creating one", $context));
                $defaultCategory = $this->categoryManager->create();
                $defaultCategory->setContext($defaultContext);
                $defaultCategory->setName(ucfirst($context));
                $defaultCategory->setEnabled(true);
                $defaultCategory->setPosition(0);

                $this->categoryManager->save($defaultCategory);
            }
        }

        $output->writeln('Done!');

        return 0;
    }
}
