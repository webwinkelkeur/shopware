<?php

namespace Valued\Shopware\Service;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductReviewService {
    private DashboardService $dashboardService;

    private LoggerInterface $logger;

    private EntityRepository $productRepository;

    private EntityRepository $productReviewRepository;

    private EntityRepository $customerReviewRepository;

    private EntityRepository $languageRepository;

    public function __construct(
        DashboardService $dashboardService,
        EntityRepository $productRepository,
        EntityRepository $productReviewRepository,
        EntityRepository $customerReviewRepository,
        EntityRepository $languageRepository,
        LoggerInterface  $logger
    ) {
        $this->dashboardService = $dashboardService;
        $this->productRepository = $productRepository;
        $this->productReviewRepository = $productReviewRepository;
        $this->customerReviewRepository = $customerReviewRepository;
        $this->languageRepository = $languageRepository;
        $this->logger = $logger;
    }

    public function sync(array $data, Context $context): ?string {
        $productReviewData = $data['product_review'];

        $product = $this->getProduct($productReviewData['product_id'], $context);
        if (!$product) {
            throw new NotFoundHttpException(sprintf('Product (%s) not found', $productReviewData['product_id']));
        }

        if ($productReviewData['deleted']) {
            $this->productReviewRepository->delete([
                [
                    'id' => $productReviewData['id'],
                ],
            ], $context);
        }

        $productReviewId = $productReviewData['id'] ?? Uuid::randomHex();
        $this->productReviewRepository->upsert([
            [
                'id' => $productReviewId,
                'productId' => $productReviewData['product_id'],
                'customerId' => $this->getCustomerId($productReviewData['reviewer']['email'], $context),
                'languageId' => $this->getLanguageId($productReviewData, $context),
                'salesChannelId' => $context->getSource()->getSalesChannelId(),
                'title' => $productReviewData['title'],
                'content' => $productReviewData['review'],
                'points' => $productReviewData['rating'],
                'status' => true,
                'createdAt' => $productReviewData['created'],
            ],
        ], $context);

        return $productReviewId;
    }

    private function getProduct(string $productId, Context $context): ?ProductEntity {
       return $this->productRepository->search(new Criteria([$productId]), $context)->first();
    }

    private function getLanguageId($productReviewData, $context): ?string {
        $locale = $productReviewData['locale'] ?? null;
        if (!$locale) {
            return null;
        }
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('language.locale.code', $locale));
        return $this->languageRepository->searchIds($criteria, $context)->firstId();
    }

    private function getCustomerId(string $email, Context $context): ?string {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('email', $email));
        $customer = $this->customerReviewRepository->search($criteria, $context)->first();
        if ($customer) {
            return $customer->getId();
        }
        return null;
    }
}