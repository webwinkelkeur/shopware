<?php

namespace Valued\Shopware\Twig;

use Shopware\Core\Checkout\Order\OrderEntity;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Valued\Shopware\Service\DashboardService;

class ConsentData extends AbstractExtension {

    private DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService) {
        $this->dashboardService = $dashboardService;
    }

    public function getFunctions() {
        return [
            new TwigFunction('getConsentOrderData', [$this, 'getConsentOrderData']),
        ];
    }

    public function getConsentOrderData(OrderEntity $orderEntity): array {
        $salesChannelId = $orderEntity->getSalesChannelId();
        $customer = $orderEntity->getOrderCustomer();
        $oderData =  [
            'webshopId' => $this->dashboardService->getConfigValue('webshopId', $salesChannelId),
            'orderNumber' => $orderEntity->getOrderNumber(),
            'email' => $customer->getEmail(),
            'firstName' => $customer->getFirstName(),
            'inviteDelay' => $this->dashboardService->getConfigValue('delay', $salesChannelId),
        ];

        if ($signature = $this->getSignature($oderData, $salesChannelId)) {
            $oderData['signature'] = $signature;
        }

        return $oderData;
    }

    private function getSignature(array $oderData, string $salesChannelId): ?string {
        $webshopId = (int) $this->dashboardService->getConfigValue('webshopId', $salesChannelId);
        $apiKey = (string) $this->dashboardService->getConfigValue('apiKey', $salesChannelId);
        if (!$webshopId || !$apiKey) {
            return null;
        }

        return hash_hmac(
            'sha512',
            http_build_query($oderData),
            sprintf(
                '%s:%s',
                $webshopId,
                $apiKey
            )
        );
    }
}
