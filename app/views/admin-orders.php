<?php if ($editing && (!$orderId || $order)): ?>
    <?php require __DIR__ . '/admin-order-form.php'; ?>
<?php else: ?>
<p class="eyebrow">Store fulfillment</p>
<?php if ($errors && !$itemEditing): ?><ul class="admin-alert" role="alert"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul><?php endif; ?>
<?php if ($orderId): ?>
    <div class="admin-row"><h1 class="admin-order-number"><?= e($order['order_number'] ?? 'Order unavailable') ?></h1><a class="btn btn-secondary" href="<?= e(adminUrl('orders')) ?>">Back to orders</a></div>
    <?php if ($order): ?>
    <div class="admin-order-layout">
        <section class="admin-card admin-note" aria-labelledby="shipping-title">
            <h2 id="shipping-title">Shipping details</h2>
            <p><strong><?= e($order['buyer_name']) ?></strong><br><?= e($order['buyer_phone']) ?></p>
            <p class="admin-address"><?= e($order['shipping_address']) ?></p>
            <p><?= e(implode(', ', [$order['shipping_city'], $order['shipping_province'], $order['postal_code']])) ?></p>
            <?php if (in_array($order['status'], ['pending', 'processing'], true)): ?>
                <a class="btn btn-outline-primary" href="<?= e(adminUrl('orders', ['order' => $orderId, 'edit' => $orderId])) ?>">Edit shipping details</a>
            <?php endif; ?>
        </section>
        <section class="admin-card" aria-labelledby="status-title">
            <div class="admin-row"><h2 id="status-title">Order status</h2><span class="admin-status <?= $order['status'] === 'completed' ? 'status-live' : '' ?>"><?= e(ORDER_STATUS_LABELS[$order['status']]) ?></span></div>
            <p class="admin-muted">Placed <?= e($order['created_at']) ?></p>
            <p>Cash on delivery · <?= e($order['currency'] . ' ' . $order['total_amount']) ?></p>
            <?php if ($next = orderStatusOptions($order['status'])): ?>
            <form class="admin-form" method="post" action="<?= e(adminUrl('orders', ['order' => $orderId])) ?>">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="order-status">
                <input type="hidden" name="order_id" value="<?= e($orderId) ?>">
                <label for="order-status">Update status
                    <select class="form-select" name="status" id="order-status" required>
                        <option value="">Choose next status</option>
                        <?php foreach ($next as $state): ?><option value="<?= e($state) ?>"><?= e(ORDER_STATUS_LABELS[$state]) ?></option><?php endforeach; ?>
                    </select>
                </label>
                <?php if (in_array('cancelled', $next, true)): ?><small>Cancelling a pending or processing order returns its items to stock.</small><?php endif; ?>
                <button class="btn btn-primary" type="submit">Save status</button>
            </form>
            <?php endif; ?>
            <?php if ($order['status'] === 'cancelled'): ?>
                <form method="post" action="<?= e(adminUrl('orders', ['order' => $orderId])) ?>" data-confirm-delete="Delete this cancelled order and its items?">
                    <?= csrfField() ?><input type="hidden" name="action" value="order-delete">
                    <button class="btn btn-outline-danger" type="submit">Delete order</button>
                </form>
            <?php endif; ?>
        </section>
    </div>
    <section class="admin-order-lines" aria-labelledby="order-items-title">
        <div class="admin-row"><h2 id="order-items-title">Order items</h2>
            <?php if ($order['status'] === 'pending' && !$itemEditing): ?>
                <a class="btn btn-primary" href="<?= e(adminUrl('orders', ['order' => $orderId, 'item' => 0])) ?>#item-editor">Add item</a>
            <?php endif; ?>
        </div>
        <?php if ($itemEditing): ?>
            <form class="admin-card admin-form my-3" id="item-editor" method="post" action="<?= e(adminUrl('orders', ['order' => $orderId, 'item' => $itemId])) ?>#item-editor">
                <h2><?= $itemId ? 'Edit item' : 'Add item' ?></h2>
                <?= csrfField() ?><input type="hidden" name="action" value="item-save">
                <?php if ($errors): ?><ul class="admin-alert" role="alert"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul><?php endif; ?>
                <?php require __DIR__ . '/admin-order-product-fields.php'; ?>
                <div class="admin-actions compact"><button class="btn btn-primary" type="submit">Save item</button><a class="btn btn-secondary" href="<?= e(adminUrl('orders', ['order' => $orderId])) ?>">Cancel</a></div>
            </form>
        <?php endif; ?>
        <div class="admin-card admin-table-wrap"><table>
            <thead><tr><th scope="col">Product</th><th scope="col">Size</th><th scope="col">Qty</th><th scope="col">Unit price</th><th scope="col">Subtotal</th><?php if ($order['status'] === 'pending'): ?><th scope="col">Actions</th><?php endif; ?></tr></thead>
            <tbody>
            <?php foreach ($lines as $line): ?>
                <tr><th scope="row"><?= e($line['product_name']) ?></th><td><?= e($line['size'] === 'standard' ? '—' : $line['size']) ?></td><td><?= e($line['quantity']) ?></td><td><?= e($order['currency'] . ' ' . $line['unit_price']) ?></td><td><?= e($order['currency'] . ' ' . orderDecimal(orderPriceCents($line['unit_price']) * (int) $line['quantity'])) ?></td>
                <?php if ($order['status'] === 'pending'): ?>
                    <td><div class="admin-actions compact">
                        <a class="text-link" href="<?= e(adminUrl('orders', ['order' => $orderId, 'item' => $line['order_item_id']])) ?>#item-editor">Edit</a>
                        <?php if (count($lines) > 1): ?>
                            <form method="post" action="<?= e(adminUrl('orders', ['order' => $orderId, 'item' => $line['order_item_id']])) ?>" data-confirm-delete="Delete this item and return its quantity to stock?">
                                <?= csrfField() ?><input type="hidden" name="action" value="item-delete"><button class="admin-delete" type="submit">Delete</button>
                            </form>
                        <?php endif; ?>
                    </div></td>
                <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    </section>
    <?php endif; ?>
