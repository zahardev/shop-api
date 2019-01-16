<?php

namespace App\Controller;

use App\Entity\Receipt;
use App\Entity\ReceiptItem;
use App\Repository\ProductRepository;
use App\Repository\ReceiptRepository;
use App\Utils\ReceiptValidator;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @IsGranted("ROLE_CASH_REGISTER")
 * */
class ReceiptController extends BaseController
{

    private $productRepository;

    private $em;

    private $receiptRepository;

    private $validator;


    public function __construct(
        EntityManagerInterface $em,
        ProductRepository $productRepository,
        ReceiptRepository $receiptRepository,
        ReceiptValidator $validator
    ) {
        $this->em = $em;
        $this->productRepository = $productRepository;
        $this->receiptRepository = $receiptRepository;
        $this->validator = $validator;
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
        $url = $this->generateUrl('show_receipt', ['uuid' => $receipt->getUuid()]);
        $response->headers->set('Location', $url);

        return $response;
    }


    /**
     * @Route("/receipts/{uuid}", name="show_receipt", methods={"GET"})
     * @param string $uuid
     * @return Response
     */
    public function showReceipt(string $uuid)
    {
        $receipt = $this->receiptRepository->findOneBy(['uuid' => $uuid]);

        if (empty($receipt)) {
            throw $this->createNotFoundException('Could not find such receipt!');
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
    public function patchReceipt(string $uuid, Request $request)
    {
        $content = $this->getJSONContent($request);
        $this->validator->validatePATCHContent($content);

        if ($this->validator->isAddReceiptItemRequest($content)) {
            $this->validator->validateAddReceiptItemContent($content);
            $value = $content['value'];

            return $this->addReceiptItem($uuid, $value['barcode'], $value['quantity']);
        }

        if ($this->validator->isFinishReceiptRequest($content)) {
            return $this->finishReceipt($uuid);
        }

        if($this->validator->isChangeLastItemQuantityRequest($content)){
            $this->validator->validateChangeLastItemQuantityContent($content);
            return $this->changeLastItemQuantity($uuid, $content['value']);
        }


        throw new HttpException(400, 'Could not process the request!');
    }


    /**
     * @param string $uuid
     * @return JsonResponse
     */
    private function finishReceipt(string $uuid)
    {
        $receipt = $this->receiptRepository->findOneBy(['uuid' => $uuid]);

        if (empty($receipt)) {
            throw $this->createNotFoundException('Could not find such receipt!');
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
     * @param string $uuid
     * @param int $newQuantity
     * @return JsonResponse
     */
    private function changeLastItemQuantity(string $uuid, int $newQuantity)
    {
        $receipt = $this->receiptRepository->findOneBy(['uuid' => $uuid]);

        if (empty($receipt)) {
            throw $this->createNotFoundException('Could not find such receipt!');
        }

        $receiptItem = $receipt->getReceiptItems()->last(); /* @var ReceiptItem $receiptItem */

        if(empty($receiptItem)){
            throw $this->createNotFoundException('Could not find last receipt item! Maybe you didn\'t add any yet?');
        }

        $receiptItem->setQuantity($newQuantity);
        $receipt = $receiptItem->getReceipt();

        $this->em->persist($receiptItem);
        $this->em->persist($receipt); //it was recalculated too
        $this->em->flush();

        return new JsonResponse($receipt->toArray(), 200);
    }

}
