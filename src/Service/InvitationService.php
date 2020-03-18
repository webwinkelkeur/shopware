<?php
namespace WebwinkelKeur\Service;

use Dompdf\Exception;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateEntity;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\Checkout\Order\OrderExceptionHandler;

class InvitationService {
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    const INVITATION_URL = 'https://dashboard.webwinkelkeur.nl/api/1.0/invitations.json?id=%s&code=%s';

    const DEFAULT_TIMEOUT = 5;

    public function __construct(SystemConfigService $systemConfigService) {
        $this->systemConfigService = $systemConfigService;
    }

    public function sendInvitation(OrderEntity $order) {
        $request = [];
        $configData = $this->getConfigData();
        if (empty($configData)) {
            error_log("CONFIG data is empty");
            return;
        }
        if (empty($configData['enableInvitations'])) {
            error_log("Invitations not enabled");
            return;
        }

        $request = $this->getOrderData($order);
        if (empty($request)) {
            error_log("Request data is empty");
            return;
        }

        $request['delay'] = $configData['delay'];
        $request['language'] = $this->getOrderLanguage($order);
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
                error_log(sprintf(
                    "Send invitations request failed: (%d) %s",
                    curl_errno($ch), curl_error($ch)));
            }

            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
                error_log(sprintf(
                    "Send invitations request failed: (%d)",
                    curl_getinfo($ch, CURLINFO_HTTP_CODE)));
            }

            curl_close($ch);

        } catch (Exception $e) {
            error_log("Sending invitations request failed with error " . $e->getMessage());
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
            error_log("API authentication is empty");
            return [];
        }
        return $configData;
    }

    private function getOrderData(OrderEntity $order): array {
        $orderCustomer = $order->getOrderCustomer();
        $stateMachineState = $order->getStateMachineState();
        $orderData = [];
        if (empty($orderCustomer)) {
            error_log("Customer is NULL");
            return [];
        }
        if (empty($stateMachineState)) {
            error_log("StateMachine is NULL");
            return [];
        }
        if ($stateMachineState->getTechnicalName() != "completed") {
            error_log("Order is not completed");
            return [];
        }

        $orderData['order'] = $order->getOrderNumber();
        $orderData['email'] = $orderCustomer->getEmail();
        $orderData['order_total'] = $order->getAmountTotal();
        $orderData['customer_name'] = $orderCustomer->getFirstName() . ' ' . $orderCustomer->getLastName();
        return $orderData;
    }

    private function getOrderLanguage(OrderEntity $order) {

        $language = $this->getConfigData()['language'];
        try {
            if ($language == 'cus') {
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
            error_log("ORDER LANGUAGE IS NULL");
        }
        return $language;
    }


}
