<?php

declare(strict_types=1);

namespace MauticPlugin\MauticEmailRssPlusBundle\Integration;

use Mautic\PluginBundle\Integration\AbstractIntegration;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;

class EmailRssPlusIntegration extends AbstractIntegration
{
    public function getName(): string
    {
        return 'EmailRssPlus';
    }

    public function getDisplayName(): string
    {
        return 'RSS Plus';
    }

    public function getAuthenticationType(): string
    {
        return 'none';
    }

    public function getIcon(): string
    {
        return 'plugins/MauticEmailRssPlusBundle/Assets/rss-icon.png';
    }

    public function getRequiredKeyFields(): array
    {
        return [];
    }

    /**
     * @param FormBuilder|Form $builder
     * @param array            $data
     * @param string           $formArea
     */
    public function appendToForm(&$builder, $data, $formArea): void
    {
        if ('features' === $formArea) {
            $builder->add(
                'enabled',
                ChoiceType::class,
                [
                    'label' => 'mautic.plugin.emailrssplus.enabled',
                    'choices' => [
                        'mautic.core.form.yes' => true,
                        'mautic.core.form.no' => false,
                    ],
                    'data' => $data['enabled'] ?? false,
                    'attr' => [
                        'class' => 'form-control',
                        'tooltip' => 'mautic.plugin.emailrssplus.enabled.tooltip',
                    ],
                    'required' => false,
                    'expanded' => true,
                    'multiple' => false,
                    'label_attr' => ['class' => 'control-label'],
                ]
            );
        }
    }

    public function isConfigured(): bool
    {
        $featureSettings = $this->settings->getFeatureSettings();
        return !empty($featureSettings['enabled']);
    }
}
