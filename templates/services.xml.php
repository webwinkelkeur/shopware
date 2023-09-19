<?php

$system_key = getenv('SYSTEM_KEY');
$system_name = getenv('SYSTEM_NAME');
$technical_name = getenv('TECHNICAL_NAME');
$dashboard_host = getenv('DASHBOARD_HOST');
echo <<<XML
<?xml version="1.0" ?>

<container xmlns="http://symfony.com/schema/dic/services"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

    <services>
        <!-- services -->
        <service id="Valued\Shopware\Service\InvitationService">
            <argument type="service" id="Valued\Shopware\Service\DashboardService"/>
            <argument type="service" id="Symfony\Component\EventDispatcher\EventDispatcherInterface"/>
        </service>
        <service id="Valued\Shopware\Service\DashboardService">
            <argument type="service" id="Shopware\Core\System\SystemConfig\SystemConfigService"/>
            <argument type="string" id="systemKey">{$system_key}</argument>
            <argument type="string" id="technicalName">{$technical_name}</argument>
            <argument type="string" id="systemName">{$system_name}</argument>
            <argument type="string" id="dashboardHost">{$dashboard_host}</argument>
        </service>
        <service id="Valued\Shopware\Service\ProductReviewService">
            <argument type="service" id="Valued\Shopware\Service\DashboardService"/>
             <argument type="service" id="product.repository"/>
             <argument type="service" id="product_review.repository"/>
             <argument type="service" id="customer.repository"/>
             <argument type="service" id="language.repository"/>
             <argument id="logger" type="service" />
        </service>
        <!-- subscribes -->
        <service id="Valued\Shopware\Subscriber\StorefrontRenderSubscriber">
            <argument type="service" id="Valued\Shopware\Service\DashboardService"/>
            <tag name="kernel.event_subscriber"/>
        </service>
        <service id="Valued\Shopware\Subscriber\SystemConfigChangedSubscriber">
            <argument type="service" id="Valued\Shopware\Service\DashboardService"/>
            <argument type="service" id="Symfony\Contracts\HttpClient\HttpClientInterface"/>
            <argument type="service" id="Symfony\Component\Routing\Generator\UrlGeneratorInterface"/>
            <argument id="logger" type="service" />
            <tag name="kernel.event_subscriber"/>
        </service>
        <!-- events listener -->
        <service id="Valued\Shopware\Listener\OrderListener">
            <argument type="service" id="order.repository"/>
            <argument type="service" id="Valued\Shopware\Service\InvitationService"/>
            <tag name="kernel.event_listener" event="state_enter.order.state.completed" method="onOrderCompleted"/>
        </service>
        <service id="Valued\Shopware\Twig\ConsentData" public="true">
         <argument type="service" id="Valued\Shopware\Service\DashboardService"/>
            <tag name="twig.extension"/>
        </service>
        <service id="{$system_name}\Shopware\Storefront\Controller\\{$system_name}ApiController" public="true">
           <argument type="service" id="Valued\Shopware\Service\DashboardService"/>
           <argument type="service" id="Symfony\Contracts\HttpClient\HttpClientInterface"/>
             <argument type="service" id="Valued\Shopware\Service\ProductReviewService"/>
           <call method="setContainer">
                <argument type="service" id="service_container"/>
           </call>
        </service>
    </services>

</container>
XML;
