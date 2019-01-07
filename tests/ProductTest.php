<?php


namespace App\Tests;

use App\Test\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ProductTest
 * @package App\Tests
 */
class ProductTest extends TestCase
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


    /**
     * This product exists in the test database
     *
     * @see fixtures/dummy.yaml
     */
    private function getDummyProductData()
    {
        return [
            'name' => 'Single Test',
            'barcode' => 1234567890123,
            'cost' => 278.75,
            'vatClass' => 21,
        ];
    }


    /**
     *
     * @param array $productData
     */
    private function assertAllProductPropertiesExist(array $productData)
    {
        foreach (['name', 'barcode', 'cost', 'vatClass'] as $key) {
            $this->assertArrayHasKey($key, $productData);
        }
    }
}
