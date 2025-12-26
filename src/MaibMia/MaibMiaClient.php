<?php

namespace Maib\MaibMia;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Command\Guzzle\DescriptionInterface;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use GuzzleHttp\Command\Result;

/**
 * maib MIA API client
 * @link https://docs.maibmerchants.md/mia-qr-api
 */
class MaibMiaClient extends GuzzleClient
{
    public const DEFAULT_BASE_URL = 'https://api.maibmerchants.md/';
    public const SANDBOX_BASE_URL = 'https://sandbox.maibmerchants.md/';

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
     * @param string $clientId
     * @param string $clientSecret
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/authentication/obtain-authentication-token
     * @link https://docs.maibmerchants.md/mia-qr-api/en/overview/general-technical-specifications#authentication
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
     * @param array  $qrData
     * @param string $authToken
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-initiation/create-qr-code-static-dynamic
     */
    public function createQr($qrData, $authToken)
    {
        $args = $qrData;
        self::setBearerAuthToken($args, $authToken);
        return parent::createQr($args);
    }

    /**
     * Create Hybrid QR Code
     * @param array  $qrData
     * @param string $authToken
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-initiation/create-hybrid-qr-code
     */
    public function createHybridQr($qrData, $authToken)
    {
        $args = $qrData;
        self::setBearerAuthToken($args, $authToken);
        return parent::createHybridQr($args);
    }

    /**
     * Create Extension for QR Code by ID
     * @param string $qrId
     * @param array  $qrData
     * @param string $authToken
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-initiation/create-hybrid-qr-code/create-extension-for-qr-code-by-id
     */
    public function createQrExtension($qrId, $qrData, $authToken)
    {
        $args = $qrData;
        $args['qrId'] = $qrId;

        self::setBearerAuthToken($args, $authToken);
        return parent::createQrExtension($args);
    }

