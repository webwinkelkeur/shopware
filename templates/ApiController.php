<?php

namespace {SYSTEM_NAME}\Shopware\Storefront\Controller;

use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class {SYSTEM_NAME}ApiController extends StorefrontController {
    /**
     * @Route("/{SYSTEM_KEY}", name="frontend.{SYSTEM_KEY}.isInstalled", methods={"GET"})
     */
    public
    function isInstalled(): JsonResponse {
        return new JsonResponse(['isInstalled' => true,]);
    }

    /**
     * @Route("/api/_action/dashboard-api-test/verify", defaults={"_routeScope"={"administration"}})
     */
    public
    function check(RequestDataBag $dataBag): JsonResponse {
        $webshopId = $dataBag->get('{SYSTEM_NAME}.config.webshopId');
        $apiKey = $dataBag->get('{SYSTEM_NAME}.config.apiKey');
        $base_url = 'https://dashboard.trustprofile.com/api/1.0/webshop.json';
        $params = http_build_query([
            'id' => $webshopId,
            'code' => $apiKey,
        ]);

        $url = sprintf('%s?%s',$base_url, $params);
        $result = @file_get_contents($url);

        if (!empty($result)) {
            $result = json_decode($result, true);
        }

        $status = $result['status'] ?? null;

        return new JsonResponse(['success' => $status === 'success']);
    }
}