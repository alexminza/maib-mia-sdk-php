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

    // Shared state
    protected static $accessToken;
    protected static $qrId;
    protected static $qrData;
    protected static $hybridQrId;
    protected static $qrPayId;
    protected static $rtpId;
    protected static $rtpData;
    protected static $rtpPayId;

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
        $options = [
            'base_uri' => self::$baseUrl,
            'timeout' => 15
        ];

        #region Logging
        $classParts = explode('\\', self::class);
        $logName = end($classParts) . '_guzzle';
        $logFileName = "$logName.log";

        $log = new \Monolog\Logger($logName);
        $log->pushHandler(new \Monolog\Handler\StreamHandler($logFileName, \Monolog\Level::Debug));

        $stack = \GuzzleHttp\HandlerStack::create();
        $stack->push(\GuzzleHttp\Middleware::log($log, new \GuzzleHttp\MessageFormatter(\GuzzleHttp\MessageFormatter::DEBUG)));

        $options['handler'] = $stack;
        #endregion

        $this->client = new MaibMiaClient(new Client($options));
        $this->expiresAt = (new \DateTime())->modify('+1 hour')->format('c');
    }

    protected function onNotSuccessfulTest(\Throwable $t): never
    {
        // https://github.com/guzzle/guzzle/issues/2185
        if ($t instanceof \GuzzleHttp\Command\Exception\CommandException) {
            $response = $t->getResponse();
            $responseBody = (string) $response->getBody();
            // $this->debugLog($responseBody, $t->getMessage());
        }

        parent::onNotSuccessfulTest($t);
    }

    protected function debugLog($message, $data)
    {
        $data_print = print_r($data, true);
        error_log("$message: $data_print");
    }

    public function testAuthenticate()
    {
        $response = $this->client->getToken(self::$clientId, self::$clientSecret);
        // $this->debugLog('getToken', $response);

        $this->assertArrayHasKey('accessToken', $response['result']);
        $this->assertNotEmpty($response['result']['accessToken']);

        self::$accessToken = $response['result']['accessToken'];
    }

    /**
     * @depends testAuthenticate
     */
    public function testCreateDynamicQr()
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

        $response = $this->client->qrCreate($qrData, self::$accessToken);
        // $this->debugLog('qrCreate', $response);

        $this->assertArrayHasKey('qrId', $response['result']);
        $this->assertNotEmpty($response['result']['qrId']);

        self::$qrId = $response['result']['qrId'];
        self::$qrData = $qrData;
    }

    /**
     * @depends testAuthenticate
     */
    public function testQrCreateHybrid()
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

        $response = $this->client->qrCreateHybrid($hybridData, self::$accessToken);
        // $this->debugLog('qrCreateHybrid', $response);

        $this->assertArrayHasKey('qrId', $response['result']);
        $this->assertNotEmpty($response['result']['qrId']);

        self::$hybridQrId = $response['result']['qrId'];
    }

    /**
     * @depends testQrCreateHybrid
     */
    public function testQrCreateExtension()
    {
        $extensionData = [
            'expiresAt' => $this->expiresAt,
            'amount' => 100.00,
            'description' => 'Updated Order #456 description',
            'orderId' => '456',
            'callbackUrl' => 'https://example.com/callback',
            'redirectUrl' => 'https://example.com/success'
        ];

        $response = $this->client->qrCreateExtension(self::$hybridQrId, $extensionData, self::$accessToken);
        // $this->debugLog('qrCreateExtension', $response);

        $this->assertNotNull($response);
    }

    /**
     * @depends testQrCreateHybrid
     */
    public function testQrCancelExtension()
    {
        $response = $this->client->qrCancelExtension(self::$hybridQrId, 'Test cancel reason', self::$accessToken);
        // $this->debugLog('qrCancelExtension', $response);

        $this->assertNotNull($response);
    }

    /**
     * @depends testQrCreateHybrid
     */
    public function testQrCancel()
    {
        $response = $this->client->qrCancel(self::$hybridQrId, 'Test cancel reason', self::$accessToken);
        // $this->debugLog('qrCancel', $response);

        $this->assertNotNull($response);
    }

    /**
     * @depends testCreateDynamicQr
     */
    public function testQrDetails()
    {
        $response = $this->client->qrDetails(self::$qrId, self::$accessToken);
        // $this->debugLog('qrDetails', $response);

        $this->assertNotNull($response);
    }

    /**
     * @depends testAuthenticate
     */
    public function testListQrCodes()
    {
        $params = [
            'count' => 10,
            'offset' => 0,
            'amountFrom' => 10.00,
            'amountTo' => 100.00,
            'sortBy' => 'createdAt',
            'order' => 'desc'
        ];

        $response = $this->client->qrList($params, self::$accessToken);
        // $this->debugLog('qrList', $response);

        $this->assertNotNull($response);
    }

    /**
     * @depends testCreateDynamicQr
     */
    public function testPerformTestQrPayment()
    {
        $testPayData = [
            'qrId' => self::$qrId,
            'amount' => self::$qrData['amount'],
            'currency' => self::$qrData['currency'],
            'iban' => 'MD88AG000000011621810140',
            'payerName' => 'TEST QR PAYMENT'
        ];

        $response = $this->client->testPay($testPayData, self::$accessToken);
        // $this->debugLog('testPay', $response);

        $this->assertArrayHasKey('payId', $response['result']);
        $this->assertNotEmpty($response['result']['payId']);

        self::$qrPayId = $response['result']['payId'];
    }

    /**
     * @depends testPerformTestQrPayment
     */
    public function testGetPaymentDetails()
    {
        $response = $this->client->paymentDetails(self::$qrPayId, self::$accessToken);
        // $this->debugLog('paymentDetails', $response);

        $this->assertNotNull($response);
    }

    /**
     * @depends testPerformTestQrPayment
     */
    public function testRefundPayment()
    {
        $response = $this->client->paymentRefund(self::$qrPayId, 'Test refund reason', self::$accessToken);
        // $this->debugLog('paymentRefund', $response);

        $this->assertNotNull($response);
    }

    /**
     * @depends testPerformTestQrPayment
     */
    public function testListPayments()
    {
        $params = [
            'count' => 10,
            'offset' => 0,
            'qrId' => self::$qrId,
            'sortBy' => 'executedAt',
            'order' => 'asc'
        ];

        $response = $this->client->paymentList($params, self::$accessToken);
        // $this->debugLog('paymentList', $response);

        $this->assertNotNull($response);
    }


    /**
     * @depends testAuthenticate
     */
    public function testCreateRtpRequest()
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

        $response = $this->client->rtpCreate($rtpData, self::$accessToken);
        // $this->debugLog('rtpCreate', $response);

        $this->assertArrayHasKey('rtpId', $response['result']);
        $this->assertNotEmpty($response['result']['rtpId']);

        self::$rtpId = $response['result']['rtpId'];
        self::$rtpData = $rtpData;
    }

    /**
     * @depends testCreateRtpRequest
     */
    public function testGetRtpStatus()
    {
        $response = $this->client->rtpStatus(self::$rtpId, self::$accessToken);
        // $this->debugLog('rtpStatus', $response);

        $this->assertNotNull($response);
    }

    /**
     * @depends testAuthenticate
     */
    public function testListRtpRequests()
    {
        $params = [
            'count' => 10,
            'offset' => 0,
            'amount' => 10.00,
            'sortBy' => 'createdAt',
            'order' => 'desc'
        ];
        $response = $this->client->rtpList($params, self::$accessToken);
        // $this->debugLog('rtpList', $response);

        $this->assertNotNull($response);
    }

    /**
     * @depends testCreateRtpRequest
     */
    public function testAcceptRtpRequest()
    {
        $acceptData = [
            'amount' => self::$rtpData['amount'],
            'currency' => self::$rtpData['currency']
        ];

        $response = $this->client->rtpTestAccept(self::$rtpId, $acceptData, self::$accessToken);
        // $this->debugLog('rtpTestAccept', $response);

        $this->assertArrayHasKey('payId', $response['result']);
        $this->assertNotEmpty($response['result']['payId']);

        self::$rtpPayId = $response['result']['payId'];
    }

    /**
     * @depends testAcceptRtpRequest
     */
    public function testRefundRtpPayment()
    {
        $response = $this->client->rtpRefund(self::$rtpPayId, 'Test refund reason', self::$accessToken);
        // $this->debugLog('rtpRefund', $response);

        $this->assertNotNull($response);
    }

    /**
     * @depends testCreateRtpRequest
     */
    public function testCancelRtpRequest()
    {
        // Create a new one to cancel
        $response = $this->client->rtpCreate(self::$rtpData, self::$accessToken);
        // $this->debugLog('rtpCreate', $response);

        $rtpIdToCancel = $response['result']['rtpId'];

        $cancelResponse = $this->client->rtpCancel($rtpIdToCancel, 'Test cancel reason', self::$accessToken);
        // $this->debugLog('rtpCancel', $cancelResponse);

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

        $this->assertFalse(MaibMiaClient::validateCallbackSignature($callbackData, self::$signatureKey));

        $callbackData['signature'] = MaibMiaClient::computeDataSignature($callbackData['result'], self::$signatureKey);
        $this->assertTrue(MaibMiaClient::validateCallbackSignature($callbackData, self::$signatureKey));
    }
}
