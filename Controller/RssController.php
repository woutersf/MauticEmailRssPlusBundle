<?php

declare(strict_types=1);

namespace MauticPlugin\MauticEmailRssPlusBundle\Controller;

use Mautic\CoreBundle\Controller\CommonController;
use MauticPlugin\MauticEmailRssPlusBundle\Model\FeedModel;
use MauticPlugin\MauticEmailRssPlusBundle\Model\TemplateModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class RssController extends CommonController
{
    public function fetchAction(Request $request, int $feedId): JsonResponse
    {
        /** @var FeedModel $feedModel */
        $feedModel = $this->getModel('rssplus.feed');
        $feed = $feedModel->getEntity($feedId);

        if (!$feed) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Feed not found',
            ], 404);
        }

        try {
            // Fetch RSS feed
            $rssUrl = $feed->getRssUrl();
            $rssContent = @file_get_contents($rssUrl);

            if ($rssContent === false) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Could not fetch RSS feed from URL',
                ]);
            }

            // Parse RSS
            $previousValue = libxml_use_internal_errors(true);
            $xml = simplexml_load_string($rssContent);
            libxml_use_internal_errors($previousValue);

            if ($xml === false) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Invalid RSS feed format',
                ]);
            }

            // Get fields to extract
            $fieldsToExtract = array_filter(array_map('trim', explode("\n", $feed->getRssFields() ?: '')));
            if (empty($fieldsToExtract)) {
                $fieldsToExtract = ['title', 'link', 'description', 'pubDate'];
            }

            // Extract items
            $items = [];
            $rssItems = $xml->channel->item ?? $xml->item ?? [];

            foreach ($rssItems as $item) {
                $extractedItem = [];

                foreach ($fieldsToExtract as $field) {
                    $value = '';

                    // Handle different field locations
                    if (isset($item->$field)) {
                        $value = (string) $item->$field;
                    } elseif ($field === 'media' && isset($item->enclosure['url'])) {
                        $value = (string) $item->enclosure['url'];
                    } elseif ($field === 'media' && isset($item->children('media', true)->content)) {
                        $value = (string) $item->children('media', true)->content->attributes()->url;
                    } elseif ($field === 'media' && isset($item->children('media', true)->thumbnail)) {
                        $value = (string) $item->children('media', true)->thumbnail->attributes()->url;
                    } elseif ($field === 'category' && isset($item->category)) {
                        $value = (string) $item->category;
                    }

                    $extractedItem[$field] = $value;
                }

                $items[] = $extractedItem;
            }

            // Get default template content
            $templateContent = $this->getDefaultTemplate();

            return new JsonResponse([
                'success' => true,
                'items' => $items,
                'template' => $templateContent,
                'feedName' => $feed->getName(),
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Error fetching RSS: ' . $e->getMessage(),
            ]);
        }
    }

    private function getDefaultTemplate(): string
    {
        return '<mj-section background-color="#ffffff" padding-top="25px" padding-bottom="0">
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
    }
}
