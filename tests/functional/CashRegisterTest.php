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

    /**
     * Testing showAction()
     *
     * @see fixtures/dummy.yaml
     */
    public function testShowAction()
    {
        $dummy = $this->getDummyProductData();

        $response = $this->sendRequest('GET', '/products/'.$dummy['barcode']);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $this->assertJsonContentType($response);

        $productData = $this->getResponseContent($response);

        $this->assertInternalType('array', $productData);

        $this->assertAllProductPropertiesExist($productData);
    }
}
