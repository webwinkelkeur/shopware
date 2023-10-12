<?php

namespace Valued\Shopware\Service;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class DashboardService {

    private SystemConfigService $systemConfigService;
    private string $systemKey;
    private string $technicalName;
    private string $systemName;
    private string $dashboardHost;

    public function __construct(
        SystemConfigService $system_config_service,
        string $system_key,
        string $technicalName,
        string $systemName,
        string $dashboardHost
    ) {
        $this->systemConfigService = $system_config_service;
        $this->systemKey = $system_key;
        $this->technicalName = $technicalName;
        $this->systemName = $systemName;
        $this->dashboardHost = $dashboardHost;
    }

    public function getSystemKey(): string {
        return $this->systemKey;
    }

    public function getSystemName(): string {
        return $this->systemName;
    }

    public function getDashboardHost(): string {
        return $this->dashboardHost;
    }

    public function getTechnicalName(): string {
        return $this->technicalName;
    }

    public function getConfigValue(string $key, ?string $sales_channel_id) {
        return $this->systemConfigService->get(sprintf(
            '%s.config.%s',
            $this->technicalName,
            $key,
        ), $sales_channel_id);
    }

    public function doRequest(string $url, string $method, array $data): ?array {
        $curl = curl_init();
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FAILONERROR => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_URL => $url,
            CURLOPT_TIMEOUT => 10,

        ];

        if ($method == 'POST') {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
            $options[CURLOPT_HTTPHEADER] = 'Content-Type:application/json';
        }

        if (!curl_setopt_array($curl, $options)) {
            throw new \Exception('Sending set cURL to options');
        }

        $response = curl_exec($curl);

        if ($response === false) {
            throw new \Exception(
                sprintf('(%s) %s', curl_errno($curl), curl_error($curl)),
            );
        }

        return json_decode($response, true);
    }
}
