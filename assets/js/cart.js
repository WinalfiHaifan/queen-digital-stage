(() => {
  'use strict';
  const data = document.getElementById('product-data');
  const dialog = document.getElementById('cart-dialog');
  if (!data || !dialog) return;

  const key = 'queen-stage-cart-v1';
  let catalog = new Map(JSON.parse(data.textContent).map((product) => [String(product.id), product]));
  const add = document.getElementById('featured-add-cart');
  const quantity = document.getElementById('featured-qty');
  const sizeField = document.getElementById('featured-size');
  const checkout = document.getElementById('cart-checkout');
  const productId = add?.closest('[data-featured-product]')?.dataset.featuredProduct;
  const list = document.getElementById('cart-items');
  const toast = document.getElementById('cart-toast');
  const adjustment = document.getElementById('cart-adjustment');
  let cart = {};
  let useStorage = true;
  let toastTimer;

  function loadCart() {
    if (!useStorage) return;
    let stored;
    try { stored = localStorage.getItem(key); } catch { useStorage = false; return; }
    try { cart = JSON.parse(stored || '{}') || {}; } catch { cart = {}; }
    if (typeof cart !== 'object' || Array.isArray(cart)) cart = {};
  }

  function saveCart() {
    if (!useStorage) return;
    try { localStorage.setItem(key, JSON.stringify(cart)); } catch { useStorage = false; }
  }

  function sizes(product) {
    return product.sizes.length ? product.sizes : ['standard'];
  }

  function productQuantity(id) {
    return Object.values(cart[id] || {}).reduce((total, amount) => total + amount, 0);
  }

  function syncCart() {
    let changed = false;
    for (const id of Object.keys(cart)) {
      const item = catalog.get(id);
      if (!item?.stock) {
        changed = true;
        delete cart[id];
        continue;
      }
      const allowed = sizes(item);
      const existing = typeof cart[id] === 'number' ? { [allowed[0]]: cart[id] } : cart[id];
      const available = {};
      let remaining = item.stock;
      for (const size of allowed) {
        const amount = existing?.[size];
        if (remaining > 0 && Number.isSafeInteger(amount) && amount > 0) {
          available[size] = Math.min(remaining, amount);
          remaining -= available[size];
        }
      }
      if (Object.entries(existing ?? {}).some(([size, amount]) => available[size] !== amount)) changed = true;
      if (remaining === item.stock) delete cart[id];
      else cart[id] = available;
    }
    return changed;
  }

  function showAdjustment(message) {
    adjustment.hidden = !message;
    adjustment.textContent = message;
  }

  function reconcileCart() {
    if (!syncCart()) return false;
    saveCart();
    showAdjustment('Availability changed. Review the updated items and quantities in your cart.');
    return true;
  }

  function formatTotal(currency, cents) {
    return `${currency} ${cents / 100n}.${String(cents % 100n).padStart(2, '0')}`;
  }

  function showToast(message) {
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('show'), 3200);
  }

  function render() {
    const changed = reconcileCart();
    list.replaceChildren();
    const totals = new Map();
    let count = 0;
    for (const [id, variants] of Object.entries(cart)) {
      const item = catalog.get(id);
      const unitPrice = BigInt(item.price.replace('.', ''));
      for (const [size, amount] of Object.entries(variants)) {
        totals.set(item.currency, (totals.get(item.currency) ?? 0n) + unitPrice * BigInt(amount));
        count += amount;
        const row = document.createElement('div');
        row.className = 'cart-line';
        const text = document.createElement('span');
        const label = size === 'standard' ? item.name : `${item.name} (${size})`;
        text.textContent = `${label} × ${amount}`;
        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'text-link';
        remove.textContent = 'Remove';
        remove.setAttribute('aria-label', `Remove ${label}`);
        remove.addEventListener('click', () => { delete cart[id][size]; saveCart(); render(); });
        row.append(text, remove);
        list.append(row);
      }
    }
    if (!count) list.textContent = 'Your cart is empty.';
    const total = Array.from(totals, ([currency, cents]) => formatTotal(currency, cents)).join(' + ') || '0.00';
    document.getElementById('cart-count').textContent = String(count);
    document.getElementById('cart-total').textContent = total;
    document.getElementById('cart-dialog-total').textContent = `Total ${total}`;
    if (checkout) checkout.hidden = count === 0;

    if (quantity && add) {
      const stock = catalog.get(productId)?.stock ?? 0;
      const remaining = Math.max(0, stock - productQuantity(productId));
      quantity.min = remaining ? '1' : '0';
      quantity.max = String(remaining);
      quantity.value = String(remaining ? Math.min(remaining, Math.max(1, Number(quantity.value) || 1)) : 0);
      quantity.disabled = add.disabled = remaining === 0;
      if (sizeField) sizeField.disabled = remaining === 0;
      add.textContent = !stock ? 'Out of Stock' : remaining ? 'Add to Cart' : 'Stock limit reached';
    }
    return changed;
  }

  async function refreshCatalog() {
    const response = await fetch('store.php?view=inventory', { cache: 'no-store' });
    if (!response.ok) throw new Error('Inventory unavailable');
    catalog = new Map((await response.json()).map((item) => [String(item.id), item]));
  }

  add?.addEventListener('click', async () => {
    const amount = Number(quantity.value);
    const size = sizeField?.value ?? 'standard';
    if (!Number.isSafeInteger(amount) || amount < 1 || amount > Number(quantity.max)) {
      showToast('Choose a quantity within the available stock.');
      return;
    }
    add.disabled = true;
    try {
      await refreshCatalog();
      loadCart();
      reconcileCart();
      const item = catalog.get(productId);
      const available = item && item.stock - productQuantity(productId);
      if (!available || amount > available || !sizes(item).includes(size)) {
        showToast('Stock has changed. Check the available quantity.');
      } else {
        const variants = cart[productId] ??= {};
        variants[size] = (variants[size] || 0) + amount;
        showToast(`${amount} × ${item.name} added to your cart.`);
      }
      saveCart();
    } catch {
      showToast('Could not verify stock. Try again in a moment.');
    } finally {
      render();
    }
  });
  document.querySelectorAll('[data-open-cart], #view-cart').forEach((button) => button.addEventListener('click', () => {
    loadCart();
    render();
    if (!dialog.open) dialog.showModal();
  }));
  checkout?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const submit = checkout.querySelector('button[type="submit"]');
    if (submit.disabled) return;
    submit.disabled = true;
    showAdjustment('');
    try {
      const previous = catalog;
      await refreshCatalog();
      loadCart();
      const changed = render();
      if (changed || checkout.hidden) return;
      const pricesChanged = Object.keys(cart).some((id) =>
        previous.get(id)?.price !== catalog.get(id).price || previous.get(id)?.currency !== catalog.get(id).currency
      );
      if (pricesChanged) {
        showAdjustment('A price has changed. Review your updated total before proceeding.');
        return;
      }
      checkout.elements.namedItem('cart').value = JSON.stringify(cart);
      checkout.submit();
    } catch {
      showAdjustment('Could not verify your cart. Try again in a moment.');
    } finally {
      submit.disabled = false;
    }
  });
  window.addEventListener('storage', (event) => { if (event.key === key) { loadCart(); render(); } });
  window.addEventListener('pageshow', (event) => { if (event.persisted) { loadCart(); render(); } });
  loadCart();
  render();
  saveCart();
})();
