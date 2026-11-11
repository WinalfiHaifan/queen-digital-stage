(() => {
  'use strict';
  const data = document.getElementById('checkout-receipt-data');
  if (!data) return;
  const { order, cart: purchased } = JSON.parse(data.textContent);
  const key = 'queen-stage-cart-v1';
  const seenKey = 'queen-stage-confirmed-orders';
  try {
    const seen = JSON.parse(localStorage.getItem(seenKey) || '[]');
    if (!Array.isArray(seen) || seen.includes(order)) return;
    const cart = JSON.parse(localStorage.getItem(key) || '{}');
    if (!cart || typeof cart !== 'object' || Array.isArray(cart)) return;
    for (const [id, sizes] of Object.entries(purchased)) {
      const current = cart[id];
      if (Number.isSafeInteger(current)) {
        const remaining = current - Object.values(sizes).reduce((sum, quantity) => sum + quantity, 0);
        if (remaining > 0) cart[id] = remaining;
        else delete cart[id];
      } else if (current && typeof current === 'object') {
        for (const [size, quantity] of Object.entries(sizes)) {
          if (!Number.isSafeInteger(current[size])) continue;
          current[size] -= quantity;
          if (current[size] <= 0) delete current[size];
        }
        if (!Object.keys(current).length) delete cart[id];
      }
    }
    localStorage.setItem(key, JSON.stringify(cart));
    localStorage.setItem(seenKey, JSON.stringify([...seen.slice(-31), order]));
  } catch {
    // The order is already recorded even when browser storage is unavailable.
  }
})();
