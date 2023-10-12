<?php

namespace {SYSTEM_NAME}\Shopware\Storefront\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Valued\Shopware\Service\DashboardService;
use Valued\Shopware\Service\ProductReviewService;
use Shopware\Core\Framework\Context;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class {SYSTEM_NAME}ApiController extends StorefrontController {
    /**
     * @var DashboardService
     */
    private DashboardService $dashboardService;

    private ProductReviewService $productReviewService;

    public function __construct(
        DashboardService $dashboardService,
        ProductReviewService $productReviewService
    ) {
        $this->dashboardService = $dashboardService;
        $this->productReviewService = $productReviewService;
    }

    /**
     * @Route("/{SYSTEM_KEY}/is_instaled", name="frontend.{SYSTEM_KEY}.isInstalled", methods={"GET"}, defaults={"_routeScope"={"storefront"}})
     */
    public function isInstalled(): JsonResponse {
        return new JsonResponse(['isInstalled' => true,]);
    }

    /**
     * @Route("/api/_action/{SYSTEM_KEY}-api-test/verify", defaults={"_routeScope"={"administration"}})
     */
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
            $content = $this->dashboardService->doRequest($url, 'GET');
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false]);
        }

        $status = ($content['status'] ?? null) === 'success';
        return new JsonResponse(['success' => $status]);
    }

    /**
     * @param Request $request
     * @Route("/{SYSTEM_KEY}/sync_product_reviews", name="frontend.{SYSTEM_KEY}.syncProductReviews", methods={"POST"}, defaults={"_routeScope"={"storefront"}})
     */
    public function syncProductReviews(Request $request, Context $context): JsonResponse {
        if (!$content = $request->getContent()) {
            return new JsonResponse('Empty request data', 400);
        }
        if (!$data = json_decode($content, true)) {
            return new JsonResponse('Invalid JSON data provided', 400);
        }

        if (!$this->hasCredentialFields($data) || $this->credentialsEmpty($data)) {
            throw new UnauthorizedHttpException('Missing API credentials params');
        }

        $this->isAuthorized($data, $context->getSource()->getSalesChannelId());

        try {
            $productReview = $this->productReviewService->sync($data, $context);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(),400);
        }

        return new JsonResponse(['review_id' => $productReview]);
    }
    private function hasCredentialFields(array $data): bool {
        return isset($data['webshop_id']) && isset($data['api_key']);
    }

    private function credentialsEmpty(array $data): bool {
        return !trim($data['webshop_id']) || !trim($data['api_key']);
    }

    private function isAuthorized(array $data, string $salesChannelId): void {
        $webshopId = $this->dashboardService->getConfigValue('webshopId', $salesChannelId);
        $apiKey = $this->dashboardService->getConfigValue('apiKey', $salesChannelId);
        if ($webshopId == $data['webshop_id'] && hash_equals($apiKey, $data['api_key'])) {
            return;
        }

        throw new UnauthorizedHttpException('Incorrect credentials');
    }
}