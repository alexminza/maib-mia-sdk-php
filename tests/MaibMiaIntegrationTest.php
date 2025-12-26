<?php

namespace Maib\MaibMia\Tests;

use Maib\MaibMia\MaibMiaClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

/**
 * @group integration
 */
class MaibMiaIntegrationTest extends TestCase
{
    protected static $clientId;
    protected static $clientSecret;
    protected static $signatureKey;
    protected static $baseUrl;

    /**
     * @var MaibMiaClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $expiresAt;

    public static function setUpBeforeClass(): void
    {
        self::$clientId = getenv('MAIB_MIA_CLIENT_ID');
        self::$clientSecret = getenv('MAIB_MIA_CLIENT_SECRET');
        self::$signatureKey = getenv('MAIB_MIA_SIGNATURE_KEY');
        self::$baseUrl = MaibMiaClient::SANDBOX_BASE_URL;

        if (!self::$clientId || !self::$clientSecret || !self::$signatureKey) {
            self::markTestSkipped('Integration test credentials not provided.');
        }
    }

    protected function setUp(): void
    {
        $this->client = new MaibMiaClient(new Client(['base_uri' => self::$baseUrl]));
        $this->expiresAt = (new \DateTime())->modify('+1 hour')->format('c');
    }

    protected function debugLog($message, $data)
    {
        $data_print = print_r($data, true);
        error_log("$message: $data_print");
    }

    public function testAuthenticate()
    {
        $response = $this->client->getToken(self::$clientId, self::$clientSecret);
        $this->debugLog('getToken', $response);

        $this->assertArrayHasKey('accessToken', $response['result']);
        $this->assertNotEmpty($response['result']['accessToken']);

        return $response['result']['accessToken'];
    }

    /**
     * @depends testAuthenticate
     */
    public function testCreateDynamicQr($accessToken)
    {
        $qrData = [
            'type' => 'Dynamic',
            'expiresAt' => $this->expiresAt,
            'amountType' => 'Fixed',
            'amount' => 50.00,
            'currency' => 'MDL',
            'orderId' => '123',
            'description' => 'Order #123',
            'callbackUrl' => 'https://example.com/callback',
            'redirectUrl' => 'https://example.com/success'
        ];

        $response = $this->client->createQr($qrData, $accessToken);
        $this->debugLog('createQr', $response);

        $this->assertArrayHasKey('qrId', $response['result']);
        $this->assertNotEmpty($response['result']['qrId']);

        return [
            'accessToken' => $accessToken,
            'qrId' => $response['result']['qrId'],
            'qrData' => $qrData
        ];
    }

    /**
     * @depends testAuthenticate
     */
    public function testCreateHybridQr($accessToken)
    {
        $hybridData = [
            'amountType' => 'Fixed',
            'currency' => 'MDL',
            'terminalId' => 'P011111',
            'extension' => [
                'expiresAt' => $this->expiresAt,
                'amount' => 50.00,
                'description' => 'Order #123',
                'orderId' => '123',
                'callbackUrl' => 'https://example.com/callback',
                'redirectUrl' => 'https://example.com/success'
            ]
        ];

        $response = $this->client->createHybridQr($hybridData, $accessToken);
        $this->debugLog('createHybridQr', $response);

        $this->assertArrayHasKey('qrId', $response['result']);
        $this->assertNotEmpty($response['result']['qrId']);

        return [
            'accessToken' => $accessToken,
            'hybridQrId' => $response['result']['qrId']
        ];
    }

    /**
     * @depends testCreateHybridQr
     */
    public function testCreateQrExtension($data)
    {
        $this->markTestSkipped();

        $extensionData = [
            'expiresAt' => $this->expiresAt,
            'amount' => 100.00,
            'description' => 'Updated Order #456 description',
            'orderId' => '456',
            'callbackUrl' => 'https://example.com/callback',
            'redirectUrl' => 'https://example.com/success'
        ];

        $response = $this->client->createQrExtension($data['hybridQrId'], $extensionData, $data['accessToken']);
        $this->debugLog('createQrExtension', $response);
        $this->assertNotNull($response);
    }

    /**
     * @depends testCreateHybridQr
     */
    public function testCancelQrExtension($data)
    {
        $response = $this->client->cancelQrExtension($data['hybridQrId'], 'Test cancel reason', $data['accessToken']);
        $this->debugLog('cancelQrExtension', $response);
        $this->assertNotNull($response);
    }

    /**
     * @depends testCreateHybridQr
     */
    public function testCancelQr($data)
    {
        $response = $this->client->cancelQr($data['hybridQrId'], 'Test cancel reason', $data['accessToken']);
        $this->debugLog('cancelQr', $response);
        $this->assertNotNull($response);
    }

    /**
     * @depends testCreateDynamicQr
     */
    public function testGetQrDetails($data)
    {
        $response = $this->client->qrDetails($data['qrId'], $data['accessToken']);
        $this->debugLog('qrDetails', $response);
        $this->assertNotNull($response);
    }

    /**
     * @depends testAuthenticate
     */
    public function testListQrCodes($accessToken)
    {
        $params = [
            'count' => 10,
            'offset' => 0,
            'amountFrom' => 10.00,
            'amountTo' => 100.00,
            'sortBy' => 'createdAt',
            'order' => 'desc'
        ];

        $response = $this->client->qrList($params, $accessToken);
        $this->debugLog('qrList', $response);
        $this->assertNotNull($response);
    }

