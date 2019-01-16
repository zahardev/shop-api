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

    public function testNewProduct()
    {
        $data = $this->getNewProductData();

        $response = $this->sendRequest('POST', '/products', json_encode($data));

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


    public function testListProducts()
    {
        $response = $this->sendRequest('GET', '/products');

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonContentType( $response );

        $products = $this->getResponseContent($response);
        $this->assertInternalType('array', $products);
        $this->assertArrayHasKey('products', $products);
        $this->assertCount(3, $products['products']);
        $this->assertAllProductPropertiesExist($products['products'][0]);
    }

    protected function sendRequest(string $method, string $uri, string $content = null, array $headers = [])
    {
        if( empty($this->adminToken) ){
            $this->adminToken = $this->generateToken('admin');
        }

        $headers['HTTP_Authorization'] = 'Bearer '.$this->adminToken;

        return parent::sendRequest($method, $uri, $content, $headers);
    }
}
