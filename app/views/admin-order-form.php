<p class="eyebrow">Orders</p>
<h1><?= $orderId ? 'Edit shipping details' : 'Add order' ?></h1>
<form class="admin-card admin-form" method="post" action="<?= e(adminUrl('orders', ['order' => $orderId, 'edit' => $orderId])) ?>">
    <?= csrfField() ?><input type="hidden" name="action" value="order-save">
    <?php if ($errors): ?><ul class="admin-alert" role="alert"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <div class="admin-fields">
    <?php foreach (SHIPPING_FIELDS as $field => [$label, $length, $autocomplete]): ?>
        <label<?= $field === 'shipping_address' ? ' class="admin-span"' : '' ?>><?= e($label) ?>
            <?php if ($field === 'shipping_address'): ?>
                <textarea class="form-control" name="<?= e($field) ?>" maxlength="<?= e($length) ?>" rows="3" required><?= e($values[$field] ?? '') ?></textarea>
            <?php else: ?>
                <input class="form-control" name="<?= e($field) ?>" type="<?= $field === 'buyer_phone' ? 'tel' : 'text' ?>" maxlength="<?= e($length) ?>" value="<?= e($values[$field] ?? '') ?>" required>
            <?php endif; ?>
        </label>
    <?php endforeach; ?>
    </div>
    <?php if (!$orderId): ?>
        <h2>First order item</h2>
        <?php require __DIR__ . '/admin-order-product-fields.php'; ?>
        <small>Payment is cash on delivery. You can add more items once the order is saved.</small>
    <?php endif; ?>
    <div class="admin-actions compact">
        <button class="btn btn-primary" type="submit">Save order</button>
        <a class="btn btn-secondary" href="<?= e(adminUrl('orders', $orderId ? ['order' => $orderId] : [])) ?>">Cancel</a>
    </div>
</form>
