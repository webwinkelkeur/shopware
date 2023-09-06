<?php

namespace {SYSTEM_NAME}\Shopware\Storefront\Controller;

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
    public function isInstalled(): JsonResponse {
        return new JsonResponse(['isInstalled' => true,]);
    }
}