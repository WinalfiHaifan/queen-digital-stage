<?php
declare(strict_types=1);

final class OrderProblem extends RuntimeException {}

const ORDER_STATUS_LABELS = [
    'pending' => 'Pending', 'processing' => 'Processing', 'shipped' => 'Shipped',
    'completed' => 'Delivered and paid', 'cancelled' => 'Cancelled',
];

const SHIPPING_FIELDS = [
    'buyer_name' => ['Full name', 100, 'name'],
    'buyer_phone' => ['Phone number', 32, 'tel'],
    'shipping_address' => ['Street address', 500, 'street-address'],
    'shipping_city' => ['City or regency', 100, 'address-level2'],
    'shipping_province' => ['Province or state', 100, 'address-level1'],
    'postal_code' => ['Postal code', 12, 'postal-code'],
];

function checkoutCart(string $json): array
{
    if (strlen($json) > 24000) {
        throw new OrderProblem('This cart is too large.');
    }
    try {
        $cart = json_decode($json, true, 4, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        throw new OrderProblem('The cart could not be read. Return to the Store and try again.');
    }
    if (!is_array($cart) || !$cart || count($cart) > 50) {
        throw new OrderProblem('Add products to your cart before checking out.');
    }
    $units = 0;
    foreach ($cart as $id => $variants) {
        if (!preg_match('/\A[1-9][0-9]{0,9}\z/', (string) $id) || (int) $id > 4294967295 || !is_array($variants) || !$variants || count($variants) > 5) {
            throw new OrderProblem('The cart contains an invalid product.');
        }
        foreach ($variants as $size => $quantity) {
            if (!in_array($size, ['standard', 'S', 'M', 'L', 'XL'], true) || !is_int($quantity) || $quantity < 1 || $quantity > 1000) {
                throw new OrderProblem('Choose a valid size and quantity for each product.');
            }
            $units += $quantity;
        }
    }
    if ($units > 1000) {
        throw new OrderProblem('Each order can contain up to 1,000 items.');
    }
    ksort($cart, SORT_NUMERIC);
    return $cart;
}

function orderPriceCents(string $price): int
{
    [$whole, $fraction] = explode('.', $price, 2);
    return (int) $whole * 100 + (int) $fraction;
}

function orderDecimal(int $cents): string
{
    return intdiv($cents, 100) . '.' . str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
}

function checkoutQuote(PDO $db, array $cart, bool $lockProducts = false): array
{
    $placeholders = implode(', ', array_fill(0, count($cart), '?'));
    $query = "SELECT p.product_id, p.product_name, p.price, p.currency, p.stock, p.is_active, c.category_name
              FROM products p JOIN product_categories c ON c.category_id = p.category_id
              WHERE p.product_id IN ($placeholders) ORDER BY p.product_id";
    $rows = contentRows($db, $query . ($lockProducts ? ' FOR UPDATE' : ''), array_keys($cart));
    if (count($rows) !== count($cart)) {
        throw new OrderProblem('A product in your cart is no longer available. Review your cart in the Store.');
    }
    $lines = [];
    $currency = $rows[0]['currency'];
    $total = 0;
    foreach ($rows as $product) {
        $id = (int) $product['product_id'];
        $variants = $cart[$id];
        $name = $product['product_name'];
        if (!(int) $product['is_active'] || array_sum($variants) > (int) $product['stock']) {
            throw new OrderProblem("Availability changed for $name. Review your cart in the Store.");
        }
        if ($currency !== $product['currency']) {
            throw new OrderProblem('Place a separate order for each currency. Review your cart in the Store.');
        }
        $allowed = productSizes($product) ?: ['standard'];
        $price = orderPriceCents($product['price']);
        foreach ($variants as $size => $quantity) {
            if (!in_array($size, $allowed, true)) {
                throw new OrderProblem("A selected size for $name is no longer available. Review your cart in the Store.");
            }
            $lines[] = ['product_id' => $id, 'product_name' => $name, 'size' => $size, 'quantity' => $quantity, 'unit_cents' => $price];
            $total += $price * $quantity;
        }
    }
    return ['lines' => $lines, 'currency' => $currency, 'total_cents' => $total];
}

function checkoutShipping(): array
{
    $values = [];
    $errors = [];
    foreach (SHIPPING_FIELDS as $key => [$label, $limit]) {
        $value = postText($key);
        if ($value === '' || str_contains($value, "\0") || !mb_check_encoding($value, 'UTF-8') || mb_strlen($value, 'UTF-8') > $limit) {
            $errors[] = "Enter a valid $label.";
        }
        $values[$key] = mb_substr($value, 0, $limit, 'UTF-8');
    }
    $phone = $values['buyer_phone'];
    $digits = preg_replace('/[^0-9]/', '', $phone);
    if ($phone !== '' && (!preg_match('/\A\+?[0-9 ().-]+\z/', $phone) || strlen($digits) < 7 || strlen($digits) > 15)) {
        $errors[] = 'Use a phone number with 7 to 15 digits.';
    }
    $postalCode = $values['postal_code'];
    if ($postalCode !== '' && !preg_match('/\A[A-Za-z0-9][A-Za-z0-9 -]{1,10}[A-Za-z0-9]\z/', $postalCode)) {
        $errors[] = 'Enter a postal code with 3 to 12 letters or digits.';
    }
    return [$values, array_values(array_unique($errors))];
}

function placeCheckoutOrder(PDO $db, string $token, array $review, array $shipping): array
{
    $db->beginTransaction();
    try {
        $existing = contentRows($db, 'SELECT order_id FROM orders WHERE request_token = ?', [$token]);
        if ($existing) {
            $db->commit();
            return ['order_id' => (int) $existing[0]['order_id']];
        }
        $current = checkoutQuote($db, $review['cart'], true);
        if ($current !== $review['quote']) {
            $db->rollBack();
            return ['quote' => $current];
        }

        $insertOrder = $db->prepare('INSERT INTO orders (order_number, request_token, buyer_name, buyer_phone, shipping_address, shipping_city, shipping_province, postal_code, currency, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $insertOrder->execute(['QS-' . strtoupper(bin2hex(random_bytes(8))), $token, ...array_values($shipping), $current['currency'], orderDecimal($current['total_cents'])]);
        $id = (int) $db->lastInsertId();
        $insertLine = $db->prepare('INSERT INTO order_items (order_id, product_id, product_name, size, quantity, unit_price) VALUES (?, ?, ?, ?, ?, ?)');
        foreach ($current['lines'] as $line) {
            $insertLine->execute([$id, $line['product_id'], $line['product_name'], $line['size'], $line['quantity'], orderDecimal($line['unit_cents'])]);
        }
        $subtract = $db->prepare('UPDATE products SET stock = stock - ? WHERE product_id = ? AND stock >= ? AND is_active = 1');
        foreach ($review['cart'] as $productId => $variants) {
            $quantity = array_sum($variants);
            $subtract->execute([$quantity, $productId, $quantity]);
            if ($subtract->rowCount() !== 1) {
                throw new OrderProblem('Stock changed before the order was saved. Review your cart in the Store.');
            }
        }
        $db->commit();
        return ['order_id' => $id];
    } catch (Throwable $error) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        if ($error instanceof PDOException && $error->getCode() === '23000') {
            $existing = contentRows($db, 'SELECT order_id FROM orders WHERE request_token = ?', [$token]);
            if ($existing) {
                return ['order_id' => (int) $existing[0]['order_id']];
            }
        }
        throw $error;
    }
}

function orderStatusOptions(string $status): array
{
    return match ($status) {
        'pending' => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped' => ['completed'],
        default => [],
    };
}

function changeOrderStatus(PDO $db, int $id, string $status): string
{
    $db->beginTransaction();
    try {
        $current = contentRows($db, 'SELECT status FROM orders WHERE order_id = ? FOR UPDATE', [$id])[0]['status'] ?? null;
        if ($current === $status) {
            $db->commit();
            return 'This order already has that status.';
        }
        if ($current === null || !in_array($status, orderStatusOptions($current), true)) {
            throw new OrderProblem('This status change is no longer available.');
        }
        if ($status === 'cancelled') {
            $quantities = contentRows($db, 'SELECT product_id, SUM(quantity) AS units FROM order_items WHERE order_id = ? GROUP BY product_id ORDER BY product_id', [$id]);
            $restore = $db->prepare('UPDATE products SET stock = stock + ? WHERE product_id = ? AND stock <= 4294967295 - ?');
            foreach ($quantities as $row) {
                $restore->execute([$row['units'], $row['product_id'], $row['units']]);
                if ($restore->rowCount() !== 1) {
                    throw new OrderProblem('Stock cannot be restored. Review the product inventory before cancelling.');
                }
            }
        }
        $db->prepare('UPDATE orders SET status = ? WHERE order_id = ?')->execute([$status, $id]);
        $db->commit();
        return $status === 'cancelled' ? 'Order cancelled and stock restored.' : 'Order status updated.';
    } catch (Throwable $error) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $error;
    }
}
