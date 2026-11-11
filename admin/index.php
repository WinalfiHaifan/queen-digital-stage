<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/content.php';
require __DIR__ . '/../app/admin-actions.php';
require __DIR__ . '/../app/orders.php';
require __DIR__ . '/../app/admin-order-actions.php';
$resources = require __DIR__ . '/../app/admin-resources.php';

startSession();
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('X-Frame-Options: DENY');

$tab = queryText('tab');
$tab = $tab === 'fan_zone_submissions' ? 'fan-zone' : $tab;
$tab = isset($resources[$tab]) || in_array($tab, ['fan-zone', 'orders'], true) ? $tab : 'dashboard';
$table = $tab === 'fan-zone' ? 'fan_zone_submissions' : $tab;
$method = $_SERVER['REQUEST_METHOD'];
$action = postText('action');
$editing = isset($_GET['edit']);
$id = recordId(queryText('edit'));
$errors = [];
$values = [];
$loginError = '';
$orderId = recordId(queryText('order'));
$itemEditing = isset($_GET['item']);
$itemId = recordId(queryText('item'));

try {
    $db = database();
    $admin = sessionAdmin($db);
    if ($method === 'POST') {
        requireCsrf();
        if (!$admin && $action === 'login') {
            $loginError = signIn($db);
        } elseif (!$admin) {
            http_response_code(403);
            $loginError = 'Sign in to continue.';
        } elseif ($action === 'logout') {
            unset($_SESSION['admin_id'], $_SESSION['admin_seen'], $_SESSION['admin_notice']);
            session_regenerate_id(true);
            redirect('index.php');
        } elseif ($tab === 'orders' && $action === 'order-status') {
            $orderId = recordId(postText('order_id'));
            try {
                notice(changeOrderStatus($db, $orderId, postText('status')));
            } catch (OrderProblem $error) {
                notice($error->getMessage());
            }
            redirect(adminUrl('orders', ['order' => $orderId]));
        } elseif ($tab === 'orders' && $action === 'order-save') {
            [$values, $errors] = checkoutShipping();
            $values += ['product_id' => postText('product_id'), 'size' => postText('size'), 'quantity' => postText('quantity')];
            if (!$errors) {
                try {
                    $savedId = saveAdminOrder($db, $orderId, array_intersect_key($values, SHIPPING_FIELDS));
                    notice($orderId ? 'Shipping details updated.' : 'Order created.');
                    redirect(adminUrl('orders', ['order' => $savedId]));
                } catch (OrderProblem $error) {
                    $errors[] = $error->getMessage();
                }
            }
            $editing = true;
            http_response_code(422);
        } elseif ($tab === 'orders' && $action === 'order-delete') {
            try {
                deleteAdminOrder($db, $orderId);
                notice('Order deleted.');
                redirect(adminUrl('orders'));
            } catch (OrderProblem $error) {
                notice($error->getMessage());
                redirect(adminUrl('orders', ['order' => $orderId]));
            }
        } elseif ($tab === 'orders' && in_array($action, ['item-save', 'item-delete'], true)) {
            try {
                $input = $action === 'item-save' ? adminOrderProductInput() : null;
                if ($input === null && !$itemId) {
                    throw new OrderProblem('Choose an item to delete.');
                }
                changeAdminOrderItem($db, $orderId, $itemId, $input);
                notice($input ? ($itemId ? 'Item updated.' : 'Item added.') : 'Item deleted.');
                redirect(adminUrl('orders', ['order' => $orderId]));
            } catch (OrderProblem $error) {
                if ($action === 'item-delete') {
                    notice($error->getMessage());
                    redirect(adminUrl('orders', ['order' => $orderId]));
                }
                $errors[] = $error->getMessage();
                $values = ['product_id' => postText('product_id'), 'size' => postText('size'), 'quantity' => postText('quantity')];
                $itemEditing = true;
                http_response_code(422);
            }
        } elseif ($tab === 'fan-zone' && $action === 'moderate') {
            $id = recordId(postText('id'));
            $status = postText('is_active');
            if ($id && in_array($status, ['0', '1'], true) && findRecord($db, 'fan_zone_submissions', 'submission_id', $id)) {
                $db->prepare('UPDATE fan_zone_submissions SET is_active = ? WHERE submission_id = ?')->execute([$status, $id]);
                notice($status === '1' ? 'Fan note published.' : 'Fan note hidden.');
            } else {
                notice('The note could not be updated.');
            }
            redirect(adminUrl('fan-zone', ['status' => queryText('status')]));
        } elseif (isset($resources[$table])) {
            [, $pk, , $fields] = $resources[$table];
            $id = recordId(postText('id'));
            if ($action === 'delete' && $id) {
                notice(deleteRecord($db, $table, $pk, $id, (int) $admin['admin_id']));
                redirect(adminUrl($tab));
            }
            if ($action === 'save') {
                $errors = saveRecord($db, $table, $pk, $fields, $id, (int) $admin['admin_id'], $values);
                if (!$errors) {
                    notice($id ? 'Changes saved.' : 'Record created.');
                    redirect(adminUrl($tab));
                }
                $editing = true;
                http_response_code(422);
            }
        }
    }

    if ($admin && $tab === 'dashboard') {
        $counts = [
            'New orders' => (int) $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
            'Pending notes' => (int) $db->query('SELECT COUNT(*) FROM fan_zone_submissions WHERE is_active = 0')->fetchColumn(),
            'Visible products' => (int) $db->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn(),
            'Visible products out of stock' => (int) $db->query('SELECT COUNT(*) FROM products WHERE stock = 0 AND is_active = 1')->fetchColumn(),
            'Songs' => (int) $db->query('SELECT COUNT(*) FROM songs')->fetchColumn(),
        ];
    } elseif ($admin && $tab === 'orders') {
        if ($orderId) {
            $order = contentRows($db, 'SELECT * FROM orders WHERE order_id = ?', [$orderId])[0] ?? null;
            $lines = $order ? contentRows($db, 'SELECT order_item_id, product_id, product_name, size, quantity, unit_price FROM order_items WHERE order_id = ? ORDER BY order_item_id', [$orderId]) : [];
            if (!$order) http_response_code(404);
            if ($editing && $order && !in_array($order['status'], ['pending', 'processing'], true)) {
                notice('Shipping details can only be edited before dispatch.');
                redirect(adminUrl('orders', ['order' => $orderId]));
            }
            if ($itemEditing && $order) {
                $item = $itemId ? (array_column($lines, null, 'order_item_id')[$itemId] ?? null) : null;
                if ($order['status'] !== 'pending' || ($itemId && !$item)) {
                    if (!$errors) {
                        notice('This order item cannot be edited.');
                        redirect(adminUrl('orders', ['order' => $orderId]));
                    }
                    $itemEditing = false;
                }
                $values = $values ?: ($item ?? []);
            }
        } elseif (!$editing) {
            $status = queryText('status');
            $status = isset(ORDER_STATUS_LABELS[$status]) ? $status : '';
            $where = $status === '' ? '' : ' WHERE status = ?';
            $params = $status === '' ? [] : [$status];
            $total = (int) contentRows($db, 'SELECT COUNT(*) AS total FROM orders' . $where, $params)[0]['total'];
            $pages = max(1, (int) ceil($total / 20));
            $current = min($pages, max(1, recordId(queryText('page'))));
            $offset = ($current - 1) * 20;
            $orders = contentRows($db, 'SELECT order_id, order_number, buyer_name, status, currency, total_amount, created_at FROM orders' . $where . " ORDER BY order_id DESC LIMIT 20 OFFSET $offset", $params);
        }
        if (($editing && !$orderId) || ($itemEditing && !empty($order))) {
            $orderProducts = products($db, false);
        }
        if ($editing) {
            $values = $values ?: ($order ?? []);
        }
    } elseif ($admin && $tab === 'fan-zone' && !$editing) {
        $status = queryText('status');
        $where = match ($status) { 'pending' => ' WHERE is_active = 0', 'published' => ' WHERE is_active = 1', default => '' };
        $total = (int) $db->query('SELECT COUNT(*) FROM fan_zone_submissions' . $where)->fetchColumn();
        $pages = max(1, (int) ceil($total / 20));
        $current = min($pages, max(1, recordId(queryText('page'))));
        $offset = ($current - 1) * 20;
        $notes = $db->query('SELECT * FROM fan_zone_submissions' . $where . " ORDER BY created_at DESC, submission_id DESC LIMIT 20 OFFSET $offset")->fetchAll();
    } elseif ($admin && isset($resources[$table])) {
        [$title, $pk, $labelKey, $fields] = $resources[$table];
        if ($editing) {
            if (!$values && $id) {
                $values = findRecord($db, $table, $pk, $id) ?? [];
                if (!$values) {
                    notice('The record no longer exists.');
                    redirect(adminUrl($tab));
                }
            }
            $options = [];
            if (isset($fields['category_id'])) {
                $options['category_id'] = $db->query('SELECT category_id, category_name FROM product_categories ORDER BY category_name')->fetchAll(PDO::FETCH_KEY_PAIR);
            }
            if (isset($fields['album_id'])) {
                $options['album_id'] = $db->query('SELECT album_id, album_title FROM albums ORDER BY album_title')->fetchAll(PDO::FETCH_KEY_PAIR);
            }
        } else {
            $rows = $tab === 'products' ? products($db, false) : $db->query("SELECT `$pk`, `$labelKey`, updated_at FROM `$tab` ORDER BY `$pk` DESC")->fetchAll();
        }
    }
} catch (PDOException $error) {
    unavailable($error);
}
$flash = $_SESSION['admin_notice'] ?? '';
unset($_SESSION['admin_notice']);
require __DIR__ . '/../app/views/admin.php';
