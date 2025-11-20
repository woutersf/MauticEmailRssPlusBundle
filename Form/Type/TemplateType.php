<?php

declare(strict_types=1);

namespace MauticPlugin\MauticEmailRssPlusBundle\Form\Type;

use Mautic\CoreBundle\Form\Type\FormButtonsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use MauticPlugin\MauticEmailRssPlusBundle\Entity\RssPlusTemplate;

class TemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'label' => 'Template Name',
            'label_attr' => ['class' => 'control-label required'],
            'attr' => [
                'class' => 'form-control',
                'placeholder' => 'Enter template name',
            ],
            'constraints' => [
                new NotBlank([
                    'message' => 'Template name is required',
                ]),
            ],
        ]);

        $defaultContent = '<mj-section background-color="#ffffff" padding-top="25px" padding-bottom="0">
      <mj-column width="100%">
        <mj-image src="{media}" alt="{title}" padding-top="0" padding-bottom="20px"></mj-image>
        <mj-text color="#000000" font-family="Ubuntu, Helvetica, Arial, sans-serif" font-size="20px" line-height="1.5" font-weight="500" padding-bottom="0px">
          <p>{title}</p>
        </mj-text>
        <mj-text color="#000000" font-family="Ubuntu, Helvetica, Arial, sans-serif" font-size="16px" line-height="1.5" font-weight="300" align="justify">
          <p>{description}</p>
        </mj-text>
        <mj-button background-color="#486AE2" color="#FFFFFF" href="{link}" font-family="Ubuntu, Helvetica, Arial, sans-serif" padding-top="20px" padding-bottom="40px">READ MORE</mj-button>
        <mj-text color="#666666" font-family="Ubuntu, Helvetica, Arial, sans-serif" font-size="12px">
          <p>{category} - {pubDate}</p>
        </mj-text>
      </mj-column>
    </mj-section>';

        $builder->add('content', TextareaType::class, [
            'label' => 'HTML Content',
            'label_attr' => ['class' => 'control-label'],
            'attr' => [
                'class' => 'form-control',
                'rows' => 20,
                'placeholder' => $defaultContent,
            ],
            'required' => false,
            'help' => 'You can use tokens in the template to insert RSS field values. Available tokens: {title}, {link}, {description}, {category}, {pubDate}, {media}. Each selected RSS item will be rendered using this template with tokens replaced by actual values.',
        ]);

        $builder->add('buttons', FormButtonsType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RssPlusTemplate::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'rssplus_template';
    }
}
