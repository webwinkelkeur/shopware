<?php

namespace Valued\Shopware\Subscriber;

use Psr\Log\LoggerInterface;
use Shopware\Core\System\SystemConfig\Event\SystemConfigChangedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Valued\Shopware\Service\DashboardService;

class SystemConfigChangedSubscriber implements EventSubscriberInterface {
    private DashboardService $dashboardService;

    private UrlGeneratorInterface $urlGenerator;

    private LoggerInterface $logger;

    public function __construct(
        DashboardService $dashboardService,
        UrlGeneratorInterface $urlGenerator,
        LoggerInterface $logger
    ) {
        $this->dashboardService = $dashboardService;
        $this->urlGenerator = $urlGenerator;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array {
        return [
            SystemConfigChangedEvent::class => 'onSystemConfigChanged',
        ];
    }

    public function onSystemConfigChanged(SystemConfigChangedEvent $event): void {
        $configKey = sprintf('%s.config.productReviews', $this->dashboardService->getTechnicalName());
        if (!$event->getValue() || $event->getKey() != $configKey) {
            return;
        }

        $salesChannelId = $event->getSalesChannelId();
        $url = sprintf('https://%s/webshops/sync_url', $this->dashboardService->getDashboardHost());
        $sync_url = $this->urlGenerator->generate(
            sprintf('frontend.%s.syncProductReviews', $this->dashboardService->getSystemKey()),
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        try {
            $this->dashboardService->doRequest(
                $url,
                'POST',
                [
                    'webshop_id' => $this->dashboardService->getConfigValue('webshopId', $salesChannelId),
                    'api_key' => $this->dashboardService->getConfigValue('apiKey', $salesChannelId),
                    'url' => $sync_url,
                ]
            );
        } catch (\Exception $e) {
            $this->logger->error(sprintf(
                'Sending sync URL to %s failed with error: %s',
                $this->dashboardService->getSystemName(),
                $e->getMessage(),
            ));
        }
    }
}