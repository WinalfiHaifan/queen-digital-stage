<div class="admin-fields">
    <label class="admin-span">Product
        <select class="form-select" name="product_id" required>
            <option value="">Choose a product</option>
            <?php foreach ($orderProducts as $product): ?>
                <?php $selectedProduct = (string) ($values['product_id'] ?? '') === (string) $product['product_id']; ?>
                <?php if (!$selectedProduct && (!$product['is_active'] || (!empty($order) && $product['currency'] !== $order['currency']))) continue; ?>
                <option value="<?= e($product['product_id']) ?>" <?= $selectedProduct ? 'selected' : '' ?>><?= e($product['product_name'] . ' · ' . money($product) . ' · ' . $product['stock'] . ' in stock') ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Size
        <select class="form-select" name="size" required>
            <?php foreach (['standard', 'S', 'M', 'L', 'XL'] as $size): ?>
                <option value="<?= e($size) ?>" <?= ($values['size'] ?? 'standard') === $size ? 'selected' : '' ?>><?= e(ucfirst($size)) ?></option>
            <?php endforeach; ?>
        </select>
        <small>Choose Standard for products without sizes.</small>
    </label>
    <label>Quantity<input class="form-control" name="quantity" type="number" min="1" max="1000" step="1" required value="<?= e($values['quantity'] ?? 1) ?>"></label>
</div>
