<?php

declare(strict_types=1);

namespace Maib\MaibMia;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Command\Guzzle\DescriptionInterface;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use GuzzleHttp\Command\Result;

/**
 * maib MIA API client
 *
 * @link https://docs.maibmerchants.md/mia-qr-api
 * @link https://docs.maibmerchants.md/request-to-pay
 */
class MaibMiaClient extends GuzzleClient
{
    public const DEFAULT_BASE_URL = 'https://api.maibmerchants.md/';
    public const SANDBOX_BASE_URL = 'https://sandbox.maibmerchants.md/';

    public function __construct(?ClientInterface $client = null, ?DescriptionInterface $description = null, array $config = [])
    {
        $client      = $client ?? new Client();
        $description = $description ?? new MaibMiaDescription($config);

        parent::__construct($client, $description, null, null, null, $config);
    }

    #region Auth
    /**
     * Obtain Authentication Token
     *
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/authentication/obtain-authentication-token
     * @link https://docs.maibmerchants.md/mia-qr-api/en/overview/general-technical-specifications#authentication
     */
    public function getToken(string $clientId, string $clientSecret): Result
    {
        $getTokenData = [
            'clientId' => $clientId,
            'clientSecret' => $clientSecret
        ];

        return parent::getToken($getTokenData);
    }
    #endregion

    #region QR
    /**
     * Create QR Code (Static, Dynamic)
     *
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-initiation/create-qr-code-static-dynamic
     */
    public function qrCreate(array $qrData, string $authToken): Result
    {
        self::setBearerAuthToken($qrData, $authToken);
        return parent::qrCreate($qrData);
    }

    /**
     * Create Hybrid QR Code
     *
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-initiation/create-hybrid-qr-code
     */
    public function qrCreateHybrid(array $qrData, string $authToken): Result
    {
        self::setBearerAuthToken($qrData, $authToken);
        return parent::qrCreateHybrid($qrData);
    }

    /**
     * Create Extension for QR Code by ID
     *
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-initiation/create-hybrid-qr-code/create-extension-for-qr-code-by-id
     */
    public function qrCreateExtension(string $qrId, array $qrData, string $authToken): Result
    {
        $qrData['qrId'] = $qrId;

        self::setBearerAuthToken($qrData, $authToken);
        return parent::qrCreateExtension($qrData);
    }

    /**
     * Retrieve QR Details by ID
     *
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/information-retrieval-get/retrieve-qr-details-by-id
     */
    public function qrDetails(string $qrId, string $authToken): Result
    {
        $qrDetailsData = [
            'qrId' => $qrId,
        ];

        self::setBearerAuthToken($qrDetailsData, $authToken);
        return parent::qrDetails($qrDetailsData);
    }

    /**
     * Cancel Active QR (Static, Dynamic)
     *
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-cancellation/cancel-active-qr-static-dynamic
     */
    public function qrCancel(string $qrId, array $cancelData, string $authToken): Result
    {
        $cancelData['qrId'] = $qrId;

        self::setBearerAuthToken($cancelData, $authToken);
        return parent::qrCancel($cancelData);
    }

    /**
     * Cancel Active QR Extension (Hybrid)
     *
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-cancellation/cancel-active-qr-extension-hybrid
     */
    public function qrCancelExtension(string $qrId, array $cancelData, string $authToken): Result
    {
        $cancelData['qrId'] = $qrId;

        self::setBearerAuthToken($cancelData, $authToken);
        return parent::qrCancelExtension($cancelData);
    }

    /**
     * Display List of QR Codes with Filtering Options
     *
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/information-retrieval-get/display-list-of-qr-codes-with-filtering-options
     */
    public function qrList(array $qrListData, string $authToken): Result
    {
        self::setBearerAuthToken($qrListData, $authToken);
        return parent::qrList($qrListData);
    }
    #endregion

    #region Payment
    /**
     * Payment Simulation (Sandbox)
     *
     * @link https://docs.maibmerchants.md/mia-qr-api/en/payment-simulation-sandbox
     */
    public function testPay(array $testPayData, string $authToken): Result
    {
        self::setBearerAuthToken($testPayData, $authToken);
        return parent::testPay($testPayData);
    }

    /**
     * Retrieve Payment Details by ID
     *
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/information-retrieval-get/retrieve-payment-details-by-id
     */
    public function paymentDetails(string $payId, string $authToken): Result
    {
        $paymentDetailsData = [
            'payId' => $payId,
        ];

        self::setBearerAuthToken($paymentDetailsData, $authToken);
        return parent::paymentDetails($paymentDetailsData);
    }

