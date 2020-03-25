<?php

namespace WebwinkelKeur\Subscriber;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StorefrontRenderSubscriber implements EventSubscriberInterface {
    /**
     * @var SystemConfigService
     */
    private $system_config_service;

    public function __construct(SystemConfigService $system_config_service) {
        $this->system_config_service = $system_config_service;
    }

    public static function getSubscribedEvents(): array {
        return [
            StorefrontRenderEvent::class => 'onRender',
        ];
    }

    public function onRender(StorefrontRenderEvent $event): void {
        $webshop_id= $this->system_config_service->get('WebwinkelKeur.config.webshopId');
        $webwinkelkeur_javascript = $this->system_config_service->get('WebwinkelKeur.config.webwinkelKeurJavascript');
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
