<?php declare(strict_types=1);

namespace WebwinkelKeur\Listener;

use Shopware\Core\Checkout\Cart\Exception\OrderNotFoundException;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\StateMachine\Event\StateMachineStateChangeEvent;
use WebwinkelKeur\Service\InvitationService;

class OrderListener {


    /**
     * @var InvitationService
     */
    private $invitationService;
    /**
     * @var EntityRepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        EntityRepositoryInterface $orderRepository,
        InvitationService $invitationService
    ) {
        $this->orderRepository = $orderRepository;
        $this->invitationService = $invitationService;
    }

    public function onOrderStateChange(StateMachineStateChangeEvent $event): void {
        $orderId = $event->getTransition()->getEntityId();
        $context = $event->getContext();
        $state = $event->getStateEventName();
        $order = $this->getOrder($orderId, $context);
        //every OrderState event is dispatched twice, on entering and leaving the state
        if ($state == 'state_enter.order.state.completed') {
            $this->invitationService->sendInvitation($order, $context);
        }
    }

    /**
     * @throws OrderNotFoundException
     */
    private function getOrder(string $orderId, Context $context): OrderEntity {
        $orderCriteria = $this->getOrderCriteria($orderId);
        /** @var OrderEntity|null $order */
        $order = $this->orderRepository->search($orderCriteria, $context)->first();
        if ($order === null) {
            throw new OrderNotFoundException($orderId);
        }

        return $order;
    }

    private function getOrderCriteria(string $orderId): Criteria {
        $orderCriteria = new Criteria([$orderId]);
        $orderCriteria->addAssociation('orderCustomer.customer');
        $orderCriteria->addAssociation('stateMachineState');
        $orderCriteria->addAssociation('language');

        return $orderCriteria;
    }
}
