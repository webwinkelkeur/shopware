<?php

namespace Valued\Shopware\Controller;

use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Valued\Shopware\Service\DashboardService;


class DashboardController extends StorefrontController {
    /**
     * @var DashboardService
     */
    private DashboardService $dashboardService;

    /**
     * @var HttpClientInterface
     */
    private HttpClientInterface $httpClient;

    public function __construct(DashboardService $dashboardService, HttpClientInterface $httpClient) {
        $this->dashboardService = $dashboardService;
        $this->httpClient = $httpClient;
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
}