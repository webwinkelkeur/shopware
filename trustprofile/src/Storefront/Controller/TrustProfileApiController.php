<?php

namespace TrustProfile\Shopware\Storefront\Controller;

use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Valued\Shopware\Controller\DashboardController;


class TrustProfileApiController extends DashboardController {
    /**
     * @Route("/trustprofile", name="frontend.trustprofile.isInstalled", methods={"GET"}, defaults={"_routeScope"={"storefront"}})
     */
    public
    function isInstalled(): JsonResponse {
        return parent::isInstalled();
    }

    /**
     * @Route("/api/_action/trustprofile-api-test/verify", defaults={"_routeScope"={"administration"}})
     */
    public
    function check(RequestDataBag $dataBag): JsonResponse {
        return parent::check($dataBag);
    }
}
