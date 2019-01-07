<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Receipt;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReceiptController extends BaseController
{

    /**
     * @Route("/receipts", methods={"POST"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     * @throws \Exception
     */
    public function newReceipt(Request $request, EntityManagerInterface $em)
    {
        $receipt = new Receipt();

        $em->persist($receipt);

        $em->flush();

        $response = new JsonResponse($this->serialize($receipt), Response::HTTP_CREATED);
        $response->headers->set('Location', 'new_receipt_location'); //todo

        return $response;
    }


    /**
     * @param Receipt $receipt
     * @return array
     */
    protected function serialize(Receipt $receipt)
    {
        return [
            'status' => $receipt->getStatus(),
            'uuid' => $receipt->getUuid(),
            'items' => $receipt->getReceiptItems(),
        ];
    }

}
