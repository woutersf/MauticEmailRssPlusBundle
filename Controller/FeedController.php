<?php

declare(strict_types=1);

namespace MauticPlugin\MauticEmailRssPlusBundle\Controller;

use Mautic\CoreBundle\Controller\FormController;
use Mautic\CoreBundle\Factory\PageHelperFactoryInterface;
use MauticPlugin\MauticEmailRssPlusBundle\Entity\RssPlusFeed;
use MauticPlugin\MauticEmailRssPlusBundle\Model\FeedModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FeedController extends FormController
{
    public function indexAction(Request $request, PageHelperFactoryInterface $pageHelperFactory, int $page = 1): Response
    {
        $this->setListFilters();

        $pageHelper = $pageHelperFactory->make('mautic.rssplus.feed', $page);

        $limit      = $pageHelper->getLimit();
        $start      = $pageHelper->getStart();
        $orderBy    = $request->getSession()->get('mautic.rssplus.feed.orderby', 'f.name');
        $orderByDir = $request->getSession()->get('mautic.rssplus.feed.orderbydir', 'ASC');
        $filter     = $request->get('search', $request->getSession()->get('mautic.rssplus.feed.filter', ''));
        $tmpl       = $request->isXmlHttpRequest() ? $request->get('tmpl', 'index') : 'index';

        /** @var FeedModel $model */
        $model = $this->getModel('rssplus.feed');
        $items = $model->getEntities([
            'start'      => $start,
            'limit'      => $limit,
            'filter'     => $filter,
            'orderBy'    => $orderBy,
            'orderByDir' => $orderByDir,
        ]);

        $request->getSession()->set('mautic.rssplus.feed.filter', $filter);

        $count = count($items);
        if ($count && $count < ($start + 1)) {
            $lastPage  = $pageHelper->countPage($count);
            $returnUrl = $this->generateUrl('mautic_rssplus_feed_index', ['page' => $lastPage]);
            $pageHelper->rememberPage($lastPage);

            return $this->postActionRedirect([
                'returnUrl'      => $returnUrl,
                'viewParameters' => [
                    'page' => $lastPage,
                    'tmpl' => $tmpl,
                ],
                'contentTemplate' => 'MauticPlugin\MauticEmailRssPlusBundle\Controller\FeedController::indexAction',
                'passthroughVars' => [
                    'activeLink'    => '#mautic_rssplus_feed_index',
                    'mauticContent' => 'rssplus_feed',
                ],
            ]);
        }

        $pageHelper->rememberPage($page);

        return $this->delegateView([
            'viewParameters'  => [
                'items'       => $items,
                'searchValue' => $filter,
                'page'        => $page,
                'limit'       => $limit,
                'tmpl'        => $tmpl,
            ],
            'contentTemplate' => '@MauticEmailRssPlus/Feed/list.html.twig',
            'passthroughVars' => [
                'route'         => $this->generateUrl('mautic_rssplus_feed_index', ['page' => $page]),
                'mauticContent' => 'rssplus_feed',
            ],
        ]);
    }

    public function newAction(Request $request): Response
    {
        $entity = new RssPlusFeed();
        /** @var FeedModel $model */
        $model = $this->getModel('rssplus.feed');

        $returnUrl = $this->generateUrl('mautic_rssplus_feed_index');
        $page      = $request->getSession()->get('mautic.rssplus.feed.page', 1);
        $action    = $this->generateUrl('mautic_rssplus_feed_action', ['objectAction' => 'new']);

        $form = $model->createForm($entity, $this->formFactory, $action);

        if ('POST' === $request->getMethod()) {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $model->saveEntity($entity);

                    $this->addFlashMessage('mautic.core.notice.created', [
                        '%name%'      => $entity->getName(),
                        '%menu_link%' => 'mautic_rssplus_feed_index',
                        '%url%'       => $this->generateUrl('mautic_rssplus_feed_action', [
                            'objectAction' => 'edit',
                            'objectId'     => $entity->getId(),
                        ]),
                    ]);
                }
            }

            if ($cancelled || ($valid && $this->getFormButton($form, ['buttons', 'save'])->isClicked())) {
                return $this->postActionRedirect([
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $page],
                    'contentTemplate' => 'MauticPlugin\MauticEmailRssPlusBundle\Controller\FeedController::indexAction',
                    'passthroughVars' => [
                        'activeLink'    => '#mautic_rssplus_feed_index',
                        'mauticContent' => 'rssplus_feed',
                    ],
                ]);
            } elseif ($valid) {
                return $this->editAction($request, $entity->getId(), true);
            }
        }

        return $this->delegateView([
            'viewParameters' => [
                'form' => $form->createView(),
            ],
            'contentTemplate' => '@MauticEmailRssPlus/Feed/form.html.twig',
            'passthroughVars' => [
                'activeLink'    => '#mautic_rssplus_feed_new',
                'route'         => $action,
                'mauticContent' => 'rssplus_feed',
            ],
        ]);
    }

    public function editAction(Request $request, int $objectId, bool $ignorePost = false): Response
    {
        /** @var FeedModel $model */
        $model  = $this->getModel('rssplus.feed');
        $entity = $model->getEntity($objectId);

        $page = $request->getSession()->get('mautic.rssplus.feed.page', 1);
        $returnUrl = $this->generateUrl('mautic_rssplus_feed_index', ['page' => $page]);

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticPlugin\MauticEmailRssPlusBundle\Controller\FeedController::indexAction',
            'passthroughVars' => [
                'activeLink'    => '#mautic_rssplus_feed_index',
                'mauticContent' => 'rssplus_feed',
            ],
        ];

        if (null === $entity) {
            return $this->postActionRedirect(
                array_merge($postActionVars, [
                    'flashes' => [
                        [
                            'type'    => 'error',
                            'msg'     => 'mautic.core.error.notfound',
                            'msgVars' => ['%id%' => $objectId],
                        ],
                    ],
                ])
            );
        } elseif ($model->isLocked($entity)) {
            return $this->isLocked($postActionVars, $entity, 'rssplus.feed');
        }

        $action = $this->generateUrl('mautic_rssplus_feed_action', ['objectAction' => 'edit', 'objectId' => $objectId]);
        $form   = $model->createForm($entity, $this->formFactory, $action);

        if (!$ignorePost && 'POST' === $request->getMethod()) {
            $valid = false;

            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $model->saveEntity($entity, $this->getFormButton($form, ['buttons', 'save'])->isClicked());

                    $this->addFlashMessage('mautic.core.notice.updated', [
                        '%name%'      => $entity->getName(),
                        '%menu_link%' => 'mautic_rssplus_feed_index',
                        '%url%'       => $this->generateUrl('mautic_rssplus_feed_action', [
                            'objectAction' => 'edit',
                            'objectId'     => $entity->getId(),
                        ]),
                    ]);
                }
            } else {
                $model->unlockEntity($entity);
            }

            if ($cancelled || ($valid && $this->getFormButton($form, ['buttons', 'save'])->isClicked())) {
                return $this->postActionRedirect($postActionVars);
            }
        } else {
            $model->lockEntity($entity);
        }

        return $this->delegateView([
            'viewParameters' => [
                'form' => $form->createView(),
            ],
            'contentTemplate' => '@MauticEmailRssPlus/Feed/form.html.twig',
            'passthroughVars' => [
                'activeLink'    => '#mautic_rssplus_feed_index',
                'route'         => $action,
                'mauticContent' => 'rssplus_feed',
            ],
        ]);
    }

    public function deleteAction(Request $request, int $objectId): Response
    {
        $page      = $request->getSession()->get('mautic.rssplus.feed.page', 1);
        $returnUrl = $this->generateUrl('mautic_rssplus_feed_index', ['page' => $page]);
        $success   = 0;
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticPlugin\MauticEmailRssPlusBundle\Controller\FeedController::indexAction',
            'passthroughVars' => [
                'activeLink'    => '#mautic_rssplus_feed_index',
                'success'       => $success,
                'mauticContent' => 'rssplus_feed',
            ],
        ];

        if (Request::METHOD_POST === $request->getMethod()) {
            /** @var FeedModel $model */
            $model  = $this->getModel('rssplus.feed');
            $entity = $model->getEntity($objectId);

            if (null === $entity) {
                $flashes[] = [
                    'type'    => 'error',
                    'msg'     => 'mautic.core.error.notfound',
                    'msgVars' => ['%id%' => $objectId],
                ];
            } elseif ($model->isLocked($entity)) {
                return $this->isLocked($postActionVars, $entity, 'rssplus.feed');
            } else {
                $model->deleteEntity($entity);
                $name      = $entity->getName();
                $flashes[] = [
                    'type'    => 'notice',
                    'msg'     => 'mautic.core.notice.deleted',
                    'msgVars' => [
                        '%name%' => $name,
                        '%id%'   => $objectId,
                    ],
                ];
            }
        }

        return $this->postActionRedirect(
            array_merge($postActionVars, [
                'flashes' => $flashes,
            ])
        );
    }

    public function executeAction(Request $request, $objectAction, $objectId = 0, $objectSubId = 0, $objectModel = ''): Response
    {
        return match ($objectAction) {
            'new' => $this->newAction($request),
            'edit' => $this->editAction($request, (int) $objectId),
            'delete' => $this->deleteAction($request, (int) $objectId),
            default => $this->accessDenied(),
        };
    }
}
