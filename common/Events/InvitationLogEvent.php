<?php


namespace Valued\Shopware\Events;

Use \Monolog\Level;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\EventData\EventDataCollection;
use Shopware\Core\Framework\Event\EventData\ScalarValueType;
use Shopware\Core\Framework\Event\FlowEventAware;
use Symfony\Contracts\EventDispatcher\Event;
use Shopware\Core\Framework\Log\LogAware;

class InvitationLogEvent extends Event implements LogAware {
    public const LOG_NAME = '%s.invitation';

    /**
     * @var Context
     */
    private $context;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $status;

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
            ->add('subject', new ScalarValueType(ScalarValueType::TYPE_STRING));
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

    public function getLogData(): array {
        return [
            'subject' => $this->subject,
            'response' => $this->response,
        ];
    }

    public function getLogLevel(): int {
        if ($this->status == 'error') {
            return Level::Error->value;
        }
        return Level::Info->value;
    }
}