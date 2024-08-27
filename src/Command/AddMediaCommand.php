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

use Sonata\MediaBundle\Model\MediaManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'sonata:media:add', description: 'Add a media into the database')]
final class AddMediaCommand extends Command
{
    /**
     * @internal This class should only be used through the console
     */
    public function __construct(private MediaManagerInterface $mediaManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('providerName', InputArgument::REQUIRED, 'The provider')
            ->addArgument('context', InputArgument::REQUIRED, 'The context')
            ->addArgument('binaryContent', InputArgument::REQUIRED, 'The content')
            ->addOption('description', null, InputOption::VALUE_OPTIONAL, 'The media description field')
            ->addOption('copyright', null, InputOption::VALUE_OPTIONAL, 'The media copyright field')
            ->addOption('author', null, InputOption::VALUE_OPTIONAL, 'The media author name field')
            ->addOption('enabled', null, InputOption::VALUE_OPTIONAL, 'The media enabled field', true);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $provider = $input->getArgument('providerName');
        $context = $input->getArgument('context');
        $binaryContent = $input->getArgument('binaryContent');

        $output->writeln(\sprintf('Add a new media - context: %s, provider: %s, content: %s', $context, $provider, $binaryContent));

        $media = $this->mediaManager->create();
        $media->setBinaryContent($binaryContent);
        $media->setContext($context);
        $media->setProviderName($provider);
        $media->setDescription($input->getOption('description'));
        $media->setCopyright($input->getOption('copyright'));
        $media->setAuthorName($input->getOption('author'));

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
