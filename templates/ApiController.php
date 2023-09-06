<?php

namespace {SYSTEM_NAME}\Shopware\Storefront\Controller;

use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Valued\Shopware\Service\DashboardService;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class {SYSTEM_NAME}ApiController extends StorefrontController {
    /**
     * @var DashboardService
     */
    private DashboardService $dashboardService;
    public function __construct(DashboardService $dashboardService) {
        $this->dashboardService = $dashboardService;
    }

    /**
     * @Route("/{SYSTEM_KEY}", name="frontend.{SYSTEM_KEY}.isInstalled", methods={"GET"})
     */
    public
    function isInstalled(): JsonResponse {
        return new JsonResponse(['isInstalled' => true,]);
    }

    /**
     * @Route("/api/_action/{SYSTEM_KEY}-api-test/verify", defaults={"_routeScope"={"administration"}})
     */
    public
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

        $status = $result['status'] ?? null;

        return new JsonResponse(['success' => $status === 'success']);
    }
}