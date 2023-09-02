<?php


namespace Valued\Shopware\Events;

use Monolog\Logger;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\EventData\EventDataCollection;
use Shopware\Core\Framework\Event\EventData\ScalarValueType;
use Shopware\Core\Framework\Event\FlowEventAware;
use Shopware\Core\Framework\Log\LogAware;
use Symfony\Contracts\EventDispatcher\Event;
use Shopware\Core\Content\Flow\Dispatching\Aware\SubjectAware;

class InvitationLogEvent extends Event implements LogAware, SubjectAware, FlowEventAware {
    public const LOG_NAME = 'review.invitation';

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

    public function __construct(string $subject, string $status, string $response, Context $context) {
        $this->subject = $subject;
        $this->context = $context;
        $this->status = $status;
        $this->response = $response;
    }

    public static function getAvailableData(): EventDataCollection {
        return (new EventDataCollection())
            ->add('subject', new ScalarValueType(ScalarValueType::TYPE_STRING));
    }

    public function getName(): string {
        return self::LOG_NAME;
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
            return Logger::ERROR;
        }
        return Logger::INFO;
    }
}