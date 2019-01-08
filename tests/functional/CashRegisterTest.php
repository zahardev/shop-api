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

    public function testAddReceiptItem()
    {
        $receiptData = $this->getDummyUnfinishedReceiptData();
        $productsData = $this->getDummyProductsData();

        $count = 0;
        $quantity = 3;
        $expectedItemCalculations = $this->getExpectedItemCalculations();
        $expectedReceiptCalculations = $this->getExpectedReceiptCalculations();

        foreach ($productsData as $productData) {
            $count++;
            $request = [
                'op' => 'add',
                'path' => '/items',
                'value' => [
                    'barcode' => $productData['barcode'],
                    'quantity' => $quantity,
                ],
            ];
            $response = $this->sendRequest(
                'PATCH',
                '/receipts/'.$receiptData['uuid'],
                json_encode($request)
            );

            //Check response status and headers
            $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
            $this->assertJsonContentType($response);

            //Check response content
            $expectedReceiptProperties = [
                'status' => 'unfinished',
                'uuid' => $receiptData['uuid'],
                'total' => $expectedReceiptCalculations[$count]['total'],
                'totalVat' => $expectedReceiptCalculations[$count]['totalVat'],
                'totalWithVat' => $expectedReceiptCalculations[$count]['totalWithVat'],
                'total21' => $expectedReceiptCalculations[$count]['total21'],
                'totalVat21' => $expectedReceiptCalculations[$count]['totalVat21'],
                'totalWithVat21' => $expectedReceiptCalculations[$count]['totalWithVat21'],
                'total6' => $expectedReceiptCalculations[$count]['total6'],
                'totalVat6' => $expectedReceiptCalculations[$count]['totalVat6'],
                'totalWithVat6' => $expectedReceiptCalculations[$count]['totalWithVat6'],
            ];
            $data = $this->getResponseContent($response);
            $this->assertInternalType('array', $data);

            $this->assertNotEmpty($data['items']);
            $this->assertCount($count, $data['items']);

            foreach ($expectedReceiptProperties as $property => $expected) {
                $this->assertArrayHasKey($property, $data);
                $this->assertEquals($expected, $data[$property], sprintf('Receipt property %s problem.', $property));
            }

            $expectedItemProperties = [
                'name' => $productData['name'],
                'barcode' => $productData['barcode'],
                'cost' => $productData['cost'],
                'vatClass' => $productData['vatClass'],
                'quantity' => $quantity,
                'costWithVat' => $expectedItemCalculations[$count]['costWithVat'],
                'vat' => $expectedItemCalculations[$count]['vat'],
                'total' => $expectedItemCalculations[$count]['total'],
                'totalVat' => $expectedItemCalculations[$count]['totalVat'],
                'totalWithVat' => $expectedItemCalculations[$count]['totalWithVat'],
            ];

            foreach ($expectedItemProperties as $property => $expected) {
                $item = $data['items'][$count-1];
                $this->assertArrayHasKey($property, $item);
                $this->assertEquals($expected, $item[$property], sprintf('Item property %s problem.', $property));
            }
        }
    }


    private function getExpectedReceiptCalculations()
    {
        return [
            1 => [
                'total' => 33.33,
                'totalVat' => 7.00,
                'totalWithVat' => 40.33,
                'total21' => 33.33,
                'totalVat21' => 7.00,
                'totalWithVat21' => 40.33,
                'total6' => 0,
                'totalVat6' => 0,
                'totalWithVat6' => 0,
            ],
            2 => [
                'total' => 99.99,
                'totalVat' => 21.00,
                'totalWithVat' => 120.99,
                'total21' => 99.99,
                'totalVat21' => 21.00,
                'totalWithVat21' => 120.99,
                'total6' => 0,
                'totalVat6' => 0,
                'totalWithVat6' => 0,
            ],
            3 => [
                'total' => 199.98,
                'totalVat' => 27.00,
                'totalWithVat' => 226.98,
                'total21' => 99.99,
                'totalVat21' => 21.00,
                'totalWithVat21' => 120.99,
                'total6' => 99.99,
                'totalVat6' => 6.00,
                'totalWithVat6' => 105.99,
            ],

        ];
    }


    private function getExpectedItemCalculations()
    {
        return [
            1 => [
                'vat' => 2.33,
                'costWithVat' => 13.44,
                'total' => 33.33,
                'totalVat' => 7.00,
                'totalWithVat' => 40.33,
            ],
            2 => [
                'vat' => 4.67,
                'costWithVat' => 26.89,
                'total' => 66.66,
                'totalVat' => 14.00,
                'totalWithVat' => 80.66,
            ],
            3 => [
                'vat' => 2.00,
                'costWithVat' => 35.33,
                'total' => 99.99,
                'totalVat' => 6.00,
                'totalWithVat' => 105.99,
            ],
        ];
    }
}
