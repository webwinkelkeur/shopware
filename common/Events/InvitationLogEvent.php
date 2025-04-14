<?php

namespace Valued\Shopware\Events;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\EventData\EventDataCollection;
use Shopware\Core\Framework\Event\EventData\ScalarValueType;
use Symfony\Contracts\EventDispatcher\Event;
use Shopware\Core\Framework\Event\FlowEventAware;
use Shopware\Core\Framework\Log\Package;

#[Package('core')]
class InvitationLogEvent extends Event implements FlowEventAware {
    public const LOG_NAME = '%s.invitation';

    private Context $context;
    private string $subject;
    private string $status;
    private $response;
    private string $system;

    public function __construct(string $subject, string $status, string $response, Context $context, string $system) {
        $this->subject = $subject;
        $this->context = $context;
        $this->status = $status;
        $this->response = $response;
        $this->system = $system;
    }

    public static function getAvailableData(): EventDataCollection {
        return (new EventDataCollection())
            ->add('subject', new ScalarValueType(ScalarValueType::TYPE_STRING))
            ->add('status', new ScalarValueType(ScalarValueType::TYPE_STRING))
            ->add('system', new ScalarValueType(ScalarValueType::TYPE_STRING));
    }

    public function getName(): string {
        return sprintf(self::LOG_NAME, $this->system);
    }

    public function getContext(): Context {
        return $this->context;
    }

    public function getSubject(): string {
        return $this->subject;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function getResponse() {
        return $this->response;
    }

    public function getLogData(): array {
        return [
            'subject' => $this->subject,
            'response' => $this->response,
            'status' => $this->status,
        ];
    }

    public function getLogLevel(): string {
        return ($this->status == 'error') ? 'error' : 'info';
    }

    public function getFlowEventName(): string {
        return $this->getName();
    }
}
