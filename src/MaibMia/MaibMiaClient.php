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

    public function createQr($qrData, $authToken)
    {
        $qrData['authToken'] = $authToken;

        // $args = [
        //     'qrData' => $qrData,
        //     'authToken' => $authToken
        // ];

        return parent::createQr($qrData);
    }
}
