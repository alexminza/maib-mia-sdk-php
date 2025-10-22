<?php

namespace Maib\MaibMia;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Command\Guzzle\DescriptionInterface;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use GuzzleHttp\Command\Result;
use GuzzleHttp\Exception\BadResponseException;

class MaibMiaClient extends GuzzleClient
{
    const DEFAULT_BASE_URL = 'https://api.maibmerchants.md/';
    const SANDBOX_BASE_URL = 'https://sandbox.maibmerchants.md/';

    /**
     * @param ClientInterface      $client
     * @param DescriptionInterface $description
     * @param array                $config
     */
    public function __construct(
        ?ClientInterface $client = null,
        ?DescriptionInterface $description = null,
        array $config = []
    ) {
        $client = $client instanceof ClientInterface ? $client : new Client();
        $description = $description instanceof DescriptionInterface ? $description : new MaibMiaDescription($config);
        parent::__construct($client, $description, null, null, null, $config);
    }

    /**
     * Obtain Authentication Token
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/authentication/obtain-authentication-token
     */
    public function getToken($clientId, $clientSecret)
    {
        $args = [
            'clientId' => $clientId,
            'clientSecret' => $clientSecret
        ];

        return parent::getToken($args);
    }

    /**
     * Create QR Code (Static, Dynamic)
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-initiation/create-qr-code-static-dynamic
     * @param array  $qrData
     * @param string $authToken
     */
    public function createQr($qrData, $authToken)
    {
        self::setBearerAuthToken($qrData, $authToken);
        return parent::createQr($qrData);
    }

    /**
     * Refund Completed Payment
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-refund/refund-completed-payment
     * @param array @refundData
     * @param string $authToken
     */
    public function paymentRefund($refundData, $authToken)
    {
        self::setBearerAuthToken($refundData, $authToken);
        return parent::paymentRefund($refundData);
    }

    /**
     * @param array  $args
     * @param string $authToken
     */
    private static function setBearerAuthToken(&$args, $authToken)
    {
        $args['authToken'] = "Bearer $authToken";
    }

    /**
     * Callback Payload Signature Key Verification
     * @link https://docs.maibmerchants.md/mia-qr-api/en/examples/signature-key-verification
     * @param array  $callbackData
     * @param string $signatureKey
     */
    public static function validateCallbackSignature($callbackData, $signatureKey)
    {
        $resultElement = $callbackData['result'] ?? [];
        $expectedSignature = $callbackData['signature'] ?? '';

        $keys = [];
        foreach ($resultElement as $key => $value) {
            if (is_null($value)) {
                continue;
            }

            // Format "amount" and "commission" with 2 decimal places
            if ($key === 'amount' || $key === 'commission') {
                $valueStr = number_format((float)$value, 2, '.', '');
            } else {
                $valueStr = (string)$value;
            }

            if (trim($valueStr) !== '') {
                $keys[$key] = $valueStr;
            }
        }

        // Sort keys by key name (case-insensitive)
        uksort($keys, 'strcasecmp');

        // Build the string to hash
        $additionalString = implode(':', $keys);
        $hashInput = $additionalString . ':' . $signatureKey;

        // Generate SHA256 hash and base64-encode it
        $hash = hash('sha256', $hashInput, true);
        $result = base64_encode($hash);

        // Compare the result with the signature
        return hash_equals($expectedSignature, $result);
    }
}
