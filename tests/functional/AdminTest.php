<?php


namespace App\Tests;

use App\Test\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ProductTest
 * @package App\Tests
 */
class AdminTest extends TestCase
{

    /**
     * Testing newAction()
     */
    public function testNewAction()
    {
        $data = $this->getNewProductData();

        $response = $this->sendRequest('POST', '/products', json_encode($data));

        //Debug errors
        if (!$response->isSuccessful()) {
            $block = self::$client->getCrawler()->filter('h1.exception-message');
            if ($block->count()) {
                $error = $block->text();
                echo $error;
            }
        }

        //Check response status and headers
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertJsonContentType($response);
        $location = $response->headers->get('Location');
        $this->assertNotEmpty($location);
        $this->assertEquals('/products/' . $data['barcode'], $location);


        //Check response content
        $productData = $this->getResponseContent($response);
        $this->assertInternalType('array', $productData);
        $this->assertEquals($data, $productData);
    }


    /**
     * Testing listAction()
     */
    public function testListAction()
    {
        $response = $this->sendRequest('GET', '/products');

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonContentType( $response );

        $products = $this->getResponseContent($response);
        $this->assertInternalType('array', $products);
        $this->assertArrayHasKey('products', $products);
        $this->assertCount(5, $products['products']);
        $this->assertAllProductPropertiesExist($products['products'][0]);
    }
}
