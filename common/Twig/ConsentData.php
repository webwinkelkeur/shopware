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

        $signature = hash_hmac(
            'sha512',
            http_build_query($oderData),
            $this->getHashKey($salesChannelId)
        );

        $oderData['signature'] = $signature;

        return $oderData;
    }

    private function getHashKey(string $salesChannelId): string {
        return sprintf(
            '%s:%s',
            $this->dashboardService->getConfigValue('webshopId', $salesChannelId),
            $this->dashboardService->getConfigValue('apiKey', $salesChannelId)
        );
    }

}