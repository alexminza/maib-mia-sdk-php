<?php

declare(strict_types=1);

namespace Maib\MaibMia;

use GuzzleHttp\Command\Guzzle\Description;
use Composer\InstalledVersions;

/**
 * maib MIA API service description
 *
 * @link https://docs.maibmerchants.md/mia-qr-api
 * @link https://docs.maibmerchants.md/request-to-pay
 */
class MaibMiaDescription extends Description
{
    private const PACKAGE_NAME    = 'alexminza/maib-mia-sdk';
    private const DEFAULT_VERSION = 'dev';

    private static function detectVersion(): string
    {
        if (!class_exists(InstalledVersions::class)) {
            return self::DEFAULT_VERSION;
        }

        if (!InstalledVersions::isInstalled(self::PACKAGE_NAME)) {
            return self::DEFAULT_VERSION;
        }

        return InstalledVersions::getPrettyVersion(self::PACKAGE_NAME)
            ?? self::DEFAULT_VERSION;
    }

    public function __construct(array $options = [])
    {
        $version   = self::detectVersion();
        $userAgent = "maib-mia-sdk-php/$version";

        $authorizationHeader = [
            'type' => 'string',
            'location' => 'header',
            'sentAs' => 'Authorization',
            'description' => 'Bearer Authentication with JWT Token',
            'required' => true,
        ];

        $models = [
            #region Generic Models
            'getResponse' => [
                'type' => 'object',
                'additionalProperties' => [
                    'location' => 'json'
                ]
            ],
            #endregion

            #region Schema-based Models
            // https://docs.maibmerchants.md/mia-qr-api/en/endpoints/authentication/obtain-authentication-token#request-parameters-body
            'AuthTokenDto' => [
                'type' => 'object',
                'properties' => [
                    'clientId' => ['type' => 'string', 'required' => true],
                    'clientSecret' => ['type' => 'string', 'required' => true],
                ],
            ],
            // https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-initiation/create-qr-code-static-dynamic#request-parameters-body
            'QrCreateDto' => [
                'type' => 'object',
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
            // https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-initiation/create-hybrid-qr-code#request-body-parameters
            'QrCreateHybridDto' => [
                'type' => 'object',
                'properties' => [
                    'amountType' => ['type' => 'string', 'enum' => ['Fixed', 'Controlled', 'Free'], 'required' => true],
                    'currency' => ['type' => 'string', 'enum' => ['MDL'], 'required' => true],
                    'terminalId' => ['type' => 'string'],
                    'extension' => ['$ref' => 'QrCreateExtensionDto'],
                ],
            ],
            // https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-initiation/create-hybrid-qr-code/create-extension-for-qr-code-by-id#request-parameters-body
            'QrCreateExtensionDto' => [
                'type' => 'object',
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
            // https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-cancellation/cancel-active-qr-static-dynamic#requests-parameters-body
            // https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-cancellation/cancel-active-qr-extension-hybrid#request-parameters-body
            'CancelDto' => [
                'type' => 'object',
                'properties' => [
                    'reason' => ['type' => 'string', 'required' => true],
                ],
            ],
            // https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-refund/refund-completed-payment#request-parameters
            'RefundDto' => [
                'type' => 'object',
                'properties' => [
                    'amount' => ['type' => 'number'],
                    'reason' => ['type' => 'string', 'required' => true],
                    'callbackUrl' => ['type' => 'string'],
                ],
            ],
            // https://docs.maibmerchants.md/mia-qr-api/en/endpoints/information-retrieval-get/display-list-of-qr-codes-with-filtering-options#request-parameters-query
            'QrListDto' => [
                'type' => 'object',
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
            // https://docs.maibmerchants.md/mia-qr-api/en/endpoints/information-retrieval-get/retrieve-list-of-payments-with-filtering-options
            'PaymentListDto' => [
                'type' => 'object',
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
            // https://docs.maibmerchants.md/mia-qr-api/en/payment-simulation-sandbox#request-parameters-body-json
            'TestPayDto' => [
                'type' => 'object',
                'properties' => [
                    'qrId' => ['type' => 'string', 'required' => true],
                    'amount' => ['type' => 'number', 'required' => true],
                    'iban' => ['type' => 'string', 'required' => true],
                    'currency' => ['type' => 'string', 'enum' => ['MDL'], 'required' => true],
                    'payerName' => ['type' => 'string', 'required' => true],
                ],
            ],
            // https://docs.maibmerchants.md/request-to-pay/api-reference/endpoints/create-a-new-payment-request-rtp#request-body-parameters
            'RtpCreateDto' => [
                'type' => 'object',
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
            // https://docs.maibmerchants.md/request-to-pay/api-reference/endpoints/list-all-payment-requests#query-parameters
            'RtpListDto' => [
                'type' => 'object',
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
            // https://docs.maibmerchants.md/request-to-pay/api-reference/sandbox-simulation-environment/simulate-acceptance-of-a-payment-request#request-body-parameters
            'RtpTestAcceptDto' => [
                'type' => 'object',
                'properties' => [
                    'amount' => ['type' => 'number', 'required' => true],
                    'currency' => ['type' => 'string', 'enum' => ['MDL'], 'required' => true],
                ],
            ],
            #endregion
        ];

        $description = [
            'name' => 'maib MIA API',
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

                #region Authentication Operations
                // https://docs.maibmerchants.md/mia-qr-api/en/endpoints/authentication/obtain-authentication-token
                'getToken' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/auth/token',
                    'summary' => 'Obtain Authentication Token',
                    'responseModel' => 'getResponse',
                    'parameters' => self::getProperties($models, 'AuthTokenDto'),
                ],
                #endregion

                #region QR Operations
                // https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-initiation/create-qr-code-static-dynamic
                'qrCreate' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/mia/qr',
                    'summary' => 'Create QR Code (Static, Dynamic)',
                    'responseModel' => 'getResponse',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                    ], self::getProperties($models, 'QrCreateDto')),
                ],
                // https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-initiation/create-hybrid-qr-code
                'qrCreateHybrid' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/mia/qr/hybrid',
                    'summary' => 'Create Hybrid QR Code',
                    'responseModel' => 'getResponse',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                    ], self::getProperties($models, 'QrCreateHybridDto')),
                ],
                // https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-initiation/create-hybrid-qr-code/create-extension-for-qr-code-by-id
                'qrCreateExtension' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/mia/qr/{qrId}/extension',
                    'summary' => 'Create Extension for QR Code by ID',
                    'responseModel' => 'getResponse',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                        'qrId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ], self::getProperties($models, 'QrCreateExtensionDto')),
                ],
                // https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-cancellation/cancel-active-qr-static-dynamic
                'qrCancel' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/mia/qr/{qrId}/cancel',
                    'summary' => 'Cancel Active QR (Static, Dynamic)',
                    'responseModel' => 'getResponse',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                        'qrId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ], self::getProperties($models, 'CancelDto')),
                ],
                // https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-cancellation/cancel-active-qr-extension-hybrid
                'qrCancelExtension' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/mia/qr/{qrId}/extension/cancel',
                    'summary' => 'Cancel Active QR Extension (Hybrid)',
                    'responseModel' => 'getResponse',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                        'qrId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ], self::getProperties($models, 'CancelDto')),
                ],
                #endregion

                #region Payment Operations
                // https://docs.maibmerchants.md/mia-qr-api/en/endpoints/payment-refund/refund-completed-payment
                'paymentRefund' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/payments/{payId}/refund',
                    'summary' => 'Refund Completed Payment',
                    'responseModel' => 'getResponse',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                        'payId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ], self::getProperties($models, 'RefundDto')),
                ],
                #endregion

                #region Information Retrieval Operations
                // https://docs.maibmerchants.md/mia-qr-api/en/endpoints/information-retrieval-get/display-list-of-qr-codes-with-filtering-options
                'qrList' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/v2/mia/qr',
                    'summary' => 'Display List of QR Codes with Filtering Options',
                    'responseModel' => 'getResponse',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                    ], self::getProperties($models, 'QrListDto', 'query')),
                ],
                // https://docs.maibmerchants.md/mia-qr-api/en/endpoints/information-retrieval-get/retrieve-qr-details-by-id
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
                // https://docs.maibmerchants.md/mia-qr-api/en/endpoints/information-retrieval-get/retrieve-list-of-payments-with-filtering-options
                'paymentList' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/v2/mia/payments',
                    'summary' => 'Retrieve List of Payments with Filtering Options',
                    'responseModel' => 'getResponse',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                    ], self::getProperties($models, 'PaymentListDto', 'query')),
                ],
                // https://docs.maibmerchants.md/mia-qr-api/en/endpoints/information-retrieval-get/retrieve-payment-details-by-id
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
                #endregion

                #region Payment Simulation Operations
                // https://docs.maibmerchants.md/mia-qr-api/en/payment-simulation-sandbox
                'testPay' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/mia/test-pay',
                    'summary' => 'Payment Simulation (Sandbox)',
                    'responseModel' => 'getResponse',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                    ], self::getProperties($models, 'TestPayDto')),
                ],
                #endregion

                #region RTP Operations
                // https://docs.maibmerchants.md/request-to-pay/api-reference/endpoints/create-a-new-payment-request-rtp
                'rtpCreate' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/rtp',
                    'summary' => 'Create a new payment request (RTP)',
                    'responseModel' => 'getResponse',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                    ], self::getProperties($models, 'RtpCreateDto')),
                ],
                // https://docs.maibmerchants.md/request-to-pay/api-reference/endpoints/retrieve-the-status-of-a-payment-request
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
                // https://docs.maibmerchants.md/request-to-pay/api-reference/endpoints/cancel-a-pending-payment-request
                'rtpCancel' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/rtp/{rtpId}/cancel',
                    'summary' => 'Cancel a pending payment request',
                    'responseModel' => 'getResponse',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                        'rtpId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ], self::getProperties($models, 'CancelDto')),
                ],
                // https://docs.maibmerchants.md/request-to-pay/api-reference/endpoints/list-all-payment-requests
                'rtpList' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/v2/rtp',
                    'summary' => 'List all payment requests',
                    'responseModel' => 'getResponse',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                    ], self::getProperties($models, 'RtpListDto', 'query')),
                ],
                // https://docs.maibmerchants.md/request-to-pay/api-reference/endpoints/initiate-a-refund-for-a-completed-payment
                'rtpRefund' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/rtp/{payId}/refund',
                    'summary' => 'Initiate a refund for a completed payment',
                    'responseModel' => 'getResponse',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                        'payId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ], self::getProperties($models, 'CancelDto')),
                ],
                #endregion

                #region RTP Simulation Operations (Sandbox)
                // https://docs.maibmerchants.md/request-to-pay/api-reference/sandbox-simulation-environment/simulate-acceptance-of-a-payment-request
                'rtpTestAccept' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/rtp/{rtpId}/test-accept',
                    'summary' => 'Simulate acceptance of a payment request',
                    'responseModel' => 'getResponse',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                        'rtpId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ], self::getProperties($models, 'RtpTestAcceptDto')),
                ],
                // https://docs.maibmerchants.md/request-to-pay/api-reference/sandbox-simulation-environment/simulate-rejection-of-a-payment-request
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
                #endregion
            ],

            'models' => $models
        ];

        parent::__construct($description, $options);
    }

    /**
     * Get property definitions from a model and inject a specific location.
     */
    private static function getProperties(array $models, string $modelName, string $location = 'json'): array
    {
        $props  = $models[$modelName]['properties'] ?? [];
        $result = [];

        foreach ($props as $name => $prop) {
            $prop['location'] = $location;
            $result[$name]    = $prop;
        }

        return $result;
    }
}