<?php else: ?>
    <div class="admin-row"><h1>Orders</h1><a class="btn btn-primary" href="<?= e(adminUrl('orders', ['edit' => 0])) ?>">Add order</a></div>
    <form class="admin-actions" method="get" aria-label="Filter orders">
        <input type="hidden" name="tab" value="orders">
        <label for="order-filter">Status</label><select class="form-select order-filter" name="status" id="order-filter"><option value="">All orders</option><?php foreach (ORDER_STATUS_LABELS as $key => $label): ?><option value="<?= e($key) ?>" <?= $key === $status ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select>
        <button class="btn btn-secondary" type="submit">Filter</button>
    </form>
    <div class="admin-card admin-table-wrap"><table>
        <thead><tr><th scope="col">Order</th><th scope="col">Buyer</th><th scope="col">Status</th><th scope="col">Total</th><th scope="col">Placed</th></tr></thead>
        <tbody>
        <?php foreach ($orders as $row): ?>
            <tr><th scope="row"><a class="text-link" href="<?= e(adminUrl('orders', ['order' => $row['order_id']])) ?>"><?= e($row['order_number']) ?></a></th><td><?= e($row['buyer_name']) ?></td><td><?= e(ORDER_STATUS_LABELS[$row['status']]) ?></td><td><?= e($row['currency'] . ' ' . $row['total_amount']) ?></td><td><?= e($row['created_at']) ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$orders): ?><tr><td colspan="5">No orders match this filter.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
    <?php if ($pages > 1): ?><nav class="admin-actions" aria-label="Order pages">
        <?php if ($current > 1): ?><a class="btn btn-secondary" href="<?= e(adminUrl('orders', ['status' => $status, 'page' => $current - 1])) ?>">Previous</a><?php endif; ?>
        <span>Page <?= $current ?> of <?= $pages ?></span>
        <?php if ($current < $pages): ?><a class="btn btn-secondary" href="<?= e(adminUrl('orders', ['status' => $status, 'page' => $current + 1])) ?>">Next</a><?php endif; ?>
    </nav><?php endif; ?>
<?php endif; ?>
<?php endif; ?>
