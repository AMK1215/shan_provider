<?php

namespace App\Http\Controllers\Api\V1\Webhook;

use App\Enums\SeamlessWalletCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Slot\SlotWebhookRequest;
use App\Services\Slot\BalanceResponseService; // Ensure this enum exists and is correctly defined
use Exception;
use Illuminate\Support\Facades\Config; // Import the Exception class for better clarity
use Illuminate\Support\Facades\Log;

class GetBalanceController extends Controller
{
    /**
     * Handle the balance request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBalance(SlotWebhookRequest $request)
    {
        // Log incoming balance request details for debugging
        Log::debug('Balance request received', [
            'operator_code' => $request->getOperatorCode(),
            'batch_count' => count($request->getBatchRequests()),
            'request_time' => $request->getRequestTime(),
            'ip' => $request->ip(),
        ]);

        // Verify signature first to ensure request authenticity
        if (! $this->verifySignature($request)) {
            // Log warning for invalid signature
            Log::warning('Invalid signature for balance request', [
                'operator_code' => $request->getOperatorCode(),
                'received_sign' => $request->getSign(),
                'expected_sign' => $this->generateExpectedSign($request),
                'ip' => $request->ip(),
                'request_data' => $request->all(), // Log all request data for debugging invalid signatures
            ]);

            // Return a global error response for invalid signature
            return response()->json(
                BalanceResponseService::buildGlobalErrorResponse(
                    SeamlessWalletCode::InvalidSignature, // Use the enum for the error code
                    'Invalid request signature'
                ),
                401 // HTTP status code for unauthorized
            );
        }

        Log::debug('Signature verification passed');

        // Initialize an array to hold responses for each batch request
        $responseData = [];

        // Process each individual request within the batch
        foreach ($request->getBatchRequests() as $batchRequest) {
            $memberAccount = $batchRequest['member_account'];
            $productCode = $batchRequest['product_code'];

            // Log details for the current member balance request being processed
            Log::debug('Processing member balance request', [
                'member_account' => $memberAccount,
                'product_code' => $productCode,
            ]);

            try {
                // Attempt to retrieve the member from the database using the request's helper method
                $member = $request->getMember($memberAccount);

                // If member is not found, log a warning and build an error response for this member
                if (! $member) {
                    Log::warning('Member not found', ['member_account' => $memberAccount]);

                    $responseData[] = BalanceResponseService::buildMemberErrorResponse(
                        $memberAccount,
                        $productCode,
                        SeamlessWalletCode::MemberNotExist, // Use the enum for the error code
                        'Member account not found'
                    );

                    continue; // Move to the next batch request
                }

                // Retrieve the member's balance. This assumes the User model has a 'balance' attribute.
                $balance = $member->balance;

                // Log the retrieved balance
                Log::debug('Member balance retrieved', [
                    'member_account' => $memberAccount,
                    'balance' => $balance,
                ]);

                // Build a successful response for the current member
                $responseData[] = [
                    'member_account' => $memberAccount,
                    'product_code' => $productCode,
                    'balance' => $balance,
                    'code' => SeamlessWalletCode::Success->value, // Use the enum value for success code
                    'message' => '', // Empty message for success
                ];

            } catch (Exception $e) { // Catch any general exceptions during processing
                // Log the error details including stack trace
                Log::error('Error processing balance request', [
                    'member_account' => $memberAccount,
                    'error' => $e->getMessage(),
                    'stack_trace' => $e->getTraceAsString(),
                ]);

                // Build an internal server error response for the current member
                $responseData[] = BalanceResponseService::buildMemberErrorResponse(
                    $memberAccount,
                    $productCode,
                    SeamlessWalletCode::InternalServerError, // Use the enum for internal server error
                    'Internal server error'
                );
            }
        }

        // Log summary of processed balance requests
        Log::debug('Balance request processed', [
            'operator_code' => $request->getOperatorCode(),
            'success_count' => count(array_filter($responseData, fn ($item) => $item['code'] === SeamlessWalletCode::Success->value)),
            'error_count' => count(array_filter($responseData, fn ($item) => $item['code'] !== SeamlessWalletCode::Success->value)),
        ]);

        // Return the final JSON response containing all batch results
        return response()->json(
            BalanceResponseService::buildSuccessResponse($responseData)
        );
    }

    /**
     * Verify the request signature.
     */
    private function verifySignature(SlotWebhookRequest $request): bool
    {
        $expectedSign = $this->generateExpectedSign($request);

        // Use hash_equals to prevent timing attacks when comparing hashes
        return hash_equals($expectedSign, $request->getSign());
    }

    /**
     * Generate the expected signature for verification.
     */
    private function generateExpectedSign(SlotWebhookRequest $request): string
    {
        // Retrieve the secret key from Laravel's configuration
        $secretKey = Config::get('seamless_key.secret_key');

        // Define $signString by concatenating the components using getter methods for consistency
        $signString = md5(
            $request->operator_code.
            $request->request_time.
            'getbalance'.
            $secretKey
        );

        // Log the components used for signature generation (masking the full secret key)
        Log::debug('Generating signature for verification', [
            'components' => [
                'operator_code' => $request->getOperatorCode(),
                'request_time' => $request->getRequestTime(),
                'method_name' => $request->getMethodName(),
                'secret_key' => '***'.substr($secretKey, -4), // Log only last 4 chars for security
            ],
            // Log the full string with masked secret key for easier debugging of the concatenation
            'full_string' => $request->getOperatorCode().$request->getRequestTime().$request->getMethodName().'***'.substr($secretKey, -4),
            'md5_result' => md5($signString), // Log the MD5 result for comparison
        ]);

        // Return the MD5 hash of the concatenated string
        return md5($signString);
    }
}
