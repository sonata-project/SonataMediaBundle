<?php

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
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixMediaContextCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('sonata:media:fix-media-context');
        $this->setDescription('Generate the default category for each media context');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $pool = $this->getContainer()->get('sonata.media.pool');
        $contextManager = $this->getContainer()->get('sonata.classification.manager.context');
        $cateoryManager = $this->getContainer()->get('sonata.classification.manager.category');

        foreach ($pool->getContexts() as $context => $contextAttrs) {
            /** @var ContextInterface $defaultContext */
            $defaultContext = $contextManager->findOneBy(array(
                'id' => $context,
            ));

            if (!$defaultContext) {
                $output->writeln(sprintf(" > default context for '%s' is missing, creating one", $context));
                $defaultContext = $contextManager->create();
                $defaultContext->setId($context);
                $defaultContext->setName(ucfirst($context));
                $defaultContext->setEnabled(true);

                $contextManager->save($defaultContext);
            }

            $defaultCategory = $cateoryManager->getRootCategory($defaultContext);

            if (!$defaultCategory) {
                $output->writeln(sprintf(" > default category for '%s' is missing, creating one", $context));
                $defaultCategory = $cateoryManager->create();
                $defaultCategory->setContext($defaultContext);
                $defaultCategory->setName(ucfirst($context));
                $defaultCategory->setEnabled(true);
                $defaultCategory->setPosition(0);

                $cateoryManager->save($defaultCategory);
            }
        }

        $output->writeln('Done!');
    }
}
