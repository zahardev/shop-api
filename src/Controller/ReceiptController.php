<?php

namespace App\Controller;

use App\Entity\Receipt;
use App\Entity\ReceiptItem;
use App\Repository\ProductRepository;
use App\Repository\ReceiptRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

class ReceiptController extends BaseController
{

    private $productRepository;

    private $em;

    private $receiptRepository;


    public function __construct(
        EntityManagerInterface $em,
        ProductRepository $productRepository,
        ReceiptRepository $receiptRepository
    ) {
        $this->em = $em;
        $this->productRepository = $productRepository;
        $this->receiptRepository = $receiptRepository;
    }


    /**
     * @Route("/receipts", methods={"POST"})
     * @return Response
     * @throws \Exception
     */
    public function newReceipt()
    {
        $receipt = new Receipt();

        $this->em->persist($receipt);
        $this->em->flush();

        $response = new JsonResponse($receipt->toArray(), Response::HTTP_CREATED);
        $response->headers->set('Location', 'new_receipt_location'); //todo

        return $response;
    }


    /**
     * @Route("/receipts/{uuid}", methods={"GET"})
     * @return Response
     */
    public function getReceipt($uuid)
    {
        $receipt = $this->receiptRepository->findOneBy(['uuid' => $uuid]);

        if (empty($receipt)) {
            throw new HttpException(400, 'Could not find such receipt!');
        }

        $response = new JsonResponse($receipt->toArray(), Response::HTTP_OK);

        return $response;
    }


    /**
     * @Route("/receipts/{uuid}", methods={"PATCH"})
     * @param string $uuid
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function handlePATCH(string $uuid, Request $request)
    {
        $content = $this->getJSONContent($request);
        $this->validatePATCHContent($content);

        if ($this->isAddReceiptItemRequest($content)) {
            $this->validateAddReceiptItemContent($content);
            $value = $content['value'];

            return $this->addReceiptItem($uuid, $value['barcode'], $value['quantity']);
        }

        if ($this->isFinishReceiptRequest($content)) {
            return $this->finishReceipt($uuid);
        }


        throw new HttpException(400, 'Could not process the request!');
    }


    /**
     * @param array $content
     * @return bool
     */
    private function isAddReceiptItemRequest(array $content)
    {
        return 'add' === $content['op'] && '/items' === $content['path'];
    }

    /**
     * @param array $content
     * @return bool
     */
    private function isFinishReceiptRequest(array $content)
    {
        return 'replace' === $content['op'] && '/status' === $content['path'] && 'finished' === $content['value'];
    }


    /**
     * @param string $uuid
     * @return JsonResponse
     */
    private function finishReceipt(string $uuid)
    {
        $receipt = $this->receiptRepository->findOneBy(['uuid' => $uuid]);

        if (empty($receipt)) {
            throw new HttpException(400, 'Could not find such receipt!');
        }

        $receipt->finish();

        $this->em->persist($receipt);
        $this->em->flush();

        return new JsonResponse($receipt->toArray(), 200);
    }


    /**
     * @param string $uuid
     * @param int $barcode
     * @param int $quantity
     * @return JsonResponse
     * @throws \Exception
     */
    private function addReceiptItem(string $uuid, int $barcode, int $quantity)
    {
        $receipt = $this->receiptRepository->findOneBy(['uuid' => $uuid]);

        if (empty($receipt)) {
            throw new HttpException(400, 'Could not find such receipt!');
        }

        $product = $this->productRepository->findOneBy(['barcode' => $barcode]);

        if (empty($product)) {
            throw new HttpException(400, 'Could not find such product!');
        }

        $receiptItem = new ReceiptItem($product, $quantity);
        $receipt->addReceiptItem($receiptItem);

        $this->em->persist($receipt);
        $this->em->flush();

        return new JsonResponse($receipt->toArray(), 200);
    }


    /**
     * @param $content
     * @throws \Exception
     */
    private function validateAddReceiptItemContent($content)
    {
        foreach (['barcode', 'quantity'] as $key) {
            if (!array_key_exists($key, $content['value'])) {
                throw new HttpException(400, sprintf('Property value should contain %s key!', $key));
            }
        }
    }
    

    /**
     * @param array $content
     * @throws \Exception
     */
    private function validatePATCHContent(array $content)
    {
        foreach (['op', 'path', 'value'] as $key) {
            if (!array_key_exists($key, $content)) {
                throw new HttpException(400, sprintf('Request JSON should contain %s key!', $key));
            }
        }

        $allowedMap = [
            'op' => ['add', 'replace'],
            'path' => ['/items', '/status'],
        ];

        foreach ($allowedMap as $property => $allowedValues) {
            if (!in_array($content[$property], $allowedValues)) {
                throw new HttpException(
                    400,
                    sprintf(
                        'Property %s can have only such value(s): %s',
                        $property,
                        implode(', ', $allowedValues)
                    )
                );
            }
        }
    }
}
