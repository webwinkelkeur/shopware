<?php

namespace TrustProfile\Shopware\Storefront\Controller;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"administration"}})
 */
class ApiTestController
{
    /**
     * @Route(path="/api/v{version}/_action/dashboard-api-test/verify")
     */
    public function check(RequestDataBag $dataBag): JsonResponse
    {
        $username = $dataBag->get('TrustProfile.config.username');
        $password = $dataBag->get('TrustProfile.config.password');

        $success = false;

        if ($username === $password) {
            $success = true;
        }

        return new JsonResponse(['success' => $success]);
    }
}