<?php


namespace App\Utils;


use Symfony\Component\HttpKernel\Exception\HttpException;

class ReceiptValidator
{

    /**
     * @param array $content
     * @return bool
     */
    public function isAddReceiptItemRequest(array $content)
    {
        return 'add' === $content['op'] && '/items' === $content['path'];
    }

    /**
     * @param array $content
     * @return bool
     */
    public function isFinishReceiptRequest(array $content)
    {
        return 'replace' === $content['op'] && '/status' === $content['path'] && 'finished' === $content['value'];
    }


    /**
     * @param array $content
     * @return bool
     */
    public function isChangeLastItemQuantityRequest(array $content)
    {
        return 'replace' === $content['op'] && '/items/last/quantity' === $content['path'];
    }

    /**
     * @param $content
     * @throws \Exception
     */
    public function validateAddReceiptItemContent($content)
    {
        foreach (['barcode', 'quantity'] as $key) {
            if (!array_key_exists($key, $content['value'])) {
                throw new HttpException(400, sprintf('Property value should contain %s key!', $key));
            }
        }
    }

    /**
     * @param $content
     * @throws \Exception
     */
    public function validateChangeLastItemQuantityContent($content)
    {
        if (!is_integer($content['value'])) {
            throw new HttpException(400, sprintf('Quantity value should be an integer! Got: %s', $content['value']));
        }
    }


    /**
     * @param array $content
     * @throws \Exception
     */
    public function validatePATCHContent(array $content)
    {
        foreach (['op', 'path', 'value'] as $key) {
            if (!array_key_exists($key, $content)) {
                throw new HttpException(400, sprintf('Request JSON should contain %s key!', $key));
            }
        }

        $allowedMap = [
            'op' => ['add', 'replace'],
            'path' => ['/items', '/items/last/quantity', '/status'],
        ];

        foreach ($allowedMap as $property => $allowedValues) {
            if (!in_array($content[$property], $allowedValues)) {
                throw new HttpException(
                    400,
                    sprintf(
                        'Property %s can have only such value(s): %s',
                        $property,
                        implode(', ', $allowedValues)
                    )
                );
            }
        }
    }
}