    /**
     * Refund Completed Payment
     *
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-refund/refund-completed-payment
     */
    public function paymentRefund(string $payId, array $refundData, string $authToken): Result
    {
        $refundData['payId'] = $payId;

        self::setBearerAuthToken($refundData, $authToken);
        return parent::paymentRefund($refundData);
    }

    /**
     * Retrieve List of Payments with Filtering Options
     *
     * @link https://docs.maibmerchants.md/mia-qr-api/en/endpoints/information-retrieval-get/retrieve-list-of-payments-with-filtering-options
     */
    public function paymentList(array $paymentListData, string $authToken): Result
    {
        self::setBearerAuthToken($paymentListData, $authToken);
        return parent::paymentList($paymentListData);
    }
    #endregion

    #region RTP
    /**
     * Create a new payment request (RTP)
     *
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/endpoints/create-a-new-payment-request-rtp
     */
    public function rtpCreate(array $rtpData, string $authToken): Result
    {
        self::setBearerAuthToken($rtpData, $authToken);
        return parent::rtpCreate($rtpData);
    }

    /**
     * Retrieve the status of a payment request
     *
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/endpoints/retrieve-the-status-of-a-payment-request
     */
    public function rtpStatus(string $rtpId, string $authToken): Result
    {
        $rtpStatusData = [
            'rtpId' => $rtpId,
        ];

        self::setBearerAuthToken($rtpStatusData, $authToken);
        return parent::rtpStatus($rtpStatusData);
    }

    /**
     * Cancel a pending payment request
     *
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/endpoints/cancel-a-pending-payment-request
     */
    public function rtpCancel(string $rtpId, array $cancelData, string $authToken): Result
    {
        $cancelData['rtpId'] = $rtpId;

        self::setBearerAuthToken($cancelData, $authToken);
        return parent::rtpCancel($cancelData);
    }

    /**
     * List all payment requests
     *
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/endpoints/list-all-payment-requests
     */
    public function rtpList(array $rtpListData, string $authToken): Result
    {
        self::setBearerAuthToken($rtpListData, $authToken);
        return parent::rtpList($rtpListData);
    }

    /**
     * Initiate a refund for a completed payment
     *
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/endpoints/initiate-a-refund-for-a-completed-payment
     */
    public function rtpRefund(string $payId, array $refundData, string $authToken): Result
    {
        $refundData['payId'] = $payId;

        self::setBearerAuthToken($refundData, $authToken);
        return parent::rtpRefund($refundData);
    }

    /**
     * Simulate acceptance of a payment request (Sandbox)
     *
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/sandbox-simulation-environment/simulate-acceptance-of-a-payment-request
     */
    public function rtpTestAccept(string $rtpId, array $testAcceptData, string $authToken): Result
    {
        $testAcceptData['rtpId'] = $rtpId;

        self::setBearerAuthToken($testAcceptData, $authToken);
        return parent::rtpTestAccept($testAcceptData);
    }

    /**
     * Simulate rejection of a payment request (Sandbox)
     *
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/sandbox-simulation-environment/simulate-rejection-of-a-payment-request
     */
    public function rtpTestReject(string $rtpId, string $authToken): Result
    {
        $testRejectData = [
            'rtpId' => $rtpId,
        ];

        self::setBearerAuthToken($testRejectData, $authToken);
        return parent::rtpTestReject($testRejectData);
    }
    #endregion

    #region Signature
    /**
     * Callback Payload Signature Key Verification
     *
     * @param array $callbackData  Callback message parsed JSON object
     * @param string $signatureKey Merchant's shared secret key
     *
     * @return bool True if signature is valid, false otherwise
     *
     * @link https://docs.maibmerchants.md/mia-qr-api/en/notifications-on-callback-url
     * @link https://docs.maibmerchants.md/mia-qr-api/en/examples/signature-key-verification
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/callback-notifications
     * @link https://docs.maibmerchants.md/request-to-pay/api-reference/examples/signature-key-verification
     */
    public static function validateCallbackSignature(array $callbackData, string $signatureKey): bool
    {
        $resultData        = $callbackData['result'] ?? [];
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
     *
     * @param array  $resultData The result data from callback payload
     * @param string $signatureKey Merchant's shared secret key
     *
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
                $valueStr = number_format(floatval($value), 2, '.', '');
            } else {
                $valueStr = strval($value);
            }

            if (trim($valueStr) !== '') {
                $keys[$key] = $valueStr;
            }
        }

        // Sort keys by key name (case-insensitive)
        uksort($keys, 'strcasecmp');

        // Build the string to hash
        $additionalString = implode(':', $keys);
        $hashInput        = $additionalString . ':' . $signatureKey;

        // Generate SHA256 hash and base64-encode it
        $hash   = hash('sha256', $hashInput, true);
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
