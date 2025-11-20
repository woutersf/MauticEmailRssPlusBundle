<?php

declare(strict_types=1);

namespace MauticPlugin\MauticEmailRssPlusBundle\Controller;

use Mautic\CoreBundle\Controller\CommonController;
use MauticPlugin\MauticEmailRssPlusBundle\Model\FeedModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends CommonController
{
    public function getFeedsAction(Request $request): JsonResponse
    {
        // Debug: Log that we reached this method
        error_log('RSS Plus: getFeedsAction called');

        /** @var FeedModel $model */
        $model = $this->getModel('rssplus.feed');

        // Get all feeds
        $feeds = $model->getEntities([
            'orderBy' => 'f.name',
            'orderByDir' => 'ASC',
        ]);

        $feedsData = [];
        foreach ($feeds as $feed) {
            $feedsData[] = [
                'id' => $feed->getId(),
                'name' => $feed->getName(),
                'machineName' => $feed->getMachineName(),
                'rssUrl' => $feed->getRssUrl(),
                'rssFields' => $feed->getRssFields(),
                'button' => $feed->getButton(),
                'token' => $feed->getToken(),
            ];
        }

        return new JsonResponse([
            'success' => true,
            'feeds' => $feedsData,
        ]);
    }
}
