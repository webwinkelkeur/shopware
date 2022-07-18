<?php

namespace WebwinkelKeur\Shopware\Subscriber;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StorefrontRenderSubscriber implements EventSubscriberInterface {
    /**
     * @var SystemConfigService
     */
    private SystemConfigService $systemConfigService;

    public function __construct(SystemConfigService $system_config_service) {
        $this->systemConfigService = $system_config_service;
    }

    public static function getSubscribedEvents(): array {
        return [
            StorefrontRenderEvent::class => 'onRender',
        ];
    }

    public function onRender(StorefrontRenderEvent $event): void {
        $sales_channel_id = $event->getContext()->getSource()->getSalesChannelId();
        $webshop_id = $this->systemConfigService->get('WebwinkelKeur.config.webshopId', $sales_channel_id);
        $webwinkelkeur_javascript = $this->systemConfigService->get('WebwinkelKeur.config.webwinkelKeurJavascript', $sales_channel_id);
        if (empty($webshop_id)) {
            $webwinkelkeur_javascript = false;
        }
        if (!$event->getRequest()->isXmlHttpRequest()) {
            $event->setParameter(
                '_webwinkelkeur_id',
                $webshop_id
            );
            $event->setParameter(
                'webwinkelkeur_javascript',
                $webwinkelkeur_javascript
            );
        }
    }
}