    /**
     * @depends testCreateDynamicQr
     */
    public function testPerformTestQrPayment($data)
    {
        $testPayData = [
            'qrId' => $data['qrId'],
            'amount' => $data['qrData']['amount'],
            'iban' => 'MD88AG000000011621810140',
            'currency' => $data['qrData']['currency'],
            'payerName' => 'TEST QR PAYMENT'
        ];

        $response = $this->client->testPay($testPayData, $data['accessToken']);
        $this->debugLog('testPay', $response);

        $this->assertArrayHasKey('payId', $response['result']);
        $this->assertNotEmpty($response['result']['payId']);

        return [
            'accessToken' => $data['accessToken'],
            'qrPayId' => $response['result']['payId'],
            'qrId' => $data['qrId']
        ];
    }

    /**
     * @depends testPerformTestQrPayment
     */
    public function testGetPaymentDetails($data)
    {
        $response = $this->client->paymentDetails($data['qrPayId'], $data['accessToken']);
        $this->debugLog('paymentDetails', $response);
        $this->assertNotNull($response);
    }

    /**
     * @depends testPerformTestQrPayment
     */
    public function testRefundPayment($data)
    {
        $response = $this->client->paymentRefund($data['qrPayId'], 'Test refund reason', $data['accessToken']);
        $this->debugLog('paymentRefund', $response);
        $this->assertNotNull($response);
    }

    /**
     * @depends testPerformTestQrPayment
     */
    public function testListPayments($data)
    {
        $params = [
            'count' => 10,
            'offset' => 0,
            'qrId' => $data['qrId'],
            'sortBy' => 'executedAt',
            'order' => 'asc'
        ];

        $response = $this->client->paymentList($params, $data['accessToken']);
        $this->debugLog('paymentList', $response);
        $this->assertNotNull($response);
    }


    /**
     * @depends testAuthenticate
     */
    public function testCreateRtpRequest($accessToken)
    {
        $rtpData = [
            'alias' => '37369112221',
            'amount' => 150.00,
            'expiresAt' => $this->expiresAt,
            'currency' => 'MDL',
            'description' => 'Invoice #123',
            'orderId' => '123',
            'terminalId' => 'P011111',
            'callbackUrl' => 'https://example.com/callback',
            'redirectUrl' => 'https://example.com/success'
        ];

        $response = $this->client->rtpCreate($rtpData, $accessToken);
        $this->debugLog('rtpCreate', $response);

        $this->assertArrayHasKey('rtpId', $response['result']);
        $this->assertNotEmpty($response['result']['rtpId']);

        return [
            'accessToken' => $accessToken,
            'rtpId' => $response['result']['rtpId'],
            'rtpData' => $rtpData
        ];
    }

    /**
     * @depends testCreateRtpRequest
     */
    public function testGetRtpStatus($data)
    {
        $response = $this->client->rtpStatus($data['rtpId'], $data['accessToken']);
        $this->debugLog('rtpStatus', $response);
        $this->assertNotNull($response);
    }

    /**
     * @depends testAuthenticate
     */
    public function testListRtpRequests($accessToken)
    {
        $params = [
            'count' => 10,
            'offset' => 0,
            'amount' => 10.00,
            'sortBy' => 'createdAt',
            'order' => 'desc'
        ];
        $response = $this->client->rtpList($params, $accessToken);
        $this->debugLog('rtpList', $response);
        $this->assertNotNull($response);
    }

    /**
     * @depends testCreateRtpRequest
     */
    public function testAcceptRtpRequest($data)
    {
        $acceptData = ['amount' => 150.00, 'currency' => 'MDL'];
        $response = $this->client->rtpTestAccept($data['rtpId'], $acceptData, $data['accessToken']);
        $this->debugLog('rtpTestAccept', $response);

        $this->assertArrayHasKey('payId', $response['result']);
        $this->assertNotEmpty($response['result']['payId']);

        return [
            'accessToken' => $data['accessToken'],
            'rtpPayId' => $response['result']['payId']
        ];
    }

    /**
     * @depends testAcceptRtpRequest
     */
    public function testRefundRtpPayment($data)
    {
        $response = $this->client->rtpRefund($data['rtpPayId'], 'Test refund reason', $data['accessToken']);
        $this->debugLog('rtpRefund', $response);
        $this->assertNotNull($response);
    }

    /**
     * @depends testCreateRtpRequest
     */
    public function testCancelRtpRequest($data)
    {
        // Create a new one to cancel, just like in test.js
        $response = $this->client->rtpCreate($data['rtpData'], $data['accessToken']);
        $this->debugLog('rtpCreate', $response);
        $rtpIdToCancel = $response['result']['rtpId'];

        $cancelResponse = $this->client->rtpCancel($rtpIdToCancel, 'Test cancel reason', $data['accessToken']);
        $this->debugLog('rtpCancel', $cancelResponse);
        $this->assertNotNull($cancelResponse);
    }


    public function testValidateCallbackSignature()
    {
        $callbackData = [
            'result' => [
                'qrId' => 'c3108b2f-6c2e-43a2-bdea-123456789012',
                'extensionId' => '3fe7f013-23a6-4d09-a4a4-123456789012',
                'qrStatus' => 'Paid',
                'payId' => 'eb361f48-bb39-45e2-950b-123456789012',
                'referenceId' => 'MIA0001234567',
                'orderId' => '123',
                'amount' => 50.00,
                'commission' => 0.1,
                'currency' => 'MDL',
                'payerName' => 'TEST QR PAYMENT',
                'payerIban' => 'MD88AG000000011621810140',
                'executedAt' => '2025-04-18T14:04:11.81145+00:00',
                'terminalId' => null
            ],
            'signature' => 'fHM+l4L1ycFWZDRTh/Vr8oybq1Q1xySdjyvmFQCmZ4s='
        ];

        $callbackData['signature'] = MaibMiaClient::computeDataSignature($callbackData['result'], self::$signatureKey);
        $isValid = MaibMiaClient::validateCallbackSignature($callbackData, self::$signatureKey);
        $this->assertTrue($isValid);
    }
}
