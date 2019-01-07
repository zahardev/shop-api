<?php


namespace App\Tests;

use App\Test\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CashRegisterTest
 * @package App\Tests
 */
class CashRegisterTest extends TestCase
{
    public function testGetProductByBarcode()
    {
        $dummy = $this->getDummyProductData();

        $response = $this->sendRequest('GET', '/products/'.$dummy['barcode']);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $this->assertJsonContentType($response);

        $productData = $this->getResponseContent($response);
        $this->assertInternalType('array', $productData);
        $this->assertAllProductPropertiesExist($productData);

        $this->assertEquals($dummy, $productData);
    }

    public function testNewReceipt()
    {
        $response = $this->sendRequest('POST', '/receipts');

        //Check response status and headers
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertJsonContentType($response);
        $location = $response->headers->get('Location');
        $this->assertNotEmpty($location);


        //Check response content
        $data = $this->getResponseContent($response);
        $this->assertInternalType('array', $data);
        $this->assertAllPropertiesExist($data, ['status', 'uuid', 'items']);
        $this->assertEquals('unfinished', $data['status']);
        $this->assertNotEmpty($data['uuid']);
        $this->assertEmpty($data['items']);
    }
}
