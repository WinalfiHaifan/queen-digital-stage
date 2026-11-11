(() => {
  'use strict';
  const $ = (selector, container = document) => container.querySelector(selector);
  const $$ = (selector, container = document) => Array.from(container.querySelectorAll(selector));
  const motion = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';

  const nav = $('.site-nav');
  const toggle = $('.nav-toggle');
  const setNav = (open) => {
    nav?.classList.toggle('open', open);
    toggle?.setAttribute('aria-expanded', String(open));
    document.body.classList.toggle('nav-open', open);
  };
  toggle?.addEventListener('click', () => setNav(!nav?.classList.contains('open')));
  nav?.addEventListener('click', (event) => { if (event.target.closest('a')) setNav(false); });
  document.addEventListener('click', (event) => { if (!event.target.closest('.nav-wrap')) setNav(false); });
  window.addEventListener('resize', () => { if (window.innerWidth > 980) setNav(false); });
  document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && nav?.classList.contains('open')) { setNav(false); toggle?.focus(); } });
  $('.site-nav .active')?.setAttribute('aria-current', 'page');
  document.body.classList.add('is-ready');

  function bindFilters(toolbarSelector, itemSelector, emptySelector) {
    const buttons = $$(toolbarSelector);
    const items = $$(itemSelector);
    const empty = emptySelector && $(emptySelector);
    const apply = (button) => {
      buttons.forEach((candidate) => {
        const selected = candidate === button;
        candidate.classList.toggle('active', selected);
        candidate.setAttribute('aria-pressed', String(selected));
      });
      let visible = 0;
      items.forEach((item) => {
        const show = button.dataset.filter === 'all' || item.dataset.category === button.dataset.filter;
        item.hidden = !show;
        item.classList.toggle('filter-visible', show);
        if (show) visible++;
      });
      if (empty) empty.hidden = visible > 0 || !items.length;
    };
    if (buttons.length) apply(buttons[0]);
    buttons.forEach((button) => button.addEventListener('click', () => apply(button)));
  }
  bindFilters('.page-gallery .filter-btn', '.page-gallery .gallery-card', '#gallery-empty');
  bindFilters('.product-filter', '.page-store .product-card', '#store-empty');

  function scrollCards(list, direction) {
    if (!list) return;
    const card = list.firstElementChild;
    list.scrollBy({ left: direction * ((card?.getBoundingClientRect().width || 300) + 24), behavior: motion });
  }
  const albums = $('#album-carousel');
  $('#album-prev')?.addEventListener('click', () => scrollCards(albums, -1));
  $('#album-next')?.addEventListener('click', () => scrollCards(albums, 1));
  const awards = $('#awardsWrapper');
  $$('[data-awards-direction]').forEach((button) => button.addEventListener('click', () => scrollCards(awards, Number(button.dataset.awardsDirection))));

  function bindDialog(dialog) {
    dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
    $('[data-dismiss-dialog]', dialog)?.addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => {
      $('iframe', dialog)?.removeAttribute('src');
      document.body.classList.remove('dialog-open');
    });
  }
  function openDialog(dialog) {
    if (!dialog?.showModal || dialog.open) return;
    dialog.showModal();
    document.body.classList.add('dialog-open');
  }
  $$('dialog').forEach(bindDialog);
  $$('[data-song-detail]').forEach((button) => button.addEventListener('click', () => {
    const dialog = document.getElementById(button.dataset.songDetail);
    const player = dialog && $('[data-player-src]', dialog);
    if (player) player.src = player.dataset.playerSrc;
    openDialog(dialog);
  }));

  const gallery = $('#gallery-dialog');
  $$('[data-gallery-image]').forEach((button) => button.addEventListener('click', () => {
    if (!gallery) return;
    const image = $('img', gallery);
    image.src = button.dataset.galleryImage;
    image.alt = button.dataset.galleryTitle;
    $('#gallery-title', gallery).textContent = button.dataset.galleryTitle;
    openDialog(gallery);
  }));

  let videoDialog;
  $$('a[data-youtube-id]').forEach((link) => link.addEventListener('click', (event) => {
    const id = link.dataset.youtubeId;
    if (!/^[\w-]{11}$/.test(id) || !window.HTMLDialogElement?.prototype.showModal || event.metaKey || event.ctrlKey) return;
    event.preventDefault();
    if (!videoDialog) {
      videoDialog = document.createElement('dialog');
      videoDialog.className = 'media-dialog';
      videoDialog.setAttribute('aria-labelledby', 'archive-video-title');
      videoDialog.innerHTML = '<div class="media-dialog-shell"><div class="media-dialog-header"><h2 id="archive-video-title">Queen video</h2><button class="dialog-close" type="button" data-dismiss-dialog aria-label="Close video">×</button></div><div class="responsive-embed"><iframe title="Queen video" allow="accelerometer; autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe></div><div class="media-dialog-footer"><a class="btn btn-secondary" target="_blank" rel="noopener">Open on YouTube</a></div></div>';
      document.body.append(videoDialog);
      bindDialog(videoDialog);
    }
    const title = link.getAttribute('aria-label')?.replace(/^Play /, '') || 'Queen video';
    $('h2', videoDialog).textContent = title;
    const player = $('iframe', videoDialog);
    player.title = title;
    player.src = `https://www.youtube-nocookie.com/embed/${id}?autoplay=1&rel=0`;
    $('a', videoDialog).href = link.href;
    openDialog(videoDialog);
  }));
})();
