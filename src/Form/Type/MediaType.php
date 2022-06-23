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

namespace Sonata\MediaBundle\Form\Type;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Sonata\MediaBundle\Form\DataTransformer\ProviderDataTransformer;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MediaType extends AbstractType implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private Pool $pool;

    /**
     * @phpstan-var class-string<\Sonata\MediaBundle\Model\MediaInterface>
     */
    private string $class;

    /**
     * @phpstan-param class-string<\Sonata\MediaBundle\Model\MediaInterface> $class
     */
    public function __construct(Pool $pool, string $class)
    {
        $this->pool = $pool;
        $this->class = $class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $dataTransformer = new ProviderDataTransformer($this->pool, $options['data_class'], [
            'provider' => $options['provider'],
            'context' => $options['context'],
            'empty_on_new' => $options['empty_on_new'],
            'new_on_update' => $options['new_on_update'],
        ]);
        $dataTransformer->setLogger($this->logger ?? new NullLogger());

        $builder->addModelTransformer($dataTransformer);

        $builder->addEventListener(FormEvents::SUBMIT, static function (FormEvent $event): void {
            if ($event->getForm()->has('unlink') && true === $event->getForm()->get('unlink')->getData()) {
                $event->setData(null);
            }
        });

        $this->pool->getProvider($options['provider'])->buildMediaType($builder);

        $builder->add('unlink', CheckboxType::class, [
            'label' => 'widget_label_unlink',
            'mapped' => false,
            'data' => false,
            'required' => false,
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['provider'] = $options['provider'];
        $view->vars['context'] = $options['context'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => $this->class,
                'empty_on_new' => true,
                'new_on_update' => true,
                'translation_domain' => 'SonataMediaBundle',
            ])
            ->setRequired(['provider', 'context'])
            ->setAllowedTypes('provider', 'string')
            ->setAllowedTypes('context', 'string')
            ->setAllowedValues('provider', $this->pool->getProviderList())
            ->setAllowedValues('context', array_keys($this->pool->getContexts()));
    }

    public function getParent(): ?string
    {
        return FormType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'sonata_media_type';
    }
}
