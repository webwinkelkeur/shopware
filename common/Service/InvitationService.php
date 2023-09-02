<?php

namespace Valued\Shopware\Service;

use Shopware\Core\Checkout\Order\Event\OrderStateMachineStateChangeEvent;
use Shopware\Core\Checkout\Order\OrderEntity;
use Valued\Shopware\Events\InvitationLogEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Monolog\Logger;

class InvitationService {
    /**
     * @var DashboardService
     */
    private DashboardService $dashboardService;

    const DEFAULT_TIMEOUT = 5;

    const LOG_FAILED = 'Sending invitation has failed with message: %s';

    private OrderStateMachineStateChangeEvent $orderStateMachineStateChangeEvent;

    private LoggerInterface $logger;

    private EventDispatcherInterface $dispatcher;

    public function __construct(
        DashboardService $dashboardService,
        LoggerInterface  $logger,
        EventDispatcherInterface  $dispatcher
    ) {
        $this->dashboardService = $dashboardService;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    public function sendInvitation(OrderEntity $order, OrderStateMachineStateChangeEvent $orderStateMachineStateChangeEvent): void {
        $this->orderStateMachineStateChangeEvent = $orderStateMachineStateChangeEvent;
        $this->dispatchLogEvent('TEST', 'TEST', 'debug');

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

        if (!$this->hasConsent($order->getOrderNumber())) {
            $this->logErrorMessage(sprintf('Invite was not send as customer did not consent for order "%s".', $order->getOrderNumber()));
            return;
        }

        $request['delay'] = intval($this->getConfigValue('delay'));
        $request['client'] = 'shopware';
        $this->postInvitation($request);
    }

    private function postInvitation(array $request): void {
        $response = $this->doRequest($this->getInvitationUrl(), 'POST', [], $request);

        if (isset($response->status) && $response->status == 'success') {
            $this->logger->info('Invitation sent successfully');
            return;
        }
        if (isset($response->message)) {
            $this->logErrorMessage($response->message);
        }
    }

    private function hasConsent(string $order_number): bool {
        if (!$this->getConfigValue('askForConsent')) {
            return true;
        }

        $response = $this->doRequest(
            $this->getHasConsentUrl(),
            'GET',
            ['orderNumber' => $order_number],
        );

        return isset($response->has_consent) && $response->has_consent === true;
    }

    private function doRequest(string $url, string $method, array $params = [], ?array $data = null): ?\stdClass {
        $url = sprintf(
            '%s?%s',
            $url,
            http_build_query(array_merge([
                'id' => $this->getConfigValue('webshopId'),
                'code' => $this->getConfigValue('apiKey'),
            ], $params)));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::DEFAULT_TIMEOUT);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $response = curl_exec($ch);
        if ($response === false) {
            $this->logErrorMessage(sprintf('Request response: (%d) %s', curl_errno($ch), curl_error($ch)));
            return null;
        }

        curl_close($ch);
        return json_decode($response);
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

    private function logErrorMessage(string $message) {
        $this->logger->error(sprintf(self::LOG_FAILED, $message));
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

    private function getConfigValue(string $name) {
        return $this->dashboardService->getConfigValue(
            $name,
            $this->orderStateMachineStateChangeEvent->getSalesChannelId(),
        );
    }

    private function getInvitationUrl(): string {
        return sprintf('https://%s/api/1.0/invitations.json', $this->dashboardService->getDashboardHost());
    }

    public function getHasConsentUrl(): string {
        return sprintf('https://%s/api/2.0/order_permissions.json', $this->dashboardService->getDashboardHost());
    }
}
