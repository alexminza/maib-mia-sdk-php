<?php

namespace Maib\MaibMia;

use GuzzleHttp\Command\Guzzle\Description;
use Composer\InstalledVersions;

class MaibMiaDescription extends Description
{
    public function __construct(array $options = [])
    {
        $package = 'alexminza/maib-mia-sdk';
        $version = InstalledVersions::getPrettyVersion($package) ?? 'dev';
        $userAgent = "$package-php/$version";

        $authorizationHeader = [
            'type' => 'string',
            'location' => 'header',
            'sentAs' => 'Authorization',
            'summary' => 'Bearer Authentication with JWT Token',
            'required' => true,
        ];

        $description = [
            //'baseUrl' => 'https://api.maibmerchants.md/',
            'name' => 'maib MIA QR API',
            'apiVersion' => 'v2',

            'operations' => [
                'baseOp' => [
                    'parameters' => [
                        'User-Agent' => [
                            'location' => 'header',
                            'default'  => $userAgent,
                        ],
                    ],
                ],

                // Authentication Operations
                'getToken' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/auth/token',
                    'summary' => 'Obtain Authentication Token',
                    'responseModel' => 'getResponse',
                    'additionalParameters' => [
                        'location' => 'json',
                        'schema' => ['$ref' => 'AuthTokenDto']
                    ]
                ],

                // QR Operations
                'qrCreate' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/mia/qr',
                    'summary' => 'Create QR Code (Static, Dynamic)',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                    ],
                    'additionalParameters' => [
                        'location' => 'json',
                        'schema' => ['$ref' => 'QrCreateDto']
                    ]
                ],
                'qrCreateHybrid' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/mia/qr/hybrid',
                    'summary' => 'Create Hybrid QR Code',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                    ],
                    'additionalParameters' => [
                        'location' => 'json',
                        'schema' => ['$ref' => 'QrCreateHybridDto']
                    ]
                ],
                'qrCreateExtension' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/mia/qr/{qrId}/extension',
                    'summary' => 'Create Extension for QR Code by ID',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'qrId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                    'additionalParameters' => [
                        'location' => 'json',
                        'schema' => ['$ref' => 'QrCreateExtensionDto']
                    ]
                ],
                'qrCancel' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/mia/qr/{qrId}/cancel',
                    'summary' => 'Cancel Active QR (Static, Dynamic)',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'qrId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                    'additionalParameters' => [
                        'location' => 'json',
                        'schema' => ['$ref' => 'CancelDto']
                    ]
                ],
                'qrCancelExtension' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/mia/qr/{qrId}/extension/cancel',
                    'summary' => 'Cancel Active QR Extension (Hybrid)',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'qrId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                    'additionalParameters' => [
                        'location' => 'json',
                        'schema' => ['$ref' => 'CancelDto']
                    ]
                ],

                // Payment Operations
                'paymentRefund' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/mia/payments/{payId}/refund',
                    'summary' => 'Refund Completed Payment',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'payId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                    'additionalParameters' => [
                        'location' => 'json',
                        'schema' => ['$ref' => 'CancelDto']
                    ]
                ],

                // Information Retrieval Operations
                'qrList' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/v2/mia/qr',
                    'summary' => 'Display List of QR Codes with Filtering Options',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                    ],
                    'additionalParameters' => [
                        'location' => 'query',
                        'schema' => ['$ref' => 'QrListDto']
                    ]
                ],
                'qrDetails' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/v2/mia/qr/{qrId}',
                    'summary' => 'Retrieve QR Details by ID',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'qrId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                ],
                'paymentList' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/v2/mia/payments',
                    'summary' => 'Retrieve List of Payments with Filtering Options',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                    ],
                    'additionalParameters' => [
                        'location' => 'query',
                        'schema' => ['$ref' => 'PaymentListDto']
                    ]
                ],
                'paymentDetails' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/v2/mia/payments/{payId}',
                    'summary' => 'Retrieve Payment Details by ID',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'payId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                ],

                // Payment Simulation Operations
                'testPay' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/mia/test-pay',
                    'summary' => 'Payment Simulation (Sandbox)',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                    ],
                    'additionalParameters' => [
                        'location' => 'json',
                        'schema' => ['$ref' => 'TestPayDto']
                    ]
                ],

                // RTP Operations
                'rtpCreate' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/rtp',
                    'summary' => 'Create a new payment request (RTP)',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                    ],
                    'additionalParameters' => [
                        'location' => 'json',
                        'schema' => ['$ref' => 'RtpCreateDto']
                    ]
                ],
                'rtpStatus' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/v2/rtp/{rtpId}',
                    'summary' => 'Retrieve the status of a payment request',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'rtpId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                ],
                'rtpCancel' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/rtp/{rtpId}/cancel',
                    'summary' => 'Cancel a pending payment request',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'rtpId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                    'additionalParameters' => [
                        'location' => 'json',
                        'schema' => ['$ref' => 'CancelDto']
                    ]
                ],
                'rtpList' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/v2/rtp',
                    'summary' => 'List all payment requests',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                    ],
                    'additionalParameters' => [
                        'location' => 'query',
                        'schema' => ['$ref' => 'RtpListDto']
                    ]
                ],
                'rtpRefund' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/rtp/{payId}/refund',
                    'summary' => 'Initiate a refund for a completed payment',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'payId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                    'additionalParameters' => [
                        'location' => 'json',
                        'schema' => ['$ref' => 'CancelDto']
                    ]
                ],

                // RTP Simulation Operations (Sandbox)
                'rtpTestAccept' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/rtp/{rtpId}/test-accept',
                    'summary' => 'Simulate acceptance of a payment request',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'rtpId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                    'additionalParameters' => [
                        'location' => 'json',
                        'schema' => ['$ref' => 'RtpTestAcceptDto']
                    ]
                ],
                'rtpTestReject' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/rtp/{rtpId}/test-reject',
                    'summary' => 'Simulate rejection of a payment request',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'rtpId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                ],
            ],

            'models' => [
                'getResponse' => [
                    'type' => 'object',
                    'additionalProperties' => [
                        'location' => 'json'
                    ]
                ],
                'AuthTokenDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'clientId' => ['type' => 'string', 'required' => true],
                        'clientSecret' => ['type' => 'string', 'required' => true],
                    ],
                ],
                'QrCreateDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'type' => ['type' => 'string', 'enum' => ['Static', 'Dynamic'], 'required' => true],
                        'expiresAt' => ['type' => 'string', 'format' => 'date-time'],
                        'amountType' => ['type' => 'string', 'enum' => ['Fixed', 'Controlled', 'Free'], 'required' => true],
                        'amount' => ['type' => 'number'],
                        'amountMin' => ['type' => 'number'],
                        'amountMax' => ['type' => 'number'],
                        'currency' => ['type' => 'string', 'enum' => ['MDL'], 'required' => true],
                        'description' => ['type' => 'string', 'required' => true],
                        'orderId' => ['type' => 'string'],
                        'callbackUrl' => ['type' => 'string'],
                        'redirectUrl' => ['type' => 'string'],
                        'terminalId' => ['type' => 'string'],
                    ],
                ],
                'QrCreateHybridDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'amountType' => ['type' => 'string', 'enum' => ['Fixed', 'Controlled', 'Free'], 'required' => true],
                        'currency' => ['type' => 'string', 'enum' => ['MDL'], 'required' => true],
                        'terminalId' => ['type' => 'string'],
                        'extension' => ['$ref' => 'QrCreateExtensionDto'],
                    ],
                ],
                'QrCreateExtensionDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'expiresAt' => ['type' => 'string', 'format' => 'date-time', 'required' => true],
                        'amount' => ['type' => 'number'],
                        'amountMin' => ['type' => 'number'],
                        'amountMax' => ['type' => 'number'],
                        'description' => ['type' => 'string', 'required' => true],
                        'orderId' => ['type' => 'string'],
                        'callbackUrl' => ['type' => 'string'],
                        'redirectUrl' => ['type' => 'string'],
                    ],
                ],
                'CancelDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'reason' => ['type' => 'string', 'required' => true],
                    ],
                ],
                'QrListDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'count' => ['type' => 'number', 'required' => true],
                        'offset' => ['type' => 'number', 'required' => true],
                        'sortBy' => ['type' => 'string', 'enum' => ['orderId', 'type', 'amountType', 'status', 'createdAt', 'expiresAt']],
                        'order' => ['type' => 'string', 'enum' => ['asc', 'desc']],
                        'qrId' => ['type' => 'string'],
                        'extensionId' => ['type' => 'string'],
                        'orderId' => ['type' => 'string'],
                        'type' => ['type' => 'string', 'enum' => ['Static', 'Dynamic', 'Hybrid']],
                        'amountType' => ['type' => 'string', 'enum' => ['Fixed', 'Controlled', 'Free']],
                        'amountFrom' => ['type' => 'number'],
                        'amountTo' => ['type' => 'number'],
                        'description' => ['type' => 'string'],
                        'status' => ['type' => 'string', 'enum' => ['Active', 'Inactive', 'Expired', 'Paid', 'Cancelled']],
                        'createdAtFrom' => ['type' => 'string', 'format' => 'date-time'],
                        'createdAtTo' => ['type' => 'string', 'format' => 'date-time'],
                        'expiresAtFrom' => ['type' => 'string', 'format' => 'date-time'],
                        'expiresAtTo' => ['type' => 'string', 'format' => 'date-time'],
                        'terminalId' => ['type' => 'string'],
                    ],
                ],
                'PaymentListDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'count' => ['type' => 'number', 'required' => true],
                        'offset' => ['type' => 'number', 'required' => true],
                        'sortBy' => ['type' => 'string', 'enum' => ['orderId', 'amount', 'status', 'executedAt']],
                        'order' => ['type' => 'string', 'enum' => ['asc', 'desc']],
                        'payId' => ['type' => 'string'],
                        'referenceId' => ['type' => 'string'],
                        'qrId' => ['type' => 'string'],
                        'extensionId' => ['type' => 'string'],
                        'orderId' => ['type' => 'string'],
                        'amountFrom' => ['type' => 'number'],
                        'amountTo' => ['type' => 'number'],
                        'description' => ['type' => 'string'],
                        'payerName' => ['type' => 'string'],
                        'payerIban' => ['type' => 'string'],
                        'status' => ['type' => 'string', 'enum' => ['Executed', 'Refunded']],
                        'executedAtFrom' => ['type' => 'string', 'format' => 'date-time'],
                        'executedAtTo' => ['type' => 'string', 'format' => 'date-time'],
                        'terminalId' => ['type' => 'string'],
                    ],
                ],
                'TestPayDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'qrId' => ['type' => 'string', 'required' => true],
                        'amount' => ['type' => 'number', 'required' => true],
                        'iban' => ['type' => 'string', 'required' => true],
                        'currency' => ['type' => 'string', 'enum' => ['MDL'], 'required' => true],
                        'payerName' => ['type' => 'string', 'required' => true],
                    ],
                ],
                'RtpCreateDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'alias' => ['type' => 'string', 'required' => true],
                        'amount' => ['type' => 'number', 'required' => true],
                        'expiresAt' => ['type' => 'string', 'format' => 'date-time', 'required' => true],
                        'currency' => ['type' => 'string', 'enum' => ['MDL'], 'required' => true],
                        'description' => ['type' => 'string', 'required' => true],
                        'orderId' => ['type' => 'string'],
                        'terminalId' => ['type' => 'string'],
                        'callbackUrl' => ['type' => 'string'],
                        'redirectUrl' => ['type' => 'string'],
                    ],
                ],
                'RtpListDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'count' => ['type' => 'number', 'required' => true],
                        'offset' => ['type' => 'number', 'required' => true],
                        'sortBy' => ['type' => 'string', 'enum' => ['orderId', 'type', 'amount', 'status', 'createdAt', 'expiresAt']],
                        'order' => ['type' => 'string', 'enum' => ['asc', 'desc']],
                        'rtpId' => ['type' => 'string'],
                        'orderId' => ['type' => 'string'],
                        'amount' => ['type' => 'number'],
                        'description' => ['type' => 'string'],
                        'status' => ['type' => 'string', 'enum' => ['Created', 'Active', 'Cancelled', 'Accepted', 'Rejected', 'Expired']],
                        'createdAtFrom' => ['type' => 'string', 'format' => 'date-time'],
                        'createdAtTo' => ['type' => 'string', 'format' => 'date-time'],
                        'expiresAtFrom' => ['type' => 'string', 'format' => 'date-time'],
                        'expiresAtTo' => ['type' => 'string', 'format' => 'date-time'],
                        'terminalId' => ['type' => 'string'],
                    ],
                ],
                'RtpTestAcceptDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'amount' => ['type' => 'number', 'required' => true],
                        'currency' => ['type' => 'string', 'enum' => ['MDL'], 'required' => true],
                    ],
                ],
            ]
        ];

        parent::__construct($description, $options);
    }
}
