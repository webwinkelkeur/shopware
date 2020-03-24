<?php

namespace WebwinkelKeur\Service;

use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\BusinessEventDispatcher;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use WebwinkelKeur\Events\InvitationLogEvent;

class InvitationService {
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    private $dispatcher;

    const INVITATION_URL = 'https://dashboard.webwinkelkeur.nl/api/1.0/invitations.json?id=%s&code=%s';

    const DEFAULT_TIMEOUT = 5;

    const LOG_FAILED = 'Sending invitation has failed';

    private $context;

    public function __construct(
        SystemConfigService $systemConfigService,
        BusinessEventDispatcher $businessEventDispatcher
    ) {
        $this->systemConfigService = $systemConfigService;
        $this->dispatcher = $businessEventDispatcher;
    }

    public function sendInvitation(OrderEntity $order, Context $context) {
        $request = [];
        $this->context = $context;

        $configData = $this->getConfigData();

        if (empty($configData['enableInvitations'])) {
            return;
        }

        $request = $this->getOrderData($order);
        if (empty($request)) {
            return;
        }

        $request['delay'] = $configData['delay'];
        $request['client'] = 'shopware';
        $this->postInvitation($request);
    }

    private function postInvitation($request): void {
        $config = $this->getConfigData();
        $url = sprintf(self::INVITATION_URL, $config['webshopId'], $config['apiKey']);

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
            return;
        }
        curl_close($ch);
    }

    private function getConfigData(): array {
        $configData = [];
        $configData['apiKey'] = $this->systemConfigService->get('WebwinkelKeur.config.webwinkelKeurKey');
        $configData['webshopId'] = $this->systemConfigService->get('WebwinkelKeur.config.webwinkelKeurId');
        $configData['enableInvitations'] = $this->systemConfigService->get('WebwinkelKeur.config.webwinkelKeurInvitation');
        $configData['delay'] = intval($this->systemConfigService->get('WebwinkelKeur.config.webwinkelKeurInvitationDelay'));
        $configData['language'] = $this->systemConfigService->get('WebwinkelKeur.config.webwinkelKeurLanguage');
        if (empty($configData['apiKey'] || empty($configData['webshopId']))) {
            $this->logErrorMessage('Empty API credentials');
            return [];
        }
        return $configData;
    }

    private function getOrderData(OrderEntity $order): array {
        $orderCustomer = $order->getOrderCustomer();
        $orderData = [];
        if (empty($orderCustomer)) {
            $this->logErrorMessage('Customer is NULL');
            return [];
        }

        $orderData['order'] = $order->getOrderNumber();
        $orderData['email'] = $orderCustomer->getEmail();
        $orderData['order_total'] = $order->getAmountTotal();
        $orderData['customer_name'] = $orderCustomer->getFirstName() . ' ' . $orderCustomer->getLastName();
        $orderData['language'] = $this->getOrderLanguage($order);
        return $orderData;
    }

    private function getOrderLanguage(OrderEntity $order): string {
        $language = $this->getConfigData()['language'];
        if ($language == 'cus') {
            $orderLanguage = $order->getLanguage();
            if (!empty($orderLanguage->getLocale()->getCode())) {
                $language = $orderLanguage->getLocale()->getCode();
            }
        }
        return $language;
    }

    private function dispatchLogEvent(string $subject, string $status, string $info): void {
        $invitationLogEvent = new InvitationLogEvent(
            $subject,
            $status,
            $info,
            $this->context
        );
        $this->dispatcher->dispatch($invitationLogEvent);
    }

    private function logErrorMessage($message) {
        $this->dispatchLogEvent(self::LOG_FAILED, 'error', $message);
    }
}
