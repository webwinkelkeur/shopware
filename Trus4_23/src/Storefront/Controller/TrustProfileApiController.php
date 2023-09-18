<?php

namespace TrustProfile\Shopware\Storefront\Controller;

use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\Framework\Context;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Valued\Shopware\Controller\DashboardController;
use Symfony\Component\HttpFoundation\Request;


class TrustProfileApiController extends DashboardController {
    /**
     * @Route("/trustprofile/is_instaled", name="frontend.trustprofile.isInstalled", methods={"GET"}, defaults={"_routeScope"={"storefront"}})
     */
    public function isInstalled(): JsonResponse {
        return parent::isInstalled();
    }

    /**
     * @Route("/api/_action/trustprofile-api-test/verify", defaults={"_routeScope"={"administration"}})
     */
    public function check(RequestDataBag $dataBag): JsonResponse {
        return parent::check($dataBag);
    }

    /**
     * @param Request $request
     * @Route("/trustprofile/sync_product_reviews", name="frontend.trustprofile.syncProductReviews", methods={"POST"}, defaults={"_routeScope"={"storefront"}})
     */
    public function syncProductReviews(Request $request, Context $context): JsonResponse {
        return parent::syncProductReviews($request, $context);
    }
}
