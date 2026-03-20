<?php 
if(!isset($_POST['amount'])) return;
$stripeSecret = getenv('STRIPE_SECRET');
\Stripe\Stripe::setApiKey($stripeSecret);

$session = \Stripe\Checkout\Session::create([
  'payment_method_types' => ['card'],
  'line_items' => [[
    'price_data' => [
      'currency' => 'usd',
      'product_data' => [
        'name' => 'Premium Course',
      ],
      'unit_amount' => $_POST['amount'],
    ],
    'quantity' => 1,
  ]],
  'mode' => 'payment',
  'success_url' => 'https://yourapp.com/success',
  'cancel_url' => 'https://yourapp.com/cancel',
]);

echo json_encode(['url' => $session->url]);