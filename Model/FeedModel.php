<?php

declare(strict_types=1);

namespace MauticPlugin\MauticEmailRssPlusBundle\Model;

use Mautic\CoreBundle\Model\FormModel;
use MauticPlugin\MauticEmailRssPlusBundle\Entity\RssPlusFeed;
use MauticPlugin\MauticEmailRssPlusBundle\Entity\RssPlusFeedRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class FeedModel extends FormModel
{
    public function getRepository(): RssPlusFeedRepository
    {
        return $this->em->getRepository(RssPlusFeed::class);
    }

    public function getPermissionBase(): string
    {
        return 'plugin:emailrssplus:feeds';
    }

    public function getEntity($id = null): ?RssPlusFeed
    {
        if (null === $id) {
            return new RssPlusFeed();
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
        if (!$entity instanceof RssPlusFeed) {
            throw new MethodNotAllowedHttpException(['RssPlusFeed']);
        }

        $isNew = $entity->getId() === null;

        if ($isNew) {
            $entity->setCreatedAt(new \DateTime());
            $currentUser = $this->userHelper->getUser();
            if ($currentUser) {
                $entity->setCreatedBy($currentUser->getId());
            }
        } else {
            $currentUser = $this->userHelper->getUser();
            if ($currentUser) {
                $entity->setUpdatedBy($currentUser->getId());
            }
        }

        parent::saveEntity($entity, $unlock);
    }

    public function createForm($entity, FormFactoryInterface $formFactory, $action = null, $options = []): \Symfony\Component\Form\FormInterface
    {
        if (!$entity instanceof RssPlusFeed) {
            throw new MethodNotAllowedHttpException(['RssPlusFeed']);
        }

        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create(\MauticPlugin\MauticEmailRssPlusBundle\Form\Type\FeedType::class, $entity, $options);
    }
}
