<?php

namespace Valued\Shopware\Controller;

use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Valued\Shopware\Service\DashboardService;
use Valued\Shopware\Service\ProductReviewService;
use Shopware\Core\Framework\Context;


class DashboardController extends StorefrontController {
    /**
     * @var DashboardService
     */
    private DashboardService $dashboardService;

    /**
     * @var HttpClientInterface
     */
    private HttpClientInterface $httpClient;

    private ProductReviewService $productReviewService;

    public function __construct(
        DashboardService $dashboardService,
        HttpClientInterface $httpClient,
        ProductReviewService $productReviewService
    ) {
        $this->dashboardService = $dashboardService;
        $this->httpClient = $httpClient;
        $this->productReviewService = $productReviewService;
    }

    public function isInstalled(): JsonResponse {
        return new JsonResponse(['isInstalled' => true,]);
    }

    public function check(RequestDataBag $dataBag): JsonResponse {
        $webshopId = intval($dataBag->get(sprintf(
            '%s.config.webshopId',
            $this->dashboardService->getTechnicalName()),
        ));
        $apiKey = strval($dataBag->get(sprintf(
            '%s.config.apiKey',
            $this->dashboardService->getTechnicalName()),
        ));

        if (!$webshopId || !trim($apiKey)) {
            return new JsonResponse(['success' => false]);
        }

        $base_url = sprintf('https://%s/api/1.0/webshop.json', $this->dashboardService->getDashboardHost());
        $params = http_build_query([
            'id' => $webshopId,
            'code' => $apiKey,
        ]);
        $url = sprintf('%s?%s', $base_url, $params);
        try {
            $content = $this->httpClient->request(
                'GET',
                $url,
            )->toArray();
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false]);
        }

        $status = ($content['status'] ?? null) === 'success';
        return new JsonResponse(['success' => $status]);
    }

    public function syncProductReviews(Request $request, Context $context): JsonResponse {
        if (!$content = $request->getContent()) {
            return new JsonResponse('Empty request data', 400);
        }
        if (!$data = json_decode($content, true)) {
            return new JsonResponse('Invalid JSON data provided', 400);
        }

        try {
            $productReview = $this->productReviewService->sync($data, $context);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(),400);
        }

        return new JsonResponse(['review_id' => $productReview]);
    }
}