    /**
     * Cancel Active QR (Static, Dynamic)
     * @param string $qrId
     * @param string $reason
     * @param string $authToken
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-cancellation/cancel-active-qr-static-dynamic
     */
    public function cancelQr($qrId, $reason, $authToken)
    {
        $args = [
            'qrId' => $qrId,
            'reason' => $reason,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::cancelQr($args);
    }

    /**
     * Cancel Active QR Extension (Hybrid)
     * @param string $qrId
     * @param string $reason
     * @param string $authToken
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-cancellation/cancel-active-qr-extension-hybrid
     */
    public function cancelQrExtension($qrId, $reason, $authToken)
    {
        $args = [
            'qrId' => $qrId,
            'reason' => $reason,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::cancelQrExtension($args);
    }

    /**
     * Refund Completed Payment
     * @param string $payId
     * @param string $reason
     * @param string $authToken
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-refund/refund-completed-payment
     */
    public function paymentRefund($payId, $reason, $authToken)
    {
        $args = [
            'payId' => $payId,
            'reason' => $reason,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::paymentRefund($args);
    }

    /**
     * Display List of QR Codes with Filtering Options
     * @param array $qrListData
     * @param string $authToken
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/information-retrieval-get/display-list-of-qr-codes-with-filtering-options
     */
    public function qrList($qrListData, $authToken)
    {
        $args = $qrListData;
        self::setBearerAuthToken($args, $authToken);
        return parent::qrList($args);
    }

    /**
     * Retrieve QR Details by ID
     * @param string $qrId
     * @param string $authToken
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/information-retrieval-get/retrieve-qr-details-by-id
     */
    public function qrDetails($qrId, $authToken)
    {
        $args = [
            'qrId' => $qrId,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::qrDetails($args);
    }

    /**
     * Retrieve List of Payments with Filtering Options
     * @param array $paymentListData
     * @param string $authToken
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/information-retrieval-get/retrieve-list-of-payments-with-filtering-options
     */
    public function paymentList($paymentListData, $authToken)
    {
        $args = $paymentListData;
        self::setBearerAuthToken($args, $authToken);
        return parent::paymentList($args);
    }

    /**
     * Retrieve Payment Details by ID
     * @param string $payId
     * @param string $authToken
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/information-retrieval-get/retrieve-payment-details-by-id
     */
    public function paymentDetails($payId, $authToken)
    {
        $args = [
            'payId' => $payId,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::paymentDetails($args);
    }

    /**
     * Payment Simulation (Sandbox)
     * @param array $testPayData
     * @param string $authToken
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/mia-qr-api/en/payment-simulation-sandbox
     */
    public function testPay($testPayData, $authToken)
    {
        $args = $testPayData;
        self::setBearerAuthToken($args, $authToken);
        return parent::testPay($args);
    }

    /**
     * Create a new payment request (RTP)
     * @param array  $rtpData
     * @param string $authToken
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/endpoints/create-a-new-payment-request-rtp
     */
    public function rtpCreate($rtpData, $authToken)
    {
        $args = $rtpData;
        self::setBearerAuthToken($args, $authToken);
        return parent::rtpCreate($args);
    }

    /**
     * Retrieve the status of a payment request
     * @param string $rtpId
     * @param string $authToken
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/endpoints/retrieve-the-status-of-a-payment-request
     */
    public function rtpStatus($rtpId, $authToken)
    {
        $args = [
            'rtpId' => $rtpId,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::rtpStatus($args);
    }

    /**
     * Cancel a pending payment request
     * @param string $rtpId
     * @param string $reason
     * @param string $authToken
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/endpoints/cancel-a-pending-payment-request
     */
    public function rtpCancel($rtpId, $reason, $authToken)
    {
        $args = [
            'rtpId' => $rtpId,
            'reason' => $reason,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::rtpCancel($args);
    }

    /**
     * List all payment requests
     * @param array  $rtpListData
     * @param string $authToken
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/endpoints/list-all-payment-requests
     */
    public function rtpList($rtpListData, $authToken)
    {
        $args = $rtpListData;
        self::setBearerAuthToken($args, $authToken);
        return parent::rtpList($args);
    }

    /**
     * Initiate a refund for a completed payment
     * @param string $payId
     * @param string $reason
     * @param string $authToken
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/endpoints/initiate-a-refund-for-a-completed-payment
     */
    public function rtpRefund($payId, $reason, $authToken)
    {
        $args = [
            'payId' => $payId,
            'reason' => $reason,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::rtpRefund($args);
    }

    /**
     * Simulate acceptance of a payment request (Sandbox)
     * @param string $rtpId
     * @param array  $testAcceptData
     * @param string $authToken
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/sandbox-simulation-environment/simulate-acceptance-of-a-payment-request
     */
    public function rtpTestAccept($rtpId, $testAcceptData, $authToken)
    {
        $args = $testAcceptData;
        $args['rtpId'] = $rtpId;

        self::setBearerAuthToken($args, $authToken);
        return parent::rtpTestAccept($args);
    }

    /**
     * Simulate rejection of a payment request (Sandbox)
     * @param string $rtpId
     * @param string $authToken
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/sandbox-simulation-environment/simulate-rejection-of-a-payment-request
     */
    public function rtpTestReject($rtpId, $authToken)
    {
        $args = [
            'rtpId' => $rtpId,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::rtpTestReject($args);
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
     * @param array  $callbackData
     * @param string $signatureKey
     * @link https://docs.maibmerchants.md/mia-qr-api/en/examples/signature-key-verification
     */
    public static function validateCallbackSignature($callbackData, $signatureKey)
    {
        $resultData = $callbackData['result'] ?? [];
        $expectedSignature = $callbackData['signature'] ?? '';

        // Compare the result with the signature
        $computedResultSignature = self::computeDataSignature($resultData, $signatureKey);
        return hash_equals($expectedSignature, $computedResultSignature);
    }

    public static function computeDataSignature($resultData, $signatureKey)
    {
        $keys = [];
        foreach ($resultData as $key => $value) {
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

        return $result;
    }
}
