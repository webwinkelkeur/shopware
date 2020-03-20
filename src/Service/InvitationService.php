<?php

namespace WebwinkelKeur\Service;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateEntity;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\Checkout\Order\OrderExceptionHandler;
use Shopware\Core\Framework\Event\BusinessEventDispatcher;
use WebwinkelKeur\Events\InvitationLogEvent;


class InvitationService {
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    private $dispatcher;

    const INVITATION_URL = 'https://dashboard.webwinkelkeur.nl/api/1.0/invitations.json?id=%s&code=%s';

    const DEFAULT_TIMEOUT = 5;

    private $context;

    public function __construct(
        SystemConfigService $systemConfigService,
        BusinessEventDispatcher $businessEventDispatcher
    ) {
        $this->systemConfigService = $systemConfigService;
        $this->dispatcher = $businessEventDispatcher;
    }

    public function sendInvitation(OrderEntity $order, $context) {
        $request = [];
        $configData = $this->getConfigData();
        $this->context = $context;

        if (empty($configData['enableInvitations'])) {
            return;
        }
        if (!$this->isOrderCompleted($order)) {
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
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::DEFAULT_TIMEOUT);
            $response = curl_exec($ch);
            if ($response == false) {
                $this->dispatchLogEvent(
                    "Sending invitation failed",
                    "error",
                    sprintf("Send invitations request failed: (%d) %s", curl_errno($ch), curl_error($ch)));

            }

            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
                $invitationLogEvent = new InvitationLogEvent(
                    "Sending invitation failed", "error",
                    sprintf("Failed code: (%d)", curl_getinfo($ch, CURLINFO_HTTP_CODE)),
                    $this->context);
                $this->dispatcher->dispatch($invitationLogEvent);
            } else {
                $this->dispatchLogEvent(
                    "Invitation sent successfully",
                    "debug",
                    sprintf("WebwinkelKeur reivew invitation was sent successfully for order : %d", $request['order']));
            }
            curl_close($ch);

        } catch (Exception $e) {
            $this->dispatchLogEvent(
                "Sending invitation failed",
                "error",
                sprintf("Sending invitations request failed with error %s ", $e->getMessage())
            );
        }

    }

    private function getConfigData(): array {
        $configData = [];
        $configData['apiKey'] = $this->systemConfigService->get('WebwinkelKeur.config.apiKey');
        $configData['webshopId'] = $this->systemConfigService->get('WebwinkelKeur.config.webshopId');
        $configData['enableInvitations'] = $this->systemConfigService->get('WebwinkelKeur.config.enableInvitations');
        $configData['delay'] = intval($this->systemConfigService->get('WebwinkelKeur.config.delay'));
        $configData['language'] = $this->systemConfigService->get('WebwinkelKeur.config.language');
        if (empty($configData['apiKey'] || empty($configData['webshopId']))) {
            $this->dispatchLogEvent("Sending invitation failed", "error", "Empty API credentials");
            return [];
        }
        return $configData;
    }

    private function getOrderData(OrderEntity $order): array {
        $orderCustomer = $order->getOrderCustomer();
        $orderData = [];
        if (empty($orderCustomer)) {
            $this->dispatchLogEvent(
                "Sending invitation failed",
                "error",
                "Customer is NULL "
            );
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
        try {
            if ($language == 'cus') {
                //TODO find language names used by Shopware
                $lanArray = ['NL' => 'nld', 'English' => 'eng', 'Deutsch' => 'deu', 'FR' => 'fra', 'ES' => 'spa'];
                $orderLanguage = $order->getLanguage();
                if (!empty($orderLanguage)) {
                    $orderLanguageName = $orderLanguage->getName();
                    if (isset($lanArray[$orderLanguageName])) {
                        $language = $lanArray[$orderLanguageName];
                    }
                }
            }
        } catch (\Throwable $e) {
        }
        return $language;
    }

    private function dispatchLogEvent(string $subject, string $status, string $info): void {
        $invitationLogEvent = new InvitationLogEvent(
            $subject,
            $status,
            $info,
            $this->context);
        $this->dispatcher->dispatch($invitationLogEvent);
    }

    public function isOrderCompleted($order) {
        $stateMachineState = $order->getStateMachineState();
        if (!empty($stateMachineState)) {
            if ($stateMachineState->getTechnicalName() == "completed") {
                return true;
            }
        }
        return false;
    }
}
