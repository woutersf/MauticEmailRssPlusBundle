<?php

declare(strict_types=1);

namespace MauticPlugin\MauticEmailRssPlusBundle\Form\Type;

use Mautic\CoreBundle\Form\Type\FormButtonsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use MauticPlugin\MauticEmailRssPlusBundle\Entity\RssPlusFeed;

class FeedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'label' => 'Feed Name',
            'label_attr' => ['class' => 'control-label required'],
            'attr' => [
                'class' => 'form-control',
                'placeholder' => 'Enter feed name',
            ],
            'constraints' => [
                new NotBlank([
                    'message' => 'Feed name is required',
                ]),
            ],
        ]);

        $builder->add('machineName', TextType::class, [
            'label' => 'Machine Name',
            'label_attr' => ['class' => 'control-label required'],
            'attr' => [
                'class' => 'form-control',
                'placeholder' => 'e.g., BBC (3 uppercase letters)',
                'maxlength' => 255,
            ],
            'constraints' => [
                new NotBlank([
                    'message' => 'Machine name is required',
                ]),
            ],
        ]);

        $builder->add('rssUrl', UrlType::class, [
            'label' => 'RSS URL',
            'label_attr' => ['class' => 'control-label required'],
            'attr' => [
                'class' => 'form-control',
                'placeholder' => 'https://feeds.bbci.co.uk/news/rss.xml',
            ],
            'constraints' => [
                new NotBlank([
                    'message' => 'RSS URL is required',
                ]),
            ],
        ]);

        $builder->add('rssFields', TextareaType::class, [
            'label' => 'RSS Fields',
            'label_attr' => ['class' => 'control-label'],
            'attr' => [
                'class' => 'form-control',
                'rows' => 6,
                'placeholder' => 'title
link
description
category
pubDate
media',
            ],
            'required' => false,
        ]);

        $builder->add('button', ChoiceType::class, [
            'label' => 'Show button in email editor',
            'label_attr' => ['class' => 'control-label'],
            'choices' => [
                'Yes' => '1',
                'No' => '0',
            ],
            'attr' => [
                'class' => 'form-control',
            ],
        ]);

        $builder->add('token', ChoiceType::class, [
            'label' => 'Enable token in email editor',
            'label_attr' => ['class' => 'control-label'],
            'choices' => [
                'No token' => '0',
                '{token}' => '1',
            ],
            'attr' => [
                'class' => 'form-control',
            ],
        ]);

        $builder->add('buttons', FormButtonsType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RssPlusFeed::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'rssplus_feed';
    }
}
