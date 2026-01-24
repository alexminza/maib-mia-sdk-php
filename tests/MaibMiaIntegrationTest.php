<?php

declare(strict_types=1);

namespace Maib\MaibMia\Tests;

use Maib\MaibMia\MaibMiaClient;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class MaibMiaIntegrationTest extends TestCase
{
    protected static $clientId;
    protected static $clientSecret;
    protected static $signatureKey;
    protected static $callbackUrl;
    protected static $baseUrl;

    // Shared state
    protected static $accessToken;
    protected static $qrId;
    protected static $qrData;
    protected static $hybridQrData;
    protected static $hybridQrId;
    protected static $hybridQrExtensionId;
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
        self::$clientId     = getenv('MAIB_MIA_CLIENT_ID');
        self::$clientSecret = getenv('MAIB_MIA_CLIENT_SECRET');
        self::$signatureKey = getenv('MAIB_MIA_SIGNATURE_KEY');
        self::$callbackUrl  = getenv('MAIB_MIA_CALLBACK_URL');
        self::$baseUrl      = MaibMiaClient::SANDBOX_BASE_URL;

        if (empty(self::$clientId) || empty(self::$clientSecret) || empty(self::$signatureKey)) {
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
        $log->pushHandler(new \Monolog\Handler\StreamHandler($logFileName, \Monolog\Logger::DEBUG));

        $stack = \GuzzleHttp\HandlerStack::create();
        $stack->push(\GuzzleHttp\Middleware::log($log, new \GuzzleHttp\MessageFormatter(\GuzzleHttp\MessageFormatter::DEBUG)));

        $options['handler'] = $stack;
        #endregion

        $this->client = new MaibMiaClient(new Client($options));
        $this->expiresAt = (new \DateTime())->modify('+1 hour')->format('c');
    }

    protected function onNotSuccessfulTest(\Throwable $t): void
    {
        if ($this->isDebugMode()) {
            // https://github.com/guzzle/guzzle/issues/2185
            if ($t instanceof \GuzzleHttp\Command\Exception\CommandException) {
                $response = $t->getResponse();
                $responseBody = !empty($response) ? (string) $response->getBody() : '';
                $exceptionMessage = $t->getMessage();

                $this->debugLog($responseBody, $exceptionMessage);
            }
        }

        parent::onNotSuccessfulTest($t);
    }

    protected function isDebugMode()
    {
        // https://stackoverflow.com/questions/12610605/is-there-a-way-to-tell-if-debug-or-verbose-was-passed-to-phpunit-in-a-test
        return in_array('--debug', $_SERVER['argv'] ?? []);
    }

    protected function debugLog($message, $data)
    {
        $data_print = print_r($data, true);
        error_log("$message: $data_print");
    }

    protected function assertResultOk($response)
    {
        $this->assertNotEmpty($response);
        $this->assertArrayHasKey('ok', $response);
        $this->assertTrue($response['ok']);
        $this->assertArrayHasKey('result', $response);
        $this->assertNotEmpty($response['result']);
    }

    protected function assertResultNotOk($response)
    {
        $this->assertNotEmpty($response);
        $this->assertArrayHasKey('ok', $response);
        $this->assertFalse($response['ok']);
        $this->assertArrayHasKey('errors', $response);
        $this->assertNotEmpty($response['errors']);
    }

    public function testAuthenticate()
    {
        $response = $this->client->getToken(self::$clientId, self::$clientSecret);
        // $this->debugLog('getToken', $response);

        $this->assertResultOk($response);
        $this->assertNotEmpty($response['result']['accessToken']);

        self::$accessToken = $response['result']['accessToken'];
    }

    #region QR
    /**
     * @depends testAuthenticate
     */
    public function testQrCreateDynamic()
    {
        $qrData = [
            'type' => 'Dynamic',
            'expiresAt' => $this->expiresAt,
            'amountType' => 'Fixed',
            'amount' => 50.00,
            'currency' => 'MDL',
            'orderId' => '123',
            'description' => 'Order #123',
            'callbackUrl' => self::$callbackUrl . '/callback',
            'redirectUrl' => self::$callbackUrl . '/success'
        ];

        $response = $this->client->qrCreate($qrData, self::$accessToken);
        // $this->debugLog('qrCreate', $response);

        $this->assertResultOk($response);
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
                'callbackUrl' => self::$callbackUrl . '/callback',
                'redirectUrl' => self::$callbackUrl . '/success'
            ]
        ];

        $response = $this->client->qrCreateHybrid($hybridData, self::$accessToken);
        // $this->debugLog('qrCreateHybrid', $response);

        $this->assertResultOk($response);
        $this->assertNotEmpty($response['result']['qrId']);
        $this->assertNotEmpty($response['result']['extensionId']);

        self::$hybridQrId = $response['result']['qrId'];
        self::$hybridQrExtensionId = $response['result']['extensionId'];
        self::$hybridQrData = $hybridData;
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
            'callbackUrl' => self::$callbackUrl . '/callback',
            'redirectUrl' => self::$callbackUrl . '/success'
        ];

        $response = $this->client->qrCreateExtension(self::$hybridQrId, $extensionData, self::$accessToken);
        // $this->debugLog('qrCreateExtension', $response);

        $this->assertResultOk($response);
    }

    /**
     * @depends testQrCreateHybrid
     */
    public function testQrCancel()
    {
        $cancelData = [
            'reason' => 'testQrCancel reason'
        ];

        $response = $this->client->qrCancel(self::$hybridQrId, $cancelData, self::$accessToken);
        // $this->debugLog('qrCancel', $response);

        $this->assertResultOk($response);
        $this->assertEquals(self::$hybridQrId, $response['result']['qrId']);
        $this->assertEquals('Cancelled', $response['result']['status']);
    }

    /**
     * @depends testQrCreateHybrid
     */
    public function testQrCancelExtension()
    {
        $cancelData = [
            'reason' => 'testQrCancelExtension reason'
        ];

        $response = $this->client->qrCancelExtension(self::$hybridQrId, $cancelData, self::$accessToken);
        // $this->debugLog('qrCancelExtension', $response);

        $this->assertResultOk($response);
        $this->assertEquals(self::$hybridQrExtensionId, $response['result']['extensionId']);
    }

    /**
     * @depends testQrCreateDynamic
     */
    public function testQrDetails()
    {
        $response = $this->client->qrDetails(self::$qrId, self::$accessToken);
        // $this->debugLog('qrDetails', $response);

        $this->assertResultOk($response);
        $this->assertEquals(self::$qrId, $response['result']['qrId']);
        $this->assertEquals('Active', $response['result']['status']);
        $this->assertEquals(self::$qrData['type'], $response['result']['type']);
        $this->assertEquals(self::$qrData['amount'], $response['result']['amount']);
        $this->assertEquals(self::$qrData['currency'], $response['result']['currency']);
    }

    /**
     * @depends testAuthenticate
     */
    public function testQrList()
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

        $this->assertResultOk($response);
        $this->assertArrayHasKey('items', $response['result']);
        $this->assertArrayHasKey('totalCount', $response['result']);
    }
    #endregion

    #region Payment
    /**
     * @depends testQrCreateDynamic
     */
    public function testPay()
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

        $this->assertResultOk($response);
        $this->assertEquals(self::$qrId, $response['result']['qrId']);
        $this->assertEquals('Paid', $response['result']['qrStatus']);
        $this->assertEquals(self::$qrData['amount'], $response['result']['amount']);
        $this->assertEquals(self::$qrData['currency'], $response['result']['currency']);
        $this->assertNotEmpty($response['result']['payId']);

        self::$qrPayId = $response['result']['payId'];
    }

    /**
     * @depends testPay
     */
    public function testPaymentDetails()
    {
        $response = $this->client->paymentDetails(self::$qrPayId, self::$accessToken);
        // $this->debugLog('paymentDetails', $response);

        $this->assertResultOk($response);
        $this->assertEquals(self::$qrPayId, $response['result']['payId']);
        $this->assertEquals('Executed', $response['result']['status']);
        $this->assertEquals(self::$qrData['amount'], $response['result']['amount']);
        $this->assertEquals(self::$qrData['currency'], $response['result']['currency']);
    }

    /**
     * @depends testPay
     */
    public function testPaymentRefundPartial()
    {
        $refundData = [
            'amount' => self::$qrData['amount'] / 2,
            'reason' => 'testPaymentRefundPartial reason',
            'callbackUrl' => self::$callbackUrl . '/refund'
        ];

        $response = $this->client->paymentRefund(self::$qrPayId, $refundData, self::$accessToken);
        // $this->debugLog('paymentRefund', $response);

        $this->assertResultOk($response);
        $this->assertNotEmpty($response['result']['refundId']);
        $this->assertEquals('Created', $response['result']['status']);
    }

    /**
     * @depends testPaymentRefundPartial
     */
    public function testPaymentRefundFull()
    {
        $refundData = [
            'reason' => 'testPaymentRefundFull reason',
            'callbackUrl' => self::$callbackUrl . '/refund'
        ];

        $response = $this->client->paymentRefund(self::$qrPayId, $refundData, self::$accessToken);
        // $this->debugLog('paymentRefund', $response);

        $this->assertResultOk($response);
        $this->assertNotEmpty($response['result']['refundId']);
        $this->assertEquals('Created', $response['result']['status']);
    }

    /**
     * @depends testPaymentRefundFull
     */
    public function testPaymentRefundError()
    {
        $this->markTestSkipped();

        $refundData = [
            'reason' => 'testRefundPaymentError reason',
            'callbackUrl' => self::$callbackUrl . '/refund'
        ];

        $response = $this->client->paymentRefund(self::$qrPayId, $refundData, self::$accessToken);
        // $this->debugLog('paymentRefund', $response);

        $this->assertResultNotOk($response);
        $this->assertEquals('payments.acquiring.payments-01001', $response['errors'][0]['errorCode']);
    }

    /**
     * @depends testPay
     */
    public function testPaymentList()
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

        $this->assertResultOk($response);
        $this->assertArrayHasKey('items', $response['result']);
        $this->assertArrayHasKey('totalCount', $response['result']);
    }
    #endregion

    #region RTP
    /**
     * @depends testAuthenticate
     */
    public function testRtpCreate()
    {
        $rtpData = [
            'alias' => '37369112221',
            'amount' => 150.00,
            'expiresAt' => $this->expiresAt,
            'currency' => 'MDL',
            'description' => 'Invoice #123',
            'orderId' => '123',
            'terminalId' => 'P011111',
            'callbackUrl' => self::$callbackUrl . '/callback',
            'redirectUrl' => self::$callbackUrl . '/success'
        ];

        $response = $this->client->rtpCreate($rtpData, self::$accessToken);
        // $this->debugLog('rtpCreate', $response);

        $this->assertResultOk($response);
        $this->assertNotEmpty($response['result']['rtpId']);

        self::$rtpId = $response['result']['rtpId'];
        self::$rtpData = $rtpData;
    }

    /**
     * @depends testRtpCreate
     */
    public function testRtpStatus()
    {
        $response = $this->client->rtpStatus(self::$rtpId, self::$accessToken);
        // $this->debugLog('rtpStatus', $response);

        $this->assertResultOk($response);
        $this->assertEquals(self::$rtpId, $response['result']['rtpId']);
    }

    /**
     * @depends testAuthenticate
     */
    public function testRtpList()
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

        $this->assertResultOk($response);
        $this->assertArrayHasKey('items', $response['result']);
        $this->assertArrayHasKey('totalCount', $response['result']);
    }

    /**
     * @depends testRtpCreate
     */
    public function testRtpTestAccept()
    {
        $acceptData = [
            'amount' => self::$rtpData['amount'],
            'currency' => self::$rtpData['currency']
        ];

        $response = $this->client->rtpTestAccept(self::$rtpId, $acceptData, self::$accessToken);
        // $this->debugLog('rtpTestAccept', $response);

        $this->assertResultOk($response);
        $this->assertNotEmpty($response['result']['payId']);

        self::$rtpPayId = $response['result']['payId'];
    }

    /**
     * @depends testRtpTestAccept
     */
    public function testRtpRefund()
    {
        $refundData = [
            'reason' => 'testRtpRefund reason'
        ];

        $response = $this->client->rtpRefund(self::$rtpPayId, $refundData, self::$accessToken);
        // $this->debugLog('rtpRefund', $response);

        $this->assertResultOk($response);
        $this->assertNotEmpty($response['result']['refundId']);
        $this->assertEquals('Created', $response['result']['status']);
    }

    /**
     * @depends testRtpCreate
     */
    public function testRtpCancel()
    {
        $response = $this->client->rtpCreate(self::$rtpData, self::$accessToken);
        // $this->debugLog('rtpCreate', $response);

        $rtpId = $response['result']['rtpId'];
        $cancelData = [
            'reason' => 'testRtpCancel reason'
        ];

        $response = $this->client->rtpCancel($rtpId, $cancelData, self::$accessToken);
        // $this->debugLog('rtpCancel', $cancelResponse);

        $this->assertResultOk($response);
        $this->assertEquals($rtpId, $response['result']['rtpId']);
        $this->assertEquals('Cancelled', $response['result']['status']);
    }
    #endregion

    #region Signature
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
    #endregion
}
