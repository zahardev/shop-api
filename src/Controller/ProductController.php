<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Utils\JsonHALResponse;
use App\Utils\Links;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends BaseController
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    public function __construct(Links $links, ProductRepository $productRepository)
    {
        parent::__construct($links);

        $this->productRepository = $productRepository;
    }


    /**
     * @Route("/products", methods={"POST"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     * @throws \Exception
     * @IsGranted("ROLE_ADMIN")
     */
    public function newProduct(Request $request, EntityManagerInterface $em)
    {
        $product = new Product();

        $form = $this->createForm(ProductType::class, $product);

        $this->processForm($request, $form);

        if (!$form->isValid()) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Invalid data sent');
        }

        $em->persist($product);

        $em->flush();

        $url = $this->generateUrl('show_product', ['barcode' => $product->getBarcode()]);

        $data = $this->serialize($product);

        $response = new JsonHALResponse($this->links->addLinks($data, $request->getPathInfo()), Response::HTTP_CREATED);
        $response->headers->set('Location', $url);

        return $response;
    }


    /**
     * @Route("/products/{barcode}", name="show_product", methods={"GET"})
     * @param string $barcode
     * @param Request $request
     * @return JsonResponse
     ** @IsGranted("ROLE_CASH_REGISTER")
     */
    public function showProduct($barcode, Request $request)
    {
        $product = $this->productRepository->findOneBy(['barcode' => $barcode]);

        $data = $this->serialize($product);

        return new JsonHALResponse($this->links->addLinks($data, $request->getPathInfo()));
    }


    /**
     * @Route("/products", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @return JsonHALResponse
     */
    public function listProducts(Request $request)
    {
        $products = $this->productRepository->findAll();

        $res = [];

        foreach ($products as $product) {
            $res[] = $this->serialize($product);
        }

        $data = ['products' => $res];

        $data = $this->links->addLinks($data, $request->getPathInfo());

        return new JsonHALResponse($data);
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
            'vatClass' => $product->getVatClass(),
        ];
    }

}
