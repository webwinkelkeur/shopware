<?php

namespace WebwinkelKeur\Shopware\Listener;

use Shopware\Core\Checkout\Cart\Exception\OrderNotFoundException;
use Shopware\Core\Checkout\Order\Event\OrderStateMachineStateChangeEvent;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use WebwinkelKeur\Shopware\Service\InvitationService;

class OrderListener {
    /**
     * @var InvitationService
     */
    private InvitationService $invitationService;

    /**
     * @var EntityRepository
     */
    private EntityRepository $orderRepository;

    public function __construct(
        EntityRepository $order_repository,
        InvitationService $invitation_service
    ) {
        $this->orderRepository = $order_repository;
        $this->invitationService = $invitation_service;
    }

    public function onOrderCompleted(OrderStateMachineStateChangeEvent $event): void {
        $order = $this->getOrder($event->getOrder()->getUniqueIdentifier(), $event->getContext());
        $this->invitationService->sendInvitation($order, $event);
    }

    /**
     * @throws OrderNotFoundException
     */
    private function getOrder(string $order_id, Context $context): OrderEntity {
        $order_criteria = $this->getOrderCriteria($order_id);
        /** @var OrderEntity|null $order */
        $order = $this->orderRepository->search($order_criteria, $context)->first();
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
