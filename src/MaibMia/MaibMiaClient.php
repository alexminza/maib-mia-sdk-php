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

    public function __construct(?ClientInterface $client = null, ?DescriptionInterface $description = null, array $config = [])
    {
        $client = $client ?? new Client();
        $description = $description ?? new MaibMiaDescription($config);
        parent::__construct($client, $description, null, null, null, $config);
    }

    #region Auth
    /**
     * Obtain Authentication Token
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/authentication/obtain-authentication-token
     * @link https://docs.maibmerchants.md/mia-qr-api/en/overview/general-technical-specifications#authentication
     */
    public function getToken(string $clientId, string $clientSecret): Result
    {
        $args = [
            'clientId' => $clientId,
            'clientSecret' => $clientSecret
        ];

        return parent::getToken($args);
    }
    #endregion

    #region QR
    /**
     * Create QR Code (Static, Dynamic)
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-initiation/create-qr-code-static-dynamic
     */
    public function qrCreate(array $qrData, string $authToken): Result
    {
        $args = $qrData;
        self::setBearerAuthToken($args, $authToken);
        return parent::qrCreate($args);
    }

    /**
     * Create Hybrid QR Code
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-initiation/create-hybrid-qr-code
     */
    public function qrCreateHybrid(array $qrData, string $authToken): Result
    {
        $args = $qrData;
        self::setBearerAuthToken($args, $authToken);
        return parent::qrCreateHybrid($args);
    }

    /**
     * Create Extension for QR Code by ID
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-initiation/create-hybrid-qr-code/create-extension-for-qr-code-by-id
     */
    public function qrCreateExtension(string $qrId, array $qrData, string $authToken): Result
    {
        $args = $qrData;
        $args['qrId'] = $qrId;

        self::setBearerAuthToken($args, $authToken);
        return parent::qrCreateExtension($args);
    }

    /**
     * Retrieve QR Details by ID
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/information-retrieval-get/retrieve-qr-details-by-id
     */
    public function qrDetails(string $qrId, string $authToken): Result
    {
        $args = [
            'qrId' => $qrId,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::qrDetails($args);
    }

    /**
     * Cancel Active QR (Static, Dynamic)
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-cancellation/cancel-active-qr-static-dynamic
     */
    public function qrCancel(string $qrId, array $cancelData, string $authToken): Result
    {
        $args = $cancelData;
        $args['qrId'] = $qrId;

        self::setBearerAuthToken($args, $authToken);
        return parent::qrCancel($args);
    }

    /**
     * Cancel Active QR Extension (Hybrid)
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-cancellation/cancel-active-qr-extension-hybrid
     */
    public function qrCancelExtension(string $qrId, array $cancelData, string $authToken): Result
    {
        $args = $cancelData;
        $args['qrId'] = $qrId;

        self::setBearerAuthToken($args, $authToken);
        return parent::qrCancelExtension($args);
    }

    /**
     * Display List of QR Codes with Filtering Options
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/information-retrieval-get/display-list-of-qr-codes-with-filtering-options
     */
    public function qrList(array $qrListData, string $authToken): Result
    {
        $args = $qrListData;
        self::setBearerAuthToken($args, $authToken);
        return parent::qrList($args);
    }
    #endregion

    #region Payment
    /**
     * Payment Simulation (Sandbox)
     * @link https://docs.maibmerchants.md/mia-qr-api/en/payment-simulation-sandbox
     */
    public function testPay(array $testPayData, string $authToken): Result
    {
        $args = $testPayData;

        self::setBearerAuthToken($args, $authToken);
        return parent::testPay($args);
    }

    /**
     * Retrieve Payment Details by ID
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/information-retrieval-get/retrieve-payment-details-by-id
     */
    public function paymentDetails(string $payId, string $authToken): Result
    {
        $args = [
            'payId' => $payId,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::paymentDetails($args);
    }

    /**
     * Refund Completed Payment
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-refund/refund-completed-payment
     */
    public function paymentRefund(string $payId, array $refundData, string $authToken): Result
    {
        $args = $refundData;
        $args['payId'] = $payId;

        self::setBearerAuthToken($args, $authToken);
        return parent::paymentRefund($args);
    }

    /**
     * Retrieve List of Payments with Filtering Options
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/information-retrieval-get/retrieve-list-of-payments-with-filtering-options
     */
    public function paymentList(array $paymentListData, string $authToken): Result
    {
        $args = $paymentListData;
        self::setBearerAuthToken($args, $authToken);
        return parent::paymentList($args);
    }
    #endregion

    #region RTP
    /**
     * Create a new payment request (RTP)
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/endpoints/create-a-new-payment-request-rtp
     */
    public function rtpCreate(array $rtpData, string $authToken): Result
    {
        $args = $rtpData;
        self::setBearerAuthToken($args, $authToken);
        return parent::rtpCreate($args);
    }

    /**
     * Retrieve the status of a payment request
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/endpoints/retrieve-the-status-of-a-payment-request
     */
    public function rtpStatus(string $rtpId, string $authToken): Result
    {
        $args = [
            'rtpId' => $rtpId,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::rtpStatus($args);
    }

    /**
     * Cancel a pending payment request
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/endpoints/cancel-a-pending-payment-request
     */
    public function rtpCancel(string $rtpId, array $cancelData, string $authToken): Result
    {
        $args = $cancelData;
        $args['rtpId'] = $rtpId;

        self::setBearerAuthToken($args, $authToken);
        return parent::rtpCancel($args);
    }

    /**
     * List all payment requests
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/endpoints/list-all-payment-requests
     */
    public function rtpList(array $rtpListData, string $authToken): Result
    {
        $args = $rtpListData;
        self::setBearerAuthToken($args, $authToken);
        return parent::rtpList($args);
    }

    /**
     * Initiate a refund for a completed payment
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/endpoints/initiate-a-refund-for-a-completed-payment
     */
    public function rtpRefund(string $payId, array $refundData, string $authToken): Result
    {
        $args = $refundData;
        $args['payId'] = $payId;

        self::setBearerAuthToken($args, $authToken);
        return parent::rtpRefund($args);
    }

    /**
     * Simulate acceptance of a payment request (Sandbox)
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/sandbox-simulation-environment/simulate-acceptance-of-a-payment-request
     */
    public function rtpTestAccept(string $rtpId, array $testAcceptData, string $authToken): Result
    {
        $args = $testAcceptData;
        $args['rtpId'] = $rtpId;

        self::setBearerAuthToken($args, $authToken);
        return parent::rtpTestAccept($args);
    }

    /**
     * Simulate rejection of a payment request (Sandbox)
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/sandbox-simulation-environment/simulate-rejection-of-a-payment-request
     */
    public function rtpTestReject(string $rtpId, string $authToken): Result
    {
        $args = [
            'rtpId' => $rtpId,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::rtpTestReject($args);
    }
    #endregion

    #region Signature
    /**
     * Callback Payload Signature Key Verification
     * @param array $callbackData  Callback message parsed JSON object
     * @param string $signatureKey Merchant's shared secret key
     * @return bool True if signature is valid, false otherwise
     * @link https://docs.maibmerchants.md/mia-qr-api/en/notifications-on-callback-url
     * @link https://docs.maibmerchants.md/mia-qr-api/en/examples/signature-key-verification
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/callback-notifications
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/examples/signature-key-verification
     */
    public static function validateCallbackSignature(array $callbackData, string $signatureKey): bool
    {
        $resultData = $callbackData['result'] ?? [];
        $callbackSignature = $callbackData['signature'] ?? '';

        // Validate required data exists
        if (empty($resultData) || empty($callbackSignature)) {
            return false;
        }

        // Compare the result with the signature
        $computedSignature = self::computeDataSignature($resultData, $signatureKey);
        return hash_equals($computedSignature, $callbackSignature);
    }

    /**
     * Compute Payload Signature
     * @param array  $resultData The result data from callback payload
     * @param string $signatureKey Merchant's shared secret key
     * @link https://docs.maibmerchants.md/mia-qr-api/en/notifications-on-callback-url
     * @link https://docs.maibmerchants.md/mia-qr-api/en/examples/signature-key-verification
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/callback-notifications
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/examples/signature-key-verification
     */
    public static function computeDataSignature(array $resultData, string $signatureKey): string
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
    #endregion

    #region Util
    private static function setBearerAuthToken(array &$args, string $authToken)
    {
        $args['authToken'] = "Bearer $authToken";
    }
    #endregion
}
