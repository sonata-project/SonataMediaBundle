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

use Sonata\ClassificationBundle\Model\CategoryManagerInterface;
use Sonata\ClassificationBundle\Model\ContextManagerInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'sonata:media:fix-media-context', description: 'Generate the default category for each media context')]
final class FixMediaContextCommand extends Command
{
    /**
     * @internal This class should only be used through the console
     */
    public function __construct(
        private Pool $mediaPool,
        private ?CategoryManagerInterface $categoryManager = null,
        private ?ContextManagerInterface $contextManager = null
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (null === $this->categoryManager || null === $this->contextManager) {
            throw new \LogicException(
                'This command could not be executed since some of its dependencies is missing.'
                .' Are the services "sonata.media.manager.category" and "sonata.classification.manager.context" available?'
            );
        }

        foreach ($this->mediaPool->getContexts() as $context => $contextAttrs) {
            $defaultContext = $this->contextManager->find($context);

            if (null === $defaultContext) {
                $output->writeln(\sprintf(" > default context for '%s' is missing, creating one", $context));
                $defaultContext = $this->contextManager->create();
                $defaultContext->setId($context);
                $defaultContext->setName(ucfirst($context));
                $defaultContext->setEnabled(true);

                $this->contextManager->save($defaultContext);
            }

            $this->categoryManager->getRootCategoriesForContext($defaultContext);
        }

        $output->writeln('Done!');

        return 0;
    }
}
