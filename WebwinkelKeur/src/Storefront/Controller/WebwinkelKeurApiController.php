<?php

namespace WebwinkelKeur\Shopware\Storefront\Controller;

use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Valued\Shopware\Controller\DashboardController;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class WebwinkelKeurApiController extends DashboardController {
    /**
     * @Route("/webwinkelkeur/is_instaled", name="frontend.webwinkelkeur.isInstalled", methods={"GET"}, defaults={"_routeScope"={"storefront"}})
     */
    public function isInstalled(): JsonResponse {
        return parent::isInstalled();
    }

    /**
     * @Route("/api/_action/webwinkelkeur-api-test/verify", defaults={"_routeScope"={"administration"}})
     */
    public function check(RequestDataBag $dataBag): JsonResponse {
        return parent::check($dataBag);
    }

    /**
     * @param Request $request
     * @Route("/webwinkelkeur/sync_product_reviews", name="frontend.webwinkelkeur.syncProductReviews", methods={"POST"}, defaults={"_routeScope"={"storefront"}})
     */
    public function syncProductReviews(Request $request, Context $context): JsonResponse {
        return parent::syncProductReviews($request, $context);
    }
}