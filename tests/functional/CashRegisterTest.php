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
        $receiptData = $this->getDummyReceiptData()['receipt_empty'];
        $productsData = $this->getDummyProductsData();

        $count = 0;
        $quantity = 3;

        foreach ($productsData as $productData) {
            $count++;

            $request = [
                'barcode' => $productData['barcode'],
                'quantity' => $quantity,
            ];
            $response = $this->sendPatchRequest('/receipts/'.$receiptData['uuid'], 'add', '/items', $request);

            //Check response status and headers
            $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
            $this->assertJsonContentType($response);

            //Check response data
            $data = $this->getResponseContent($response);
            $this->assertInternalType('array', $data);
            $this->checkReceiptResponseData($data, $receiptData, $count);
            $this->checkReceiptResponseItemData($data, $productData, $count);
        }
    }


    public function testAddReceiptWithWrongRequest()
    {
        $receiptData = $this->getDummyReceiptData()['receipt_empty'];
        $productData = $this->getDummyProductsData()[0];

        $request = [
            'barcode' => $productData['barcode'],
            'quantity' => 3,
        ];
        $response = $this->sendPatchRequest('/receipts/'.$receiptData['uuid'], 'adds', '/items', $request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST ,$response->getStatusCode());
        $contentType = $response->headers->get('content-type');
        $this->assertNotEmpty($contentType);
        $this->assertEquals('application/problem+json', $contentType);

        $data = $this->getResponseContent($response);
        $this->assertInternalType('array', $data);

        $this->assertAllPropertiesExist($data, ['status', 'type', 'title', 'detail']);
    }


    public function testGetReceipt()
    {
        $receiptData = $this->getDummyReceiptData()['receipt_with_items'];
        $response = $this->sendRequest('GET', '/receipts/'.$receiptData['uuid']);

        $data = $this->getResponseContent($response);
        $this->checkReceiptResponseData($data, $receiptData, 3); //all 3 iterations were inserted in the dummy.yaml
    }

    public function testFinishReceipt()
    {
        $receiptData = $this->getDummyReceiptData()['receipt_with_items'];
        $response = $this->sendPatchRequest('/receipts/'.$receiptData['uuid'], 'replace', '/status', 'finished');

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonContentType($response);

        $data = $this->getResponseContent($response);
        $receiptData['status'] = 'finished';
        $this->checkReceiptResponseData($data, $receiptData, 3);
    }


    private function checkReceiptResponseItemData(array $data, array $productData, int $iteration)
    {
        $quantity = 3;
        $expectedItemCalculations = $this->getExpectedItemCalculations();
        $receiptItem = $data['items'][$iteration - 1];

        $expectedItemProperties = [
            'name' => $productData['name'],
            'barcode' => $productData['barcode'],
            'cost' => $productData['cost'],
            'vatClass' => $productData['vatClass'],
            'quantity' => $quantity,
            'costWithVat' => $expectedItemCalculations[$iteration]['costWithVat'],
            'vat' => $expectedItemCalculations[$iteration]['vat'],
            'total' => $expectedItemCalculations[$iteration]['total'],
            'totalVat' => $expectedItemCalculations[$iteration]['totalVat'],
            'totalWithVat' => $expectedItemCalculations[$iteration]['totalWithVat'],
        ];

        foreach ($expectedItemProperties as $property => $expected) {
            $this->assertArrayHasKey($property, $receiptItem);
            $this->assertEquals($expected, $receiptItem[$property], sprintf('Item property %s problem.', $property));
        }
    }


    private function checkReceiptResponseData(array $data, array $receiptData, int $iteration)
    {
        $expectedReceiptCalculations = $this->getExpectedReceiptCalculations();

        //Check response content
        $expectedReceiptProperties = [
            'status' => $receiptData['status'],
            'uuid' => $receiptData['uuid'],
            'total' => $expectedReceiptCalculations[$iteration]['total'],
            'totalVat' => $expectedReceiptCalculations[$iteration]['totalVat'],
            'totalWithVat' => $expectedReceiptCalculations[$iteration]['totalWithVat'],
            'total21' => $expectedReceiptCalculations[$iteration]['total21'],
            'totalVat21' => $expectedReceiptCalculations[$iteration]['totalVat21'],
            'totalWithVat21' => $expectedReceiptCalculations[$iteration]['totalWithVat21'],
            'total6' => $expectedReceiptCalculations[$iteration]['total6'],
            'totalVat6' => $expectedReceiptCalculations[$iteration]['totalVat6'],
            'totalWithVat6' => $expectedReceiptCalculations[$iteration]['totalWithVat6'],
        ];

        $this->assertNotEmpty($data['items']);
        $this->assertCount($iteration, $data['items']);

        foreach ($expectedReceiptProperties as $property => $expected) {
            $this->assertArrayHasKey($property, $data);
            $this->assertEquals($expected, $data[$property], sprintf('Receipt property %s problem.', $property));
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
