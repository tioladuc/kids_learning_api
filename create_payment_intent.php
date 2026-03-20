<?php
require 'vendor/autoload.php';
$stripeSecret = getenv('STRIPE_SECRET');
\Stripe\Stripe::setApiKey($stripeSecret);

header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);
$amount = $input['amount']; // in cents

try {
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $amount,
        'currency' => 'usd',
        'automatic_payment_methods' => ['enabled' => true],
    ]);

    echo json_encode([
        'clientSecret' => $paymentIntent->client_secret
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}