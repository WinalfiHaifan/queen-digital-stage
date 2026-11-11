<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title ?? ($admin ? 'Admin Dashboard' : 'Admin Sign In')) ?> | Queen Digital Stage</title>
    <link rel="stylesheet" href="../assets/vendor/adminlte/adminlte.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="queen-admin <?= $admin ? 'layout-fixed sidebar-expand-lg bg-body-tertiary' : 'login-page bg-body-tertiary' ?>">
    <?php if (!$admin): ?>
    <main class="login-box admin-login">
        <form class="card card-outline card-primary admin-card admin-form" method="post" action="index.php">
            <p class="eyebrow">Content management</p><h1>Admin sign in</h1>
            <p>Sign in to edit the archive, manage merchandise, or moderate fan notes.</p>
            <?php if ($loginError): ?><p class="admin-alert" role="alert"><?= e($loginError) ?></p><?php endif; ?>
            <?= csrfField() ?><input type="hidden" name="action" value="login">
            <label>Username or email<input class="form-control" name="login" autocomplete="username" maxlength="254" required></label>
            <label>Password<input class="form-control" type="password" name="password" autocomplete="current-password" maxlength="72" required></label>
            <button class="btn btn-primary" type="submit">Sign in</button>
            <a class="text-link" href="../index.php">View website ↗</a>
        </form>
    </main>
    <?php else: ?>
    <div class="app-wrapper">
        <header class="app-header navbar navbar-expand bg-body">
            <div class="container-fluid">
                <button class="btn btn-outline-secondary" type="button" data-lte-toggle="sidebar" aria-label="Toggle navigation">☰</button>
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <a class="btn btn-sm btn-outline-secondary" href="../index.php">View website ↗</a>
                    <form method="post" action="index.php"><?= csrfField() ?><input type="hidden" name="action" value="logout"><button class="btn btn-sm btn-outline-secondary" type="submit">Sign out</button></form>
                </div>
            </div>
        </header>
        <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
            <div class="sidebar-brand">
                <a href="<?= e(adminUrl('dashboard')) ?>" class="brand-link"><span class="admin-brand-mark" aria-hidden="true">Q</span><span class="brand-text">Queen Digital Stage</span></a>
            </div>
            <div class="sidebar-wrapper">
            <nav class="mt-2" aria-label="Admin sections">
                <ul class="nav sidebar-menu flex-column">
                    <li class="nav-header"><?= e($admin['full_name']) ?></li>
                    <?php foreach (['dashboard' => 'Dashboard', 'orders' => 'Orders & Items', 'fan-zone' => 'Fan Zone'] + array_map(static fn(array $config): string => $config[0], array_diff_key($resources, ['fan_zone_submissions' => true])) as $key => $label): ?>
                        <li class="nav-item"><a class="nav-link<?= $key === $tab ? ' active' : '' ?>" href="<?= e(adminUrl($key)) ?>" <?= $key === $tab ? 'aria-current="page"' : '' ?>><p><?= e($label) ?></p></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
            </div>
        </aside>
        <main class="app-main">
          <div class="app-content"><div class="container-fluid admin-main">
            <?php if ($flash): ?><p class="admin-notice" role="status"><?= e($flash) ?></p><?php endif; ?>
            <?php if ($tab === 'dashboard'): ?>
                <p class="eyebrow">Queen Digital Stage</p><h1>Dashboard</h1>
                <div class="row g-3"><?php foreach ($counts as $label => $count): ?><div class="col-sm-6 col-xl-4"><article class="small-box text-bg-primary h-100"><div class="inner"><h2><?= e($count) ?></h2><p class="mb-0"><?= e($label) ?></p></div></article></div><?php endforeach; ?></div>
                <div class="admin-actions"><a class="btn btn-primary" href="<?= e(adminUrl('orders', ['status' => 'pending'])) ?>">Review Orders</a><a class="btn btn-secondary" href="<?= e(adminUrl('fan-zone', ['status' => 'pending'])) ?>">Review Fan Zone</a><a class="btn btn-secondary" href="<?= e(adminUrl('products')) ?>">Manage Store</a></div>
            <?php elseif ($tab === 'orders'): ?>
                <?php require __DIR__ . '/admin-orders.php'; ?>
            <?php elseif ($tab === 'fan-zone' && !$editing): ?>
                <div class="admin-row"><div><p class="eyebrow">Community moderation</p><h1>Fan Zone</h1></div><a class="btn btn-primary" href="<?= e(adminUrl('fan-zone', ['edit' => 0])) ?>">Add record</a></div>
                <p>Publish reviewed notes or hide them from the public feed.</p>
                <div class="admin-actions" aria-label="Filter notes">
                    <?php foreach (['' => 'All', 'pending' => 'Pending or hidden', 'published' => 'Published'] as $key => $label): ?><a class="btn <?= $status === $key ? 'btn-primary' : 'btn-secondary' ?>" href="<?= e(adminUrl('fan-zone', ['status' => $key])) ?>"><?= e($label) ?></a><?php endforeach; ?>
                </div>
                <div class="admin-notes">
                <?php foreach ($notes as $note): ?>
                    <article class="admin-card admin-note">
                        <div class="admin-row"><h2><?= e($note['name']) ?></h2><span class="admin-status <?= $note['is_active'] ? 'status-live' : '' ?>"><?= $note['is_active'] ? 'Published' : 'Pending or hidden' ?></span></div>
                        <p class="admin-muted"><?= e($note['favorite_topic']) ?> · <?= e($note['created_at']) ?></p>
                        <p class="admin-message"><?= e($note['message']) ?></p>
                        <div class="admin-actions compact">
                            <form method="post"><?= csrfField() ?><input type="hidden" name="action" value="moderate"><input type="hidden" name="id" value="<?= e($note['submission_id']) ?>"><input type="hidden" name="is_active" value="<?= $note['is_active'] ? 0 : 1 ?>"><button class="btn <?= $note['is_active'] ? 'btn-secondary' : 'btn-primary' ?>" type="submit"><?= $note['is_active'] ? 'Hide note' : 'Approve and publish' ?></button></form>
                            <a class="text-link" href="<?= e(adminUrl('fan-zone', ['edit' => $note['submission_id']])) ?>">Edit</a>
                            <form method="post" action="<?= e(adminUrl('fan-zone')) ?>" data-confirm-delete><?= csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($note['submission_id']) ?>"><button class="admin-delete" type="submit">Delete</button></form>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php if (!$notes): ?><p class="admin-card">No notes match this filter.</p><?php endif; ?>
                </div>
                <?php if ($pages > 1): ?><nav class="admin-actions" aria-label="Notes pages"><?php if ($current > 1): ?><a class="btn btn-secondary" href="<?= e(adminUrl('fan-zone', ['status' => $status, 'page' => $current - 1])) ?>">Previous</a><?php endif; ?><span>Page <?= e($current) ?> of <?= e($pages) ?></span><?php if ($current < $pages): ?><a class="btn btn-secondary" href="<?= e(adminUrl('fan-zone', ['status' => $status, 'page' => $current + 1])) ?>">Next</a><?php endif; ?></nav><?php endif; ?>
            <?php elseif ($editing): ?>
                <p class="eyebrow"><?= e($title) ?></p><h1><?= $id ? 'Edit record' : 'Add record' ?></h1>
                <form class="admin-card admin-form" method="post" action="<?= e(adminUrl($tab, ['edit' => $id])) ?>">
                    <?= csrfField() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e($id) ?>">
                    <?php if ($tab === 'products' && $id): ?><input type="hidden" name="stock_at_open" value="<?= e($values['stock_at_open'] ?? $values['stock'] ?? '') ?>"><?php endif; ?>
                    <?php if ($errors): ?><ul class="admin-alert" role="alert"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul><?php endif; ?>
                    <div class="admin-fields">
                    <?php foreach ($fields as $key => $field):
                        [$label, $type] = $field;
                        $limit = $field[2] ?? 0;
                        $value = array_key_exists($key, $values) ? $values[$key] : ($field[3] ?? '');
                        $optional = array_key_exists(3, $field) && $field[3] === null;
                        $required = $type === 'password' ? !$id : !$optional;
                        $choices = $options[$key] ?? ($type === 'choice' ? array_combine($limit, $limit) : []);
                        $attributes = is_int($limit) && $limit > 0 ? ' maxlength="' . $limit . '"' : '';
                        $attributes .= $required ? ' required' : '';
                        ?>
                        <?php if ($type === 'flag'): ?>
                            <label class="admin-check"><input type="checkbox" name="<?= e($key) ?>" value="1" <?= (string) $value === '1' ? 'checked' : '' ?>><?= e($label) ?></label>
                        <?php else: ?>
                        <label class="<?= $type === 'long' ? 'admin-span' : '' ?>"><?= e($label) ?><?= $required ? ' *' : '' ?>
                            <?php if (in_array($type, ['choice', 'category', 'album'], true)): ?>
                                <select class="form-select" name="<?= e($key) ?>" <?= $required ? 'required' : '' ?>><option value=""><?= $required ? 'Choose an option' : 'None' ?></option><?php foreach ($choices as $option => $optionLabel): ?><option value="<?= e($option) ?>" <?= (string) $option === (string) $value ? 'selected' : '' ?>><?= e($optionLabel) ?></option><?php endforeach; ?></select>
                            <?php elseif ($type === 'long'): ?>
                                <textarea class="form-control" name="<?= e($key) ?>" rows="4" <?= $attributes ?>><?= e($value) ?></textarea>
                            <?php else:
                                $inputType = match ($type) { 'decimal', 'uint', 'year' => 'number', 'url', 'spotify-album', 'spotify-track' => 'url', 'date', 'email', 'password' => $type, default => 'text' };
                                $attributes .= match ($type) { 'decimal' => ' min="0" max="99999999.99" step="0.01"', 'uint' => ' min="0" max="4294967295" step="1"', 'year' => ' min="1901" max="2155" step="1"', 'password' => ' minlength="12" autocomplete="new-password"', default => '' }; ?>
                                <input class="form-control" name="<?= e($key) ?>" type="<?= e($inputType) ?>" value="<?= $type === 'password' ? '' : e($value) ?>" <?= $attributes ?>>
                                <?php if ($type === 'password' && $id): ?><small>Leave blank to keep the current password.</small><?php endif; ?>
                            <?php endif; ?>
                        </label>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </div>
                    <div class="admin-actions"><button class="btn btn-primary" type="submit">Save record</button><a class="btn btn-secondary" href="<?= e(adminUrl($tab)) ?>">Cancel</a></div>
                </form>
            <?php else: ?>
                <div class="admin-row"><div><p class="eyebrow">Content management</p><h1><?= e($title) ?></h1></div><a class="btn btn-primary" href="<?= e(adminUrl($tab, ['edit' => 0])) ?>">Add record</a></div>
                <div class="admin-table-wrap admin-card">
                    <table><thead><tr><th scope="col">Name</th><?php if ($tab === 'products'): ?><th scope="col">Category</th><th scope="col">Price</th><th scope="col">Stock</th><th scope="col">Weight</th><th scope="col">Visibility</th><?php else: ?><th scope="col">Updated</th><?php endif; ?><th scope="col">Actions</th></tr></thead><tbody>
                    <?php foreach ($rows as $row): ?><tr><th scope="row"><?= e($row[$labelKey]) ?></th>
                        <?php if ($tab === 'products'): ?><td><?= e($row['category_name']) ?></td><td><?= e(money($row)) ?></td><td><?= e($row['stock']) ?></td><td><?= e($row['weight_gram']) ?> g</td><td><?= $row['is_active'] ? 'Visible' : 'Hidden' ?></td><?php else: ?><td><?= e($row['updated_at'] ?? '—') ?></td><?php endif; ?>
                        <td><div class="admin-actions compact"><a class="text-link" href="<?= e(adminUrl($tab, ['edit' => $row[$pk]])) ?>">Edit</a>
                            <?php if ($tab !== 'admin_users' || (int) $row[$pk] !== (int) $admin['admin_id']): ?>
                            <form method="post" action="<?= e(adminUrl($tab)) ?>" data-confirm-delete><?= csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($row[$pk]) ?>"><button class="admin-delete" type="submit">Delete</button></form>
                            <?php endif; ?>
                        </div></td></tr>
                    <?php endforeach; ?>
                    <?php if (!$rows): ?><tr><td colspan="<?= $tab === 'products' ? 7 : 3 ?>">No records yet.</td></tr><?php endif; ?>
                    </tbody></table>
                </div>
            <?php endif; ?>
          </div></div>
        </main>
        <footer class="app-footer">Queen Digital Stage <span class="float-end text-body-secondary">AdminLTE</span></footer>
    </div>
    <?php endif; ?>
    <script src="../assets/vendor/adminlte/adminlte.min.js" defer></script>
    <script src="../assets/js/admin.js" defer></script>
</body>
</html>
