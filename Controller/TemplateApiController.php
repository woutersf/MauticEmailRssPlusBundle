<?php

declare(strict_types=1);

namespace MauticPlugin\MauticEmailRssPlusBundle\Controller;

use Mautic\CoreBundle\Controller\CommonController;
use MauticPlugin\MauticEmailRssPlusBundle\Model\TemplateModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class TemplateApiController extends CommonController
{
    public function getTemplatesAction(Request $request): JsonResponse
    {
        // Debug: Log that we reached this method
        error_log('RSS Plus: getTemplatesAction called');

        /** @var TemplateModel $model */
        $model = $this->getModel('rssplus.template');

        // Get all templates
        $templates = $model->getEntities([
            'orderBy' => 't.name',
            'orderByDir' => 'ASC',
        ]);

        $templatesData = [];
        foreach ($templates as $template) {
            $templatesData[] = [
                'id' => $template->getId(),
                'name' => $template->getName(),
                'content' => $template->getContent(),
            ];
        }

        return new JsonResponse([
            'success' => true,
            'templates' => $templatesData,
        ]);
    }
}
