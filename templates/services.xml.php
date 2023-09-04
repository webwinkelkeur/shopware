<?php

$system_key = getenv('SYSTEM_KEY');
$system_name = getenv('SYSTEM_NAME');
$dashboard_host = getenv('DASHBOARD_HOST');
echo <<<XML
<?xml version="1.0" ?>

<container xmlns="http://symfony.com/schema/dic/services"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

    <services>
        <!-- services -->
        <service id="{$system_key}.invitation.logger" class="Monolog\Logger">
            <factory service="Shopware\Core\Framework\Log\LoggerFactory" method="createRotating"/>
            <argument type="string">{$system_key}.invitation.logger</argument>
        </service>
        <service id="Valued\Shopware\Service\InvitationService">
            <argument type="service" id="Valued\Shopware\Service\DashboardService"/>
            <argument type="service" id="{$system_key}.invitation.logger"/>
            <argument type="service" id="Symfony\Component\EventDispatcher\EventDispatcherInterface"/>
        </service>
        <service id="Valued\Shopware\Service\DashboardService">
            <argument type="service" id="Shopware\Core\System\SystemConfig\SystemConfigService"/>
            <argument type="string" id="systemKey">{$system_key}</argument>
            <argument type="string" id="systemName">{$system_name}</argument>
            <argument type="string" id="dashboardHost">{$dashboard_host}</argument>
        </service>
        <!-- subscribes -->
        <service id="Valued\Shopware\Subscriber\StorefrontRenderSubscriber">
            <argument type="service" id="Valued\Shopware\Service\DashboardService"/>
            <tag name="kernel.event_subscriber"/>
        </service>
        <!-- events listener -->
        <service id="Valued\Shopware\Listener\OrderListener">
            <argument type="service" id="order.repository"/>
            <argument type="service" id="Valued\Shopware\Service\InvitationService"/>
            <tag name="kernel.event_listener" event="state_enter.order.state.completed" method="onOrderCompleted"/>
        </service>
    </services>

</container>
XML;