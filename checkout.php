<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/content.php';
require __DIR__ . '/app/orders.php';

startSession();
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store');
header('Referrer-Policy: same-origin');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

$reviews = array_filter($_SESSION['checkout_reviews'] ?? [], static fn(array $entry): bool => $entry['expires_at'] > time());
$_SESSION['checkout_reviews'] = $reviews;
$confirmed = $_SESSION['checkout_confirmed'] ?? [];
$token = $_SERVER['REQUEST_METHOD'] === 'POST' ? postText('review') : queryText('review');
$token = preg_match('/\A[a-f0-9]{32}\z/', $token) ? $token : '';
$review = $reviews[$token] ?? null;
$receipt = null;
$values = [];
$errors = [];

try {
    $db = database();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        requireCsrf();
        if (postText('action') === 'review') {
            $cart = checkoutCart(postText('cart'));
            $quote = checkoutQuote($db, $cart);
            $token = bin2hex(random_bytes(16));
            $reviews[$token] = ['cart' => $cart, 'quote' => $quote, 'expires_at' => time() + 1800];
            $_SESSION['checkout_reviews'] = array_slice($reviews, -8, null, true);
            redirect('checkout.php?review=' . $token);
        }
        if (postText('action') !== 'place') {
            throw new OrderProblem('Return to the Store to begin checkout.');
        }
        if (isset($confirmed[$token])) {
            redirect('checkout.php?review=' . $token);
        }
        if (!$review) {
            throw new OrderProblem('Your order summary has expired. Open your cart in the Store and try again.');
        }
        [$values, $errors] = checkoutShipping();
        if (!$errors) {
            $result = placeCheckoutOrder($db, $token, $review, $values);
            if (isset($result['order_id'])) {
                $confirmed[$token] = ['order_id' => $result['order_id'], 'cart' => $review['cart']];
                $_SESSION['checkout_confirmed'] = array_slice($confirmed, -8, null, true);
                unset($_SESSION['checkout_reviews'][$token]);
                redirect('checkout.php?review=' . $token);
            }
            $review['quote'] = $result['quote'];
            $_SESSION['checkout_reviews'][$token] = $review;
            $errors[] = 'The order summary changed. Check the updated total before placing your order.';
        }
        http_response_code(422);
    } elseif (isset($confirmed[$token])) {
        $receipt = contentRows($db, 'SELECT order_number, currency, total_amount, status, buyer_name, shipping_address, shipping_city, shipping_province, postal_code FROM orders WHERE order_id = ?', [$confirmed[$token]['order_id']])[0] ?? null;
    }
} catch (OrderProblem $problem) {
    http_response_code(422);
    $errors[] = $problem->getMessage();
} catch (PDOException $error) {
    $retryable = $error->getCode() === '40001' || (int) ($error->errorInfo[1] ?? 0) === 1205;
    if (!$retryable) {
        unavailable($error);
    }
    http_response_code(409);
    $errors[] = 'Inventory is being updated. Try placing your order again.';
}

require __DIR__ . '/app/views/checkout.php';
