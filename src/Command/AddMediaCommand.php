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
use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class AddMediaCommand extends Command
{
    protected static $defaultName = 'sonata:media:add';
    protected static $defaultDescription = 'Add a media into the database';

    /**
     * @var ManagerInterface<MediaInterface>
     */
    private $mediaManager;

    /**
     * @param ManagerInterface<MediaInterface> $mediaManager
     */
    public function __construct(ManagerInterface $mediaManager)
    {
        parent::__construct();

        $this->mediaManager = $mediaManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(static::$defaultDescription)
            ->setDefinition([
                new InputArgument('providerName', InputArgument::REQUIRED, 'The provider'),
                new InputArgument('context', InputArgument::REQUIRED, 'The context'),
                new InputArgument('binaryContent', InputArgument::REQUIRED, 'The content'),

                new InputOption('description', null, InputOption::VALUE_OPTIONAL, 'The media description field', null),
                new InputOption('copyright', null, InputOption::VALUE_OPTIONAL, 'The media copyright field', null),
                new InputOption('author', null, InputOption::VALUE_OPTIONAL, 'The media author name field', null),
                new InputOption('enabled', null, InputOption::VALUE_OPTIONAL, 'The media enabled field', true),
        ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $provider = $input->getArgument('providerName');
        $context = $input->getArgument('context');
        $binaryContent = $input->getArgument('binaryContent');

        $output->writeln(sprintf('Add a new media - context: %s, provider: %s, content: %s', $context, $provider, $binaryContent));

        $media = $this->mediaManager->create();
        $media->setBinaryContent($binaryContent);
        $media->setContext($context);
        $media->setProviderName($provider);

        if ($input->getOption('description')) {
            $media->setDescription($input->getOption('description'));
        }

        if ($input->getOption('copyright')) {
            $media->setCopyright($input->getOption('copyright'));
        }

        if ($input->getOption('author')) {
            $media->setAuthorName($input->getOption('author'));
        }

        if (\in_array($input->getOption('enabled'), [1, true, 'true'], true)) {
            $media->setEnabled(true);
        } else {
            $media->setEnabled(false);
        }

        $this->mediaManager->save($media);

        $output->writeln('done!');

        return 0;
    }
}
