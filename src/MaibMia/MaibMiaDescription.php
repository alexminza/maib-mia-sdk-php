<?php

namespace Maib\MaibMia;

use GuzzleHttp\Command\Guzzle\Description;

class MaibMiaDescription extends Description
{
    public function __construct(array $options = [])
    {
        $authorizationHeader = [
            'type' => 'string',
            'location' => 'header',
            'sentAs' => 'Authorization',
            'description' => 'Bearer Authentication with JWT Token',
            'required' => true,
        ];

        $description = [
            //'baseUrl' => 'https://api.maibmerchants.md/',
            'name' => 'maib MIA QR API',
            'apiVersion' => 'v2',

            'operations' => [
                // Authentication Operations
                'getToken' => [
                    'httpMethod' => 'POST',
                    'uri' => '/v2/auth/token',
                    'description' => 'Obtain Authentication Token',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'clientId' => ['type' => 'string', 'location' => 'json'],
                        'clientSecret' => ['type' => 'string', 'location' => 'json'],
                    ],
                ],

                // QR Operations
                'createQr' => [
                    'httpMethod' => 'POST',
                    'uri' => '/v2/mia/qr',
                    'description' => 'Create QR Code (Static, Dynamic)',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'type' => ['type' => 'string', 'location' => 'json', 'enum' => ['Static', 'Dynamic'], 'required' => true],
                        'expiresAt' => ['type' => 'string', 'format' => 'date-time', 'location' => 'json'],
                        'amountType' => ['type' => 'string', 'location' => 'json', 'enum' => ['Fixed', 'Controlled', 'Free'], 'required' => true],
                        'amount' => ['type' => 'number', 'location' => 'json'],
                        'amountMin' => ['type' => 'number', 'location' => 'json'],
                        'amountMax' => ['type' => 'number', 'location' => 'json'],
                        'currency' => ['type' => 'string', 'location' => 'json', 'enum' => ['MDL'], 'required' => true],
                        'description' => ['type' => 'string', 'location' => 'json', 'required' => true],
                        'orderId' => ['type' => 'string', 'location' => 'json'],
                        'callbackUrl' => ['type' => 'string', 'location' => 'json'],
                        'redirectUrl' => ['type' => 'string', 'location' => 'json'],
                        'terminalId' => ['type' => 'string', 'location' => 'json'],
                    ],
                ],
            ],

            'models' => [
                'getResponse' => [
                    'type' => 'object',
                    'additionalProperties' => [
                        'location' => 'json'
                    ]
                ]
            ]
        ];

        parent::__construct($description, $options);
    }
}
