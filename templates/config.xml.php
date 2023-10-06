<?php
$name = getenv('SYSTEM_NAME');
$javascript_name = sprintf('%sJavascript',lcfirst($name));

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
        <input-field type="int">
            <name>webshopId</name>
            <label>{$name} ID</label>
            <helpText>The ID of the webshop.</helpText>
            <helpText lang="en-GB">The ID of the webshop.</helpText>
            <helpText lang="nl-NL">De ID van de webwinkel.</helpText>
            <helpText lang="de-DE">Die ID des Webshops.</helpText>
        </input-field>
        <input-field type="text">
            <name>apiKey</name>
            <label>API key</label>
            <helpText>Your personal {$name} API code.</helpText>
            <helpText lang="en-GB">Your personal {$name} API code.</helpText>
            <helpText lang="nl-NL">Je persoonlijke {$name} API code.</helpText>
            <helpText lang="de-DE">Ihr persönlicher {$name} API-Code.</helpText>
        </input-field>
        {$api_test_component}
    </card>

    <card>
        <title>JavaScript integration</title>
        <input-field type="bool">
            <name>{$javascript_name}</name>
            <label>Enable {$name} JavaScript integration</label>
            <label lang="en-GB">Enable {$name} JavaScript integration</label>
            <label lang="nl-NL">Enable {$name} JavaScript integration</label>
            <label lang="de-DE">Aktiviere {$name} JavaScript-Integration</label>
            <defaultValue>false</defaultValue>
            <helpText>Shows the sidebar.</helpText>
            <helpText lang="en-GB">Shows the sidebar.</helpText>
            <helpText lang="nl-NL">Toont de sidebar.</helpText>
            <helpText lang="de-DE">Zeigt die Sidebar an.</helpText>
        </input-field>
    </card>

    <card>
        <title>Invitations</title>
        <title lang="nl-NL">Uitnodigingen</title>
        <input-field type="bool">
            <name>enableInvitations</name>
            <label>Enable {$name} invitations</label>
            <label lang="en-GB">Enable {$name} invitations</label>
            <label lang="nl-NL">{$name}-uitnodigingen inschakelen</label>
            <label lang="de-DE">Aktivieren Sie {$name} Einladungen</label>
            <defaultValue>false</defaultValue>
            <helpText>Sends a review invitation after an order has been completed.</helpText>
            <helpText lang="en-GB">Sends a review invitation after an order has been completed.</helpText>
            <helpText lang="nl-NL">Verzendt een beoordelingsuitnodiging nadat een bestelling is voltooid.</helpText>
            <helpText lang="de-DE">Sendet eine Einladung zur Bewertung, nachdem eine Bestellung abgeschlossen wurde.</helpText>
        </input-field>
        <input-field type="bool">
            <name>askForConsent</name>
            <label>Ask for consent</label>
            <label lang="en-GB">Ask for consent</label>
            <label lang="nl-NL">Vraag om toestemming</label>
            <label lang="de-DE">Um Zustimmung bitten</label>
            <defaultValue>false</defaultValue>
            <helpText lang="en-GB">Sends a review invitation only after a consent has been given (requires javascript integration to be enabled).</helpText>
            <helpText>Sends a review invitation only after a consent has been given (requires javascript integration to be enabled).</helpText>
            <helpText lang="nl-NL">Stuurt pas een review-uitnodiging nadat toestemming is gegeven (javascript-integratie moet ingeschakeld zijn).</helpText>
            <helpText lang="de-DE">Sendet eine Bewertungseinladung nur nach einer Zustimmung (erfordert die Aktivierung der JavaScript-Integration).</helpText>
        </input-field>
        <input-field type="bool">
            <name>returningCustomers</name>
            <label>Invite returning customers</label>
            <label lang="en-GB">Invite returning customers</label>
            <label lang="nl-NL">Vraag om toestemming</label>
            <label lang="de-DE">Wiederkehrende Kunden einladen</label>
            <defaultValue>true</defaultValue>
            <helpText>If se to "Yes", customers will get new review invite for every new order</helpText>
            <helpText lang="en-GB">If se to "Yes", customers will get new review invite for every new order</helpText>
            <helpText lang="nl-NL">Als dit is ingesteld op "Ja", krijgen klanten voor elke nieuwe bestelling een nieuwe uitnodiging voor een beoordeling</helpText>
            <helpText lang="de-DE">Bei der Einstellung "Ja" erhalten die Kunden für jede neue Bestellung eine neue Einladung zur Bewertung.</helpText>
        </input-field>
        <input-field type="bool">
            <name>productReviews</name>
            <label>Product Reviews</label>
            <label lang="en-GB">Product reviews</label>
            <label lang="nl-NL">Productbeoordelingen</label>
            <label lang="de-DE">Produktprüfungen</label>
            <defaultValue>false</defaultValue>
            <helpText>Automatically display product reviews collected using {$name} on your Shopware store</helpText>
            <helpText lang="en-GB">Automatically display product reviews collected using {$name} on your Shopware store</helpText>
            <helpText lang="nl-NL">Geef productbeoordelingen die zijn verzameld met {$name} automatisch weer in je Shopware-winkel</helpText>
            <helpText lang="de-DE">Automatische Anzeige der mit {$name} gesammelten Produktbewertungen in Ihrem Shopware-Shop</helpText>
        </input-field>
        <input-field type="int">
            <name>delay</name>
            <label>Delay</label>
            <label lang="en-GB">Delay</label>
            <label lang="nl-NL">Vertraging</label>
            <label lang="de-DE">Verzögerung</label>
            <defaultValue>0</defaultValue>
            <helpText lang="en-GB">The (optional) delay in days (use 0 to send the invitation a.s.a.p.).</helpText>
            <helpText lang="nl-NL">De (optionele) vertraging in dagen (gebruik 0 om de uitnodiging z.s.m. te versturen).</helpText>
            <helpText lang="de-DE">Die (optionale) Verzögerung in Tagen (verwenden Sie 0, um die Einladung sofort zu versenden).</helpText>
        </input-field>
        <input-field type="single-select">
            <name>language</name>
            <label>Language</label>
            <label lang="en-GB">Language</label>
            <label lang="nl-NL">Taal</label>
            <label lang="de-DE">Sprache</label>
            <helpText>Invitation language - 'Customer's language' will create the invitation based on the order language.</helpText>
            <helpText lang="en-GB">Invitation language - 'Customer's language' will create the invitation based on the order language.</helpText>
            <helpText lang="nl-NL">Taal uitnodiging - 'Taal van de klant' maakt de uitnodiging op basis van de taal van de bestelling.</helpText>
            <helpText lang="de-DE">Einladungssprache - 'Die Sprache des Kunden' erstellt die Einladung auf der Grundlage der Bestellsprache.</helpText>
            <options>
                <option>
                    <id>cus</id>
                    <name>Customer's language</name>
                    <name lang="en-GB">Customer's language</name>
                    <name lang="nl-NL">Taal van de klant</name>
                    <name lang="de-DE">Sprache des Kunden</name>
                </option>
                <option>
                    <id>nld</id>
                    <name>Nederlands</name>
                    <name lang="en-GB">Nederlands</name>
                    <name lang="nl-NL">Nederlands</name>
                    <name lang="de-DE"> Niederländisch</name>
                </option>
                <option>
                    <id>eng</id>
                    <name>English</name>
                    <name lang="en-GB">English</name>
                    <name lang="nl-NL">Engels</name>
                    <name lang="de-DE">Englisch</name>
                </option>
                <option>
                    <id>deu</id>
                    <name>Deutsche</name>
                    <name lang="en-GB">Deutsche</name>
                    <name lang="nl-NL">Duits</name>
                    <name lang="de-DE">Deutsch</name>
                </option>
                <option>
                    <id>fra</id>
                    <name>French</name>
                    <name lang="en-GB">French</name>
                    <name lang="nl-NL">Frans</name>
                    <name lang="de-DE">Französisch</name>
                </option>
                <option>
                    <id>spa</id>
                    <name>Spanish</name>
                    <name lang="en-GB">Spanish</name>
                    <name lang="nl-NL">Spaans</name>
                    <name lang="de-DE">Spanisch</name>
                </option>
                <option>
                    <id>ita</id>
                    <name>Italian</name>
                    <name lang="en-GB">Italian</name>
                    <name lang="nl-NL">Italiaans</name>
                    <name lang="de-DE">Italienisch</name>
                </option>
                <option>
                    <id>bul</id>
                    <name>Bulgarian</name>
                    <name lang="en-GB">Bulgarian</name>
                    <name lang="nl-NL">Bulgaars</name>
                    <name lang="de-DE">Bulgarisch</name>
                </option>
                <option>
                    <id>hrv</id>
                    <name>Croatian</name>
                    <name lang="en-GB">Croatian</name>
                    <name lang="nl-NL">Kroatisch</name>
                    <name lang="de-DE">Kroatisch</name>
                </option>
            </options>
            <defaultValue>cus</defaultValue>
        </input-field>
    </card>

</config>
XML;
