<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends BaseController
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {

        $this->productRepository = $productRepository;
    }


    /**
     * @Route("/products/{barcode}", name="show_product", methods={"GET"})
     * @param string $barcode
     * @return JsonResponse
     */
    public function showAction($barcode)
    {
        $product = $this->productRepository->findOneBy(['barcode' => $barcode]);

        return new JsonResponse($this->serialize($product));
    }


    /**
     * @param Product $product
     * @return array
     */
    protected function serialize(Product $product)
    {
        return [
            'name' => $product->getName(),
            'barcode' => $product->getBarcode(),
            'cost' => $product->getCost(),
            'vatClass' => $product->getVatClass()->getPercent(),
        ];
    }

}
