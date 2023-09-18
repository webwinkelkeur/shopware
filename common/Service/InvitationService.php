<?php

namespace Valued\Shopware\Service;

use Shopware\Core\Checkout\Order\Event\OrderStateMachineStateChangeEvent;
use Shopware\Core\Checkout\Order\OrderEntity;
use Valued\Shopware\Events\InvitationLogEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class InvitationService {
    /**
     * @var DashboardService
     */
    private DashboardService $dashboardService;

    const DEFAULT_TIMEOUT = 5;

    const LOG_FAILED = 'Sending invitation has failed with message: %s';

    private OrderStateMachineStateChangeEvent $orderStateMachineStateChangeEvent;

    private EventDispatcherInterface $dispatcher;

    private $curl;

    public function __construct(
        DashboardService         $dashboardService,
        EventDispatcherInterface $dispatcher
    ) {
        $this->dashboardService = $dashboardService;
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

        if (!$this->hasConsent($order->getOrderNumber())) {
            $this->dispatchLogEvent(
                'Invitation was not created',
                'debug',
                sprintf(
                    'Invite was not created as customer did not consent for order "%s".',
                    $order->getOrderNumber(),
                ),
            );
            return;
        }

        $request['delay'] = intval($this->getConfigValue('delay'));
        $request['client'] = 'shopware';
        $this->postInvitation($request);
    }

    private function postInvitation(array $request): void {
        try {
            $response = $this->doRequest($this->getInvitationUrl(), 'POST', [], $request);
        } catch (\Exception $e) {
            $this->logErrorMessage($e->getMessage());
            return;
        }
        if (isset($response->status) && $response->status == 'success') {
            $this->dispatchLogEvent(
                'Invitation created successfully',
                'debug',
                sprintf($response->message),
            );
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

        try {
            $response = $this->doRequest(
                $this->getHasConsentUrl(),
                'GET',
                ['orderNumber' => $order_number],
            );
        } catch (\Exception $e) {
            $this->dispatchLogEvent('Check consent failed', 'error', $e->getMessage());
            return false;
        }

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

        $options = [CURLOPT_CUSTOMREQUEST => $method];
        if ($data) {
            $options[CURLOPT_POSTFIELDS] = $data;
        }
        $curl = $this->getCurl($url, $options);

        $response = curl_exec($curl);
        if ($response === false) {
            $this->logErrorMessage(sprintf('Request response: (%d) %s', curl_errno($curl), curl_error($curl)));
            return null;
        }

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

    private function logErrorMessage(string $message): void {
        $this->dispatchLogEvent(self::LOG_FAILED, 'error', $message);
    }

    private function dispatchLogEvent(string $subject, string $status, string $info): void {
        $invitation_log_event = new InvitationLogEvent(
            $subject,
            $status,
            $info,
            $this->orderStateMachineStateChangeEvent->getContext(),
            $this->dashboardService->getSystemKey(),
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


    private function getCurl(string $url, array $options) {
        if (!$this->curl) {
            $this->curl = curl_init();
        } else {
            curl_reset($this->curl);
        }

        $default_options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_URL => $url,
            CURLOPT_CONNECTTIMEOUT => self::DEFAULT_TIMEOUT,
            CURLOPT_FAILONERROR => true,
        ];
        if (!curl_setopt_array($this->curl, $default_options + $options)) {
            throw new \RuntimeException('curl_setopt_array failed');
        }

        if (!$this->curl) {
            throw new \RuntimeException('curl_init failed');
        }

        return $this->curl;
    }
}
