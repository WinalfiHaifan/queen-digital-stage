<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $receipt ? 'Order received' : 'Checkout' ?> | Queen Digital Stage</title>
    <link rel="icon" href="assets/images/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/database-site.css">
</head>
<body class="page-checkout">
<header class="site-header">
    <div class="container checkout-header">
        <a class="brand" href="index.php"><span class="brand-mark" aria-hidden="true">Q</span><span>Queen Digital Stage</span></a>
        <a class="text-link" href="store.php">← Back to Store</a>
    </div>
</header>
<main class="container checkout-main">
    <p class="eyebrow">Queen merchandise</p>
    <h1><?= $receipt ? 'Order received' : 'Checkout' ?></h1>
    <?php if ($errors): ?><ul class="checkout-error" role="alert"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <?php if ($receipt): ?>
        <article class="card checkout-panel checkout-receipt" aria-labelledby="order-reference">
            <p class="eyebrow">Order number</p>
            <h2 id="order-reference"><?= e($receipt['order_number']) ?></h2>
            <p class="data-chip"><?= e(ORDER_STATUS_LABELS[$receipt['status']]) ?></p>
            <p>Payment method: Cash on delivery</p>
            <p class="checkout-total"><span>Order total</span><strong><?= e($receipt['currency'] . ' ' . $receipt['total_amount']) ?></strong></p>
            <p class="checkout-address"><strong><?= e($receipt['buyer_name']) ?></strong><br><?= e($receipt['shipping_address']) ?><br><?= e(implode(', ', [$receipt['shipping_city'], $receipt['shipping_province'], $receipt['postal_code']])) ?></p>
            <a class="btn btn-secondary" href="store.php">Continue shopping</a>
        </article>
        <script id="checkout-receipt-data" type="application/json"><?= json_encode(['order' => $receipt['order_number'], 'cart' => $confirmed[$token]['cart']], JSON_HEX_TAG | JSON_THROW_ON_ERROR) ?></script>
        <script src="assets/js/checkout.js" defer></script>
    <?php elseif ($review): $quote = $review['quote']; ?>
        <div class="checkout-layout">
            <form class="card checkout-panel checkout-form" action="checkout.php" method="post">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="place">
                <input type="hidden" name="review" value="<?= e($token) ?>">
                <h2>Shipping details</h2>
                <div class="checkout-fields">
                    <?php foreach (SHIPPING_FIELDS as $field => [$label, $length, $autocomplete]): ?>
                    <label class="<?= $field === 'shipping_address' ? 'checkout-wide' : '' ?>" for="<?= e($field) ?>">
                        <?= e($label) ?>
                        <?php if ($field === 'shipping_address'): ?>
                            <textarea class="input" id="<?= e($field) ?>" name="<?= e($field) ?>" maxlength="<?= $length ?>" autocomplete="<?= e($autocomplete) ?>" rows="3" required><?= e($values[$field] ?? '') ?></textarea>
                        <?php else: ?>
                            <input class="input" id="<?= e($field) ?>" name="<?= e($field) ?>" type="<?= $field === 'buyer_phone' ? 'tel' : 'text' ?>" maxlength="<?= $length ?>" autocomplete="<?= e($autocomplete) ?>" value="<?= e($values[$field] ?? '') ?>" required>
                        <?php endif; ?>
                    </label>
                    <?php endforeach; ?>
                </div>
                <section class="checkout-payment" aria-label="Payment method"><span class="data-chip">COD</span><div><strong>Cash on delivery</strong><p>Pay the order total when it arrives.</p></div></section>
                <button class="btn btn-primary" type="submit">Place order</button>
            </form>
            <aside class="card checkout-panel" aria-labelledby="checkout-summary">
                <h2 id="checkout-summary">Order summary</h2>
                <ul class="checkout-items">
                <?php foreach ($quote['lines'] as $line): ?>
                    <li><div><strong><?= e($line['product_name']) ?></strong><span><?= $line['size'] !== 'standard' ? e('Size ' . $line['size']) . ' · ' : '' ?>Qty <?= $line['quantity'] ?></span></div><span><?= e($quote['currency'] . ' ' . orderDecimal($line['unit_cents'] * $line['quantity'])) ?></span></li>
                <?php endforeach; ?>
                </ul>
                <p class="checkout-fee"><span>Shipping fee (demo)</span><span><?= e($quote['currency']) ?> 0.00</span></p>
                <p class="checkout-total"><span>Due on delivery</span><strong><?= e($quote['currency'] . ' ' . orderDecimal($quote['total_cents'])) ?></strong></p>
            </aside>
        </div>
    <?php else: ?>
        <article class="card checkout-panel checkout-receipt">
            <h2>Review your cart first</h2>
            <p>Choose your products in the Store, then open the cart to start checkout.</p>
            <a class="btn btn-primary" href="store.php">Go to Store</a>
        </article>
    <?php endif; ?>
</main>
</body>
</html>
