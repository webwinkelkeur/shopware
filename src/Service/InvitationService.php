<?php

namespace WebwinkelKeur\Shopware\Service;

use Shopware\Core\Checkout\Order\Event\OrderStateMachineStateChangeEvent;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Event\BusinessEventDispatcher;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use WebwinkelKeur\Shopware\Events\InvitationLogEvent;

class InvitationService {
    /**
     * @var SystemConfigService
     */
    private SystemConfigService $systemConfigService;

    private BusinessEventDispatcher $dispatcher;

    const INVITATION_URL = 'https://dashboard.webwinkelkeur.nl/api/1.0/invitations.json';

    const DEFAULT_TIMEOUT = 5;

    const LOG_FAILED = 'Sending invitation has failed';

    private OrderStateMachineStateChangeEvent $orderStateMachineStateChangeEvent;

    public function __construct(
        SystemConfigService $system_config_service,
        BusinessEventDispatcher $dispatcher
    ) {
        $this->systemConfigService = $system_config_service;
        $this->dispatcher = $dispatcher;
    }

    public function sendInvitation(OrderEntity $order, OrderStateMachineStateChangeEvent $orderStateMachineStateChangeEvent): void {
        $this->orderStateMachineStateChangeEvent = $orderStateMachineStateChangeEvent;

        if (
            empty($this->getConfigValue('apiKey')) ||
            empty($this->getConfigValue('webshopId'))
        ) {
            $this->logErrorMessage('Empty API credentials');
            return;
        }

        if (empty($this->getConfigValue('enableInvitations'))) {
            return;
        }

        $request = $this->getOrderData($order);
        if (empty($request)) {
            return;
        }

        $request['delay'] = intval($this->getConfigValue('delay'));
        $request['client'] = 'shopware';
        $this->postInvitation($request);
    }

    private function postInvitation($request): void {
        $url = self::INVITATION_URL . '?' . http_build_query([
                'id' => $this->getConfigValue('webshopId'),
                'code' => $this->getConfigValue('apiKey'),
            ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::DEFAULT_TIMEOUT);
        $response = curl_exec($ch);
        if ($response === false) {
            $this->logErrorMessage(sprintf('Request response: (%d) %s', curl_errno($ch), curl_error($ch)));
            return;
        }
        curl_close($ch);
        $response = json_decode($response);

        if (isset($response->status) && $response->status == 'success') {
            $this->dispatchLogEvent(
                'Invitation sent successfully',
                'debug',
                sprintf($response->message)
            );
            return;
        }
        if (isset($response->message)) {
            $this->logErrorMessage($response->message);
        }
    }

    private function getOrderData(OrderEntity $order): array {
        $order_customer = $order->getOrderCustomer();
        $order_data = [];
        if (empty($order_customer)) {
            $this->logErrorMessage('Customer is NULL');
            return [];
        }

        $order_data['order'] = $order->getOrderNumber();
        $order_data['email'] = $order_customer->getEmail();
        $order_data['order_total'] = $order->getAmountTotal();
        $order_data['customer_name'] = $order_customer->getFirstName() . ' ' . $order_customer->getLastName();
        $order_data['language'] = $this->getOrderLanguage($order);
        return $order_data;
    }

    private function getOrderLanguage(OrderEntity $order): string {
        $language = $this->getConfigValue('language');
        if ($language == 'cus') {
            $order_language = $order->getLanguage();
            if (!empty($order_language->getLocale()->getCode())) {
                $language = $order_language->getLocale()->getCode();
            }
        }
        return $language;
    }

    private function dispatchLogEvent(string $subject, string $status, string $info): void {
        $invitation_log_event = new InvitationLogEvent(
            $subject,
            $status,
            $info,
            $this->orderStateMachineStateChangeEvent->getContext()
        );
        $this->dispatcher->dispatch($invitation_log_event);
    }

    private function logErrorMessage($message) {
        $this->dispatchLogEvent(self::LOG_FAILED, 'error', $message);
    }

    private function getConfigValue(string $name) {
        return $this->systemConfigService->get(
            "WebwinkelKeur.config.{$name}",
            $this->orderStateMachineStateChangeEvent->getSalesChannelId()
        );
    }
}
