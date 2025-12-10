<?php

namespace App\Controller;

use App\DTO\MakePurchasePayloadDto;
use App\Service\PurchaseProcessingService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class MakePurchaseController extends AbstractController
{
    #[Route('/make/purchase', name: 'app_make_purchase', methods: ['POST'])]
    public function index(
        PurchaseProcessingService $purchaseProcessingService,
        #[MapRequestPayload] MakePurchasePayloadDto $purchasePayload,
    ): JsonResponse
    {
        $transactions = $purchaseProcessingService->buyItem($purchasePayload->userId, $purchasePayload->itemId);

        return is_array($transactions) ? new JsonResponse($transactions) : new JsonResponse(['failed']);
    }
}
