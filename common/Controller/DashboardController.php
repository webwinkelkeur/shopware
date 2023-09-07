<?php

namespace Valued\Shopware\Controller;

use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Valued\Shopware\Service\DashboardService;


class DashboardController extends StorefrontController {
    /**
     * @var DashboardService
     */
    private DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService) {
        $this->dashboardService = $dashboardService;
    }

    public
    function isInstalled(): JsonResponse {
        return new JsonResponse(['isInstalled' => true,]);
    }

    function check(RequestDataBag $dataBag): JsonResponse {
        $webshopId = $dataBag->get(sprintf('%s.config.webshopId', $this->dashboardService->getSystemName()));
        $apiKey = $dataBag->get(sprintf('%s.config.apiKey', $this->dashboardService->getSystemName()));

        $base_url = sprintf('https://%s/api/1.0/webshop.json', $this->dashboardService->getDashboardHost());
        $params = http_build_query([
            'id' => $webshopId,
            'code' => $apiKey,
        ]);
        $url = sprintf('%s?%s', $base_url, $params);

        $result = @file_get_contents($url);
        if (!empty($result)) {
            $result = json_decode($result, true);
        }

        $status = ($result['status'] ?? null) === 'success';
        return new JsonResponse(['success' => $status]);
    }
}