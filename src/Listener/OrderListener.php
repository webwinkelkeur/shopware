<?php

namespace WebwinkelKeur\Listener;

use Shopware\Core\Checkout\Cart\Exception\OrderNotFoundException;
use Shopware\Core\Checkout\Order\Event\OrderStateMachineStateChangeEvent;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use WebwinkelKeur\Service\InvitationService;

class OrderListener {
    /**
     * @var InvitationService
     */
    private $invitation_service;

    /**
     * @var EntityRepositoryInterface
     */
    private $order_repository;

    public function __construct(
        EntityRepositoryInterface $order_repository,
        InvitationService $invitation_service
    ) {
        $this->order_repository = $order_repository;
        $this->invitation_service = $invitation_service;
    }

    public function onOrderCompleted(OrderStateMachineStateChangeEvent $event): void {
        $context = $event->getContext();
        $order = $this->getOrder($event->getOrder()->getUniqueIdentifier(), $context);
        $this->invitation_service->sendInvitation($order, $context);
    }

    /**
     * @throws OrderNotFoundException
     */
    private function getOrder(string $order_id, Context $context): OrderEntity {
        $order_criteria = $this->getOrderCriteria($order_id);
        /** @var OrderEntity|null $order */
        $order = $this->order_repository->search($order_criteria, $context)->first();
        if ($order === null) {
            throw new OrderNotFoundException($order_id);
        }

        return $order;
    }

    private function getOrderCriteria(string $order_id): Criteria {
        $order_criteria = new Criteria([$order_id]);
        $order_criteria->addAssociation('orderCustomer.customer');
        $order_criteria->addAssociation('language.locale');
        return $order_criteria;
    }
}
