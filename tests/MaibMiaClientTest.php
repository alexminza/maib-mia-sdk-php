<?php

namespace Maib\MaibMia\Tests;

use Maib\MaibMia\MaibMiaClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;

class MaibMiaClientTest extends TestCase
{
    private function getMockClient(array $responses = [])
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        return new MaibMiaClient($client);
    }

    public function testRtpCreate()
    {
        $responseBody = json_encode(['rtpId' => 'some-uuid']);
        $client = $this->getMockClient([
            new Response(200, [], $responseBody)
        ]);

        $rtpData = [
            'alias' => '37369112221',
            'amount' => 10.0,
            'expiresAt' => '2029-10-22T10:32:28+03:00',
            'currency' => 'MDL',
            'description' => 'Test order',
        ];

        $result = $client->rtpCreate($rtpData, 'token');
        $this->assertEquals('some-uuid', $result['rtpId']);
    }

    public function testRtpStatus()
    {
        $responseBody = json_encode(['rtpId' => 'some-uuid', 'status' => 'Active']);
        $client = $this->getMockClient([
            new Response(200, [], $responseBody)
        ]);

        $result = $client->rtpStatus('some-uuid', 'token');
        $this->assertEquals('Active', $result['status']);
    }

    public function testRtpCancel()
    {
        $responseBody = json_encode(['status' => 'Cancelled']);
        $client = $this->getMockClient([
            new Response(200, [], $responseBody)
        ]);

        $result = $client->rtpCancel('some-uuid', 'Reason', 'token');
        $this->assertEquals('Cancelled', $result['status']);
    }

    public function testRtpList()
    {
        $responseBody = json_encode(['data' => [['rtpId' => '1'], ['rtpId' => '2']]]);
        $client = $this->getMockClient([
            new Response(200, [], $responseBody)
        ]);

        $result = $client->rtpList(['count' => 10, 'offset' => 0], 'token');
        $this->assertCount(2, $result['data']);
    }

    public function testRtpRefund()
    {
        $responseBody = json_encode(['status' => 'Refunded']);
        $client = $this->getMockClient([
            new Response(200, [], $responseBody)
        ]);

        $result = $client->rtpRefund('pay-uuid', 'Reason', 'token');
        $this->assertEquals('Refunded', $result['status']);
    }

    public function testRtpTestAccept()
    {
        $responseBody = json_encode(['status' => 'Accepted']);
        $client = $this->getMockClient([
            new Response(200, [], $responseBody)
        ]);

        $result = $client->rtpTestAccept('some-uuid', ['amount' => 10.0, 'currency' => 'MDL'], 'token');
        $this->assertEquals('Accepted', $result['status']);
    }

    public function testRtpTestReject()
    {
        $responseBody = json_encode(['status' => 'Rejected']);
        $client = $this->getMockClient([
            new Response(200, [], $responseBody)
        ]);

        $result = $client->rtpTestReject('some-uuid', 'token');
        $this->assertEquals('Rejected', $result['status']);
    }
}
