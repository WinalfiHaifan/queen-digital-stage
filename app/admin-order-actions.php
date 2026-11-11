<?php
declare(strict_types=1);

function adminOrderTransaction(PDO $db, callable $change): void
{
    $db->beginTransaction();
    try {
        $change();
        $db->commit();
    } catch (Throwable $error) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        if ($error instanceof PDOException && $error->getCode() === '23000') {
            throw new OrderProblem('This product and size already appear in the order.');
        }
        if ($error instanceof PDOException && ($error->getCode() === '40001' || (int) ($error->errorInfo[1] ?? 0) === 1205)) {
            throw new OrderProblem('Inventory is being updated. Please try again.');
        }
        throw $error;
    }
}

function lockAdminOrder(PDO $db, int $orderId): array
{
    $order = contentRows($db, 'SELECT * FROM orders WHERE order_id = ? FOR UPDATE', [$orderId])[0] ?? null;
    if (!$order) {
        throw new OrderProblem('The order no longer exists.');
    }
    return $order;
}

function adminOrderProductInput(): array
{
    $productId = recordId(postText('product_id'));
    $size = postText('size');
    $quantity = recordId(postText('quantity'));
    if (!$productId || !in_array($size, ['standard', 'S', 'M', 'L', 'XL'], true) || $quantity < 1 || $quantity > 1000) {
        throw new OrderProblem('Choose a product, a valid size, and a quantity between 1 and 1,000.');
    }
    return [$productId, $size, $quantity];
}

function saveAdminOrder(PDO $db, int $id, array $shipping): int
{
    if (!$id) {
        [$productId, $size, $quantity] = adminOrderProductInput();
        $cart = [$productId => [$size => $quantity]];
        $review = ['cart' => $cart, 'quote' => checkoutQuote($db, $cart)];
        $result = placeCheckoutOrder($db, bin2hex(random_bytes(16)), $review, $shipping);
        if (!isset($result['order_id'])) {
            throw new OrderProblem('Price or availability changed. Review the product and try again.');
        }
        return $result['order_id'];
    }
    adminOrderTransaction($db, function () use ($db, $id, $shipping): void {
        if (!in_array(lockAdminOrder($db, $id)['status'], ['pending', 'processing'], true)) {
            throw new OrderProblem('Shipping details can only be edited before dispatch.');
        }
        $db->prepare('UPDATE orders SET buyer_name = ?, buyer_phone = ?, shipping_address = ?, shipping_city = ?, shipping_province = ?, postal_code = ? WHERE order_id = ?')
            ->execute([...array_values($shipping), $id]);
    });
    return $id;
}

function deleteAdminOrder(PDO $db, int $orderId): void
{
    adminOrderTransaction($db, function () use ($db, $orderId): void {
        if (lockAdminOrder($db, $orderId)['status'] !== 'cancelled') {
            throw new OrderProblem('Only cancelled orders can be deleted.');
        }
        $db->prepare('DELETE FROM order_items WHERE order_id = ?')->execute([$orderId]);
        $db->prepare('DELETE FROM orders WHERE order_id = ?')->execute([$orderId]);
    });
}

function changeAdminOrderItem(PDO $db, int $orderId, int $itemId, ?array $input): void
{
    adminOrderTransaction($db, function () use ($db, $orderId, $itemId, $input): void {
        $order = lockAdminOrder($db, $orderId);
        if ($order['status'] !== 'pending') {
            throw new OrderProblem('Items can only be changed while the order is pending.');
        }
        $previous = $itemId ? contentRows($db, 'SELECT * FROM order_items WHERE order_item_id = ? AND order_id = ?', [$itemId, $orderId])[0] ?? null : null;
        if ($itemId && !$previous) {
            throw new OrderProblem('This item no longer belongs to the order.');
        }
        $units = (int) contentRows($db, 'SELECT COALESCE(SUM(quantity), 0) AS units FROM order_items WHERE order_id = ?', [$orderId])[0]['units'];
        [$productId, $size, $quantity] = $input ?? [0, '', 0];
        $newUnits = $units - (int) ($previous['quantity'] ?? 0) + $quantity;
        if ($newUnits < 1 || $newUnits > 1000) {
            throw new OrderProblem('Keep between 1 and 1,000 units per order. Cancel the order to remove its last item.');
        }

        $stockChanges = [];
        if ($previous) {
            $stockChanges[(int) $previous['product_id']] = (int) $previous['quantity'];
        }
        if ($input) {
            $stockChanges[$productId] = ($stockChanges[$productId] ?? 0) - $quantity;
        }
        ksort($stockChanges, SORT_NUMERIC);
        $placeholders = implode(', ', array_fill(0, count($stockChanges), '?'));
        $products = contentRows($db, "SELECT p.product_id, p.product_name, p.stock, p.currency, p.price, p.is_active, c.category_name
            FROM products p JOIN product_categories c ON c.category_id = p.category_id
            WHERE p.product_id IN ($placeholders) ORDER BY p.product_id FOR UPDATE", array_keys($stockChanges));
        $catalog = array_column($products, null, 'product_id');
        if (count($catalog) !== count($stockChanges)) {
            throw new OrderProblem('One of these products is no longer available.');
        }
        if ($input) {
            $product = $catalog[$productId];
            $sameProduct = $previous && (int) $previous['product_id'] === $productId;
            $sameSize = $sameProduct && $previous['size'] === $size;
            if ((!$sameProduct || $stockChanges[$productId] < 0) && (!(int) $product['is_active'] || $order['currency'] !== $product['currency'])) {
                throw new OrderProblem('Use an active product with the same currency as the order.');
            }
            if (!$sameSize && !in_array($size, productSizes($product) ?: ['standard'], true)) {
                throw new OrderProblem('Choose a size that is available for this product.');
            }
            $line = [$productId, $sameProduct ? $previous['product_name'] : $product['product_name'], $size, $quantity,
                $sameProduct ? $previous['unit_price'] : $product['price']];
            if ($itemId) {
                $db->prepare('UPDATE order_items SET product_id = ?, product_name = ?, size = ?, quantity = ?, unit_price = ? WHERE order_item_id = ?')
                    ->execute([...$line, $itemId]);
            } else {
                $db->prepare('INSERT INTO order_items (product_id, product_name, size, quantity, unit_price, order_id) VALUES (?, ?, ?, ?, ?, ?)')
                    ->execute([...$line, $orderId]);
            }
        } else {
            $db->prepare('DELETE FROM order_items WHERE order_item_id = ? AND order_id = ?')->execute([$itemId, $orderId]);
        }
        $update = $db->prepare('UPDATE products SET stock = ? WHERE product_id = ?');
        foreach ($stockChanges as $id => $delta) {
            $stock = (int) $catalog[$id]['stock'] + $delta;
            if ($stock < 0 || $stock > 4294967295) {
                throw new OrderProblem('There is not enough stock to make this change.');
            }
            if ($delta) {
                $update->execute([$stock, $id]);
            }
        }
        $db->prepare('UPDATE orders SET total_amount = (SELECT SUM(quantity * unit_price) FROM order_items WHERE order_id = ?) WHERE order_id = ?')
            ->execute([$orderId, $orderId]);
    });
}
