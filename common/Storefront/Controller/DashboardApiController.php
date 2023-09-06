<?php

namespace Valued\Shopware\Storefront\Controller;

use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class DashboardApiController extends StorefrontController {
    /**
     * @Route("/{system}", name="frontend.{system}.isInstalled", methods={"GET"})
     */
    public function isInstalled(): JsonResponse {
        return new JsonResponse(['isInstalled' => true,]);
    }
}