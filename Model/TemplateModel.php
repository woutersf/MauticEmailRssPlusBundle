<?php

declare(strict_types=1);

namespace MauticPlugin\MauticEmailRssPlusBundle\Model;

use Mautic\CoreBundle\Model\FormModel;
use MauticPlugin\MauticEmailRssPlusBundle\Entity\RssPlusTemplate;
use MauticPlugin\MauticEmailRssPlusBundle\Entity\RssPlusTemplateRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class TemplateModel extends FormModel
{
    public function getRepository(): RssPlusTemplateRepository
    {
        return $this->em->getRepository(RssPlusTemplate::class);
    }

    public function getPermissionBase(): string
    {
        return 'plugin:emailrssplus:templates';
    }

    public function getEntity($id = null): ?RssPlusTemplate
    {
        if (null === $id) {
            return new RssPlusTemplate();
        }

        return parent::getEntity($id);
    }

    protected function dispatchEvent($action, &$entity, $isNew = false, ?\Symfony\Contracts\EventDispatcher\Event $event = null): ?\Symfony\Contracts\EventDispatcher\Event
    {
        // No events for now
        return null;
    }

    public function saveEntity($entity, $unlock = true): void
    {
        if (!$entity instanceof RssPlusTemplate) {
            throw new MethodNotAllowedHttpException(['RssPlusTemplate']);
        }

        $isNew = $entity->getId() === null;

        if ($isNew) {
            $entity->setCreatedAt(new \DateTime());
        } else {
            $entity->setUpdatedAt(new \DateTime());
            $currentUser = $this->userHelper->getUser();
            if ($currentUser) {
                $entity->setUpdatedBy($currentUser->getId());
            }
        }

        parent::saveEntity($entity, $unlock);
    }

    public function createForm($entity, FormFactoryInterface $formFactory, $action = null, $options = []): \Symfony\Component\Form\FormInterface
    {
        if (!$entity instanceof RssPlusTemplate) {
            throw new MethodNotAllowedHttpException(['RssPlusTemplate']);
        }

        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create(\MauticPlugin\MauticEmailRssPlusBundle\Form\Type\TemplateType::class, $entity, $options);
    }
}
