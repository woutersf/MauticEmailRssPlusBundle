<?php

declare(strict_types=1);

namespace MauticPlugin\MauticEmailRssPlusBundle\Controller;

use Mautic\CoreBundle\Controller\FormController;
use Mautic\CoreBundle\Factory\PageHelperFactoryInterface;
use MauticPlugin\MauticEmailRssPlusBundle\Entity\RssPlusTemplate;
use MauticPlugin\MauticEmailRssPlusBundle\Model\TemplateModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TemplateController extends FormController
{
    public function indexAction(Request $request, PageHelperFactoryInterface $pageHelperFactory, int $page = 1): Response
    {
        $this->setListFilters();

        $pageHelper = $pageHelperFactory->make('mautic.rssplus.template', $page);

        $limit      = $pageHelper->getLimit();
        $start      = $pageHelper->getStart();
        $orderBy    = $request->getSession()->get('mautic.rssplus.template.orderby', 't.name');
        $orderByDir = $request->getSession()->get('mautic.rssplus.template.orderbydir', 'ASC');
        $filter     = $request->get('search', $request->getSession()->get('mautic.rssplus.template.filter', ''));
        $tmpl       = $request->isXmlHttpRequest() ? $request->get('tmpl', 'index') : 'index';

        /** @var TemplateModel $model */
        $model = $this->getModel('rssplus.template');
        $items = $model->getEntities([
            'start'      => $start,
            'limit'      => $limit,
            'filter'     => $filter,
            'orderBy'    => $orderBy,
            'orderByDir' => $orderByDir,
        ]);

        $request->getSession()->set('mautic.rssplus.template.filter', $filter);

        $count = count($items);
        if ($count && $count < ($start + 1)) {
            $lastPage  = $pageHelper->countPage($count);
            $returnUrl = $this->generateUrl('mautic_rssplus_template_index', ['page' => $lastPage]);
            $pageHelper->rememberPage($lastPage);

            return $this->postActionRedirect([
                'returnUrl'      => $returnUrl,
                'viewParameters' => [
                    'page' => $lastPage,
                    'tmpl' => $tmpl,
                ],
                'contentTemplate' => 'MauticPlugin\MauticEmailRssPlusBundle\Controller\TemplateController::indexAction',
                'passthroughVars' => [
                    'activeLink'    => '#mautic_rssplus_template_index',
                    'mauticContent' => 'rssplus_template',
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
            'contentTemplate' => '@MauticEmailRssPlus/Template/list.html.twig',
            'passthroughVars' => [
                'route'         => $this->generateUrl('mautic_rssplus_template_index', ['page' => $page]),
                'mauticContent' => 'rssplus_template',
            ],
        ]);
    }

    public function newAction(Request $request): Response
    {
        $entity = new RssPlusTemplate();
        /** @var TemplateModel $model */
        $model = $this->getModel('rssplus.template');

        $returnUrl = $this->generateUrl('mautic_rssplus_template_index');
        $page      = $request->getSession()->get('mautic.rssplus.template.page', 1);
        $action    = $this->generateUrl('mautic_rssplus_template_action', ['objectAction' => 'new']);

        $form = $model->createForm($entity, $this->formFactory, $action);

        if ('POST' === $request->getMethod()) {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $model->saveEntity($entity);

                    $this->addFlashMessage('mautic.core.notice.created', [
                        '%name%'      => $entity->getName(),
                        '%menu_link%' => 'mautic_rssplus_template_index',
                        '%url%'       => $this->generateUrl('mautic_rssplus_template_action', [
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
                    'contentTemplate' => 'MauticPlugin\MauticEmailRssPlusBundle\Controller\TemplateController::indexAction',
                    'passthroughVars' => [
                        'activeLink'    => '#mautic_rssplus_template_index',
                        'mauticContent' => 'rssplus_template',
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
            'contentTemplate' => '@MauticEmailRssPlus/Template/form.html.twig',
            'passthroughVars' => [
                'activeLink'    => '#mautic_rssplus_template_new',
                'route'         => $action,
                'mauticContent' => 'rssplus_template',
            ],
        ]);
    }

    public function editAction(Request $request, int $objectId, bool $ignorePost = false): Response
    {
        /** @var TemplateModel $model */
        $model  = $this->getModel('rssplus.template');
        $entity = $model->getEntity($objectId);

        $page = $request->getSession()->get('mautic.rssplus.template.page', 1);
        $returnUrl = $this->generateUrl('mautic_rssplus_template_index', ['page' => $page]);

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticPlugin\MauticEmailRssPlusBundle\Controller\TemplateController::indexAction',
            'passthroughVars' => [
                'activeLink'    => '#mautic_rssplus_template_index',
                'mauticContent' => 'rssplus_template',
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
            return $this->isLocked($postActionVars, $entity, 'rssplus.template');
        }

        $action = $this->generateUrl('mautic_rssplus_template_action', ['objectAction' => 'edit', 'objectId' => $objectId]);
        $form   = $model->createForm($entity, $this->formFactory, $action);

        if (!$ignorePost && 'POST' === $request->getMethod()) {
            $valid = false;

            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $model->saveEntity($entity, $this->getFormButton($form, ['buttons', 'save'])->isClicked());

                    $this->addFlashMessage('mautic.core.notice.updated', [
                        '%name%'      => $entity->getName(),
                        '%menu_link%' => 'mautic_rssplus_template_index',
                        '%url%'       => $this->generateUrl('mautic_rssplus_template_action', [
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
            'contentTemplate' => '@MauticEmailRssPlus/Template/form.html.twig',
            'passthroughVars' => [
                'activeLink'    => '#mautic_rssplus_template_index',
                'route'         => $action,
                'mauticContent' => 'rssplus_template',
            ],
        ]);
    }

    public function deleteAction(Request $request, int $objectId): Response
    {
        $page      = $request->getSession()->get('mautic.rssplus.template.page', 1);
        $returnUrl = $this->generateUrl('mautic_rssplus_template_index', ['page' => $page]);
        $success   = 0;
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticPlugin\MauticEmailRssPlusBundle\Controller\TemplateController::indexAction',
            'passthroughVars' => [
                'activeLink'    => '#mautic_rssplus_template_index',
                'success'       => $success,
                'mauticContent' => 'rssplus_template',
            ],
        ];

        if (Request::METHOD_POST === $request->getMethod()) {
            /** @var TemplateModel $model */
            $model  = $this->getModel('rssplus.template');
            $entity = $model->getEntity($objectId);

            if (null === $entity) {
                $flashes[] = [
                    'type'    => 'error',
                    'msg'     => 'mautic.core.error.notfound',
                    'msgVars' => ['%id%' => $objectId],
                ];
            } elseif ($model->isLocked($entity)) {
                return $this->isLocked($postActionVars, $entity, 'rssplus.template');
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
