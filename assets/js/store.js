(() => {
  'use strict';

  const $ = (selector, context = document) => context.querySelector(selector);
  const $$ = (selector, context = document) => Array.from(context.querySelectorAll(selector));

  const featuredSection = $('.featured-product');
  const featuredImage = $('.featured-image img');
  const featuredName = $('.featured-content h3');
  const featuredPrice = $('.featured-content .price');
  const sizeOption = $('.product-option');
  const sizeButtons = $$('.size-btn');
  const quantityInput = $('#featured-qty');
  const minusButton = $('#qty-minus');
  const plusButton = $('#qty-plus');
  const addButton = $('#featured-add-cart');
  const viewCartButton = $('#view-cart');
  const cartCount = $('#cart-count');
  const cartTotal = $('#cart-total');
  const cartToast = $('#cart-toast');

  if (!featuredSection || !quantityInput || !addButton) return;

  const storage = {
    get(key, fallback) {
      try { return window.localStorage.getItem(key) ?? fallback; }
      catch (error) { return fallback; }
    },
    set(key, value) {
      try { window.localStorage.setItem(key, String(value)); }
      catch (error) { /* The demo still works if storage is unavailable. */ }
    },
  };

  let itemCount = Number(storage.get('queenCartCount', '0')) || 0;
  let totalValue = Number(storage.get('queenCartTotal', '0')) || 0;
  let quantity = Math.max(1, Number(quantityInput.value) || 1);
  let selectedSize = $('.size-btn.active')?.dataset.size || 'S';
  let activeCategory = 'apparel';

  const updateCartSummary = () => {
    if (cartCount) cartCount.textContent = String(itemCount);
    if (cartTotal) cartTotal.textContent = `$${totalValue.toFixed(2).replace('.00', '')}`;
  };

  const showToast = (message) => {
    if (!cartToast) return;
    cartToast.textContent = message;
    cartToast.classList.add('show');
    window.clearTimeout(showToast.timer);
    showToast.timer = window.setTimeout(() => cartToast.classList.remove('show'), 2600);
  };

  const updateQuantity = (nextQuantity) => {
    quantity = Math.min(99, Math.max(1, nextQuantity));
    quantityInput.value = String(quantity);
    minusButton?.toggleAttribute('disabled', quantity === 1);
    minusButton?.setAttribute('aria-disabled', String(quantity === 1));
  };

  const resetSize = () => {
    selectedSize = 'S';
    sizeButtons.forEach((button) => {
      const active = button.dataset.size === selectedSize;
      button.classList.toggle('active', active);
      button.setAttribute('aria-pressed', String(active));
    });
  };

  $$('.select-product').forEach((button) => {
    button.addEventListener('click', () => {
      const card = button.closest('.product-card');
      if (!card) return;

      const name = card.dataset.name || 'Queen Merchandise';
      const price = Number(card.dataset.price || 0);
      const image = card.dataset.image || '';
      activeCategory = card.dataset.category || 'collectible';

      if (featuredName) featuredName.textContent = name;
      if (featuredPrice) featuredPrice.textContent = `$${price}`;
      if (featuredImage) {
        featuredImage.src = image;
        featuredImage.alt = name;
      }
      addButton.dataset.product = name;
      addButton.dataset.price = String(price);
      sizeOption?.toggleAttribute('hidden', activeCategory !== 'apparel');
      resetSize();
      updateQuantity(1);

      $$('.product-card').forEach((product) => product.classList.toggle('selected-product', product === card));
      featuredSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
      showToast(`${name} selected.`);
    });
  });

  sizeButtons.forEach((button) => {
    button.setAttribute('type', 'button');
    button.setAttribute('aria-pressed', String(button.classList.contains('active')));
    button.addEventListener('click', () => {
      selectedSize = button.dataset.size || 'S';
      sizeButtons.forEach((item) => {
        const active = item === button;
        item.classList.toggle('active', active);
        item.setAttribute('aria-pressed', String(active));
      });
    });
  });

  minusButton?.addEventListener('click', () => updateQuantity(quantity - 1));
  plusButton?.addEventListener('click', () => updateQuantity(quantity + 1));

  addButton.addEventListener('click', () => {
    const unitPrice = Number(addButton.dataset.price || 0);
    const productName = addButton.dataset.product || 'Queen Merchandise';
    itemCount += quantity;
    totalValue += unitPrice * quantity;
    storage.set('queenCartCount', itemCount);
    storage.set('queenCartTotal', totalValue);
    updateCartSummary();

    const sizeText = activeCategory === 'apparel' ? `, size ${selectedSize}` : '';
    showToast(`${quantity} × ${productName}${sizeText} added to cart.`);
    const originalText = addButton.textContent;
    addButton.textContent = 'Added ✓';
    addButton.classList.add('is-added');
    window.setTimeout(() => {
      addButton.textContent = originalText;
      addButton.classList.remove('is-added');
    }, 1200);
  });

  viewCartButton?.addEventListener('click', () => {
    const summary = itemCount
      ? `Cart contains ${itemCount} item${itemCount === 1 ? '' : 's'} totaling $${totalValue.toFixed(2).replace('.00', '')}.`
      : 'Your cart is still empty.';
    showToast(summary);
    $('.cart-note')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
  });

  updateCartSummary();
  updateQuantity(quantity);
  resetSize();
})();
