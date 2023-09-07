<?php
$name = getenv('SYSTEM_NAME');
$technical_name = getenv('TECHNICAL_NAME');
$javascript_name = sprintf('%sJavascript',lcfirst($technical_name));

$api_test_component = <<< XML
        <component name="dashboard-api-test-button">
            <name>apiTest</name>
            <label>Test API</label>
        </component>
XML;
if (getenv('SYSTEM_KEY') != 'trustprofile') {
    $api_test_component = '';
}

echo <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="https://raw.githubusercontent.com/shopware/platform/master/src/Core/System/SystemConfig/Schema/config.xsd">

    <card>
        <title>{$name}</title>
        <input-field type="text">
            <name>webshopId</name>
            <label>{$name} ID</label>
            <helpText>The ID of the webshop.</helpText>
            <helpText lang="nl-NL">De ID van de webwinkel.</helpText>
        </input-field>
        <input-field type="text">
            <name>apiKey</name>
            <label>API key</label>
            <helpText>Your personal {$name} API code.</helpText>
            <helpText lang="nl-NL">Je persoonlijke {$name} API code.</helpText>
        </input-field>
        {$api_test_component}
    </card>

    <card>
        <title>JavaScript integration</title>
        <input-field type="bool">
            <name>{$javascript_name}</name>
            <label>Enable {$name} JavaScript integration</label>
            <defaultValue>false</defaultValue>
            <helpText>Shows the sidebar.</helpText>
            <helpText lang="nl-NL">Toont de sidebar.</helpText>
        </input-field>
    </card>

    <card>
        <title>Invitations</title>
        <title lang="nl-NL">Uitnodigingen</title>
        <input-field type="bool">
            <name>enableInvitations</name>
            <label>Enable {$name} invitations</label>
            <label lang="nl-NL">{$name}-uitnodigingen inschakelen</label>
            <defaultValue>false</defaultValue>
            <helpText>Sends a review invitation after an order has been completed.</helpText>
            <helpText lang="nl-NL">Verzendt een beoordelingsuitnodiging nadat een bestelling is voltooid.</helpText>
        </input-field>
        <input-field type="bool">
            <name>askForConsent</name>
            <label>Ask for consent</label>
            <label lang="nl-NL">Vraag om toestemming</label>
            <defaultValue>false</defaultValue>
            <helpText>Sends a review invitation only after a consent has been given (requires javascript integration to be enabled).</helpText>
            <helpText lang="nl-NL">Stuurt pas een review-uitnodiging nadat toestemming is gegeven (javascript-integratie moet ingeschakeld zijn).</helpText>
        </input-field>
        <input-field type="int">
            <name>delay</name>
            <label>Delay</label>
            <label lang="nl-NL">Vertraging</label>
            <defaultValue>0</defaultValue>
            <helpText>The (optional) delay in days (use 0 to send the invitation a.s.a.p.).</helpText>
            <helpText lang="nl-NL">De (optionele) vertraging in dagen (gebruik 0 om de uitnodiging z.s.m. te versturen).</helpText>
        </input-field>
        <input-field type="single-select">
            <name>language</name>
            <label>Language</label>
            <label lang="nl-NL">Taal</label>
            <options>
                <option>
                    <id>cus</id>
                    <name>Customer's language</name>
                    <name lang="nl-NL">Taal van de klant</name>
                </option>
                <option>
                    <id>nld</id>
                    <name>Nederlands</name>
                </option>
                <option>
                    <id>eng</id>
                    <name>English</name>
                </option>
                <option>
                    <id>deu</id>
                    <name>Deutsche</name>
                </option>
                <option>
                    <id>fra</id>
                    <name>French</name>
                </option>
                <option>
                    <id>spa</id>
                    <name>Spanish</name>
                </option>
                <option>
                    <id>ita</id>
                    <name>Italian</name>
                </option>
            </options>
            <defaultValue>cus</defaultValue>
        </input-field>
    </card>

</config>
XML;
