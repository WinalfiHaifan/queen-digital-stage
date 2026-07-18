(() => {
  'use strict';

  document.documentElement.classList.add('js');

  const $ = (selector, context = document) => context.querySelector(selector);
  const $$ = (selector, context = document) => Array.from(context.querySelectorAll(selector));
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // ---------------------------------------------------------------------------
  // Shared page shell: active navigation, mobile navigation, and scroll progress
  // ---------------------------------------------------------------------------
  const currentFile = window.location.pathname.split('/').pop() || 'index.html';
  $$('.site-nav a').forEach((link) => {
    if (link.getAttribute('href') === currentFile) {
      link.classList.add('active');
      link.setAttribute('aria-current', 'page');
    }
  });

  const navToggle = $('.nav-toggle');
  const siteNav = $('.site-nav');
  const closeNavigation = () => {
    if (!navToggle || !siteNav) return;
    siteNav.classList.remove('open');
    navToggle.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('nav-open');
  };

  if (navToggle && siteNav) {
    navToggle.addEventListener('click', () => {
      const isOpen = siteNav.classList.toggle('open');
      navToggle.setAttribute('aria-expanded', String(isOpen));
      document.body.classList.toggle('nav-open', isOpen);
    });

    siteNav.addEventListener('click', (event) => {
      if (event.target.closest('a')) closeNavigation();
    });

    document.addEventListener('click', (event) => {
      if (!siteNav.classList.contains('open')) return;
      if (!event.target.closest('.nav-wrap')) closeNavigation();
    });

    window.addEventListener('resize', () => {
      if (window.innerWidth > 980) closeNavigation();
    });
  }

  const progress = document.createElement('div');
  progress.className = 'scroll-progress';
  progress.setAttribute('aria-hidden', 'true');
  progress.innerHTML = '<span></span>';
  document.body.prepend(progress);
  const progressBar = $('span', progress);

  const updateScrollProgress = () => {
    const total = document.documentElement.scrollHeight - window.innerHeight;
    const percentage = total > 0 ? Math.min(100, Math.max(0, (window.scrollY / total) * 100)) : 0;
    progressBar.style.transform = `scaleX(${percentage / 100})`;
  };
  updateScrollProgress();
  window.addEventListener('scroll', updateScrollProgress, { passive: true });
  window.addEventListener('resize', updateScrollProgress);

  // ---------------------------------------------------------------------------
  // Accessible, lightweight reveal animations across all eight pages
  // ---------------------------------------------------------------------------
  const revealSelectors = [
    '.section-heading', '.hero-copy', '.hero-text', '.hero-panel', '.hero-card',
    '.explore-card', '.song-card', '.album-card', '.member-card', '.fact-card',
    '.timeline-card', '.gallery-card', '.video-card', '.award-card', '.story-card',
    '.product-card', '.support-card', '.faq-box', '.community-form', '.content-page > *',
    '.two-col > *', '.about-intro > *', '.cta-panel', '.cta-box', '.embed-card',
    '.lyrics-copy', '.lyrics-finder-panel'
  ];

  const revealElements = $$(revealSelectors.join(','));
  revealElements.forEach((element, index) => {
    element.dataset.reveal = '';
    element.style.setProperty('--reveal-delay', `${Math.min(index % 6, 5) * 55}ms`);
  });

  if (prefersReducedMotion || !('IntersectionObserver' in window)) {
    revealElements.forEach((element) => element.classList.add('is-visible'));
  } else {
    const revealObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
    revealElements.forEach((element) => revealObserver.observe(element));
  }

  requestAnimationFrame(() => document.body.classList.add('is-ready'));

  // Improve image loading behavior without changing the single shared image folder.
  $$('img').forEach((image) => {
    image.addEventListener('load', () => image.classList.add('is-loaded'), { once: true });
    if (image.complete) image.classList.add('is-loaded');
  });

  // ---------------------------------------------------------------------------
  // Reusable animated filters: Gallery and Store
  // ---------------------------------------------------------------------------
  function setupFilters(buttonSelector, itemSelector) {
    const buttons = $$(buttonSelector);
    const items = $$(itemSelector);
    if (!buttons.length || !items.length) return;

    buttons.forEach((button) => {
      button.addEventListener('click', () => {
        buttons.forEach((item) => {
          item.classList.remove('active');
          item.setAttribute('aria-pressed', 'false');
        });
        button.classList.add('active');
        button.setAttribute('aria-pressed', 'true');
        const filter = button.dataset.filter || 'all';

        items.forEach((item) => {
          const shouldShow = filter === 'all' || item.dataset.category === filter;
          if (shouldShow) {
            item.hidden = false;
            requestAnimationFrame(() => item.classList.add('filter-visible'));
          } else {
            item.classList.remove('filter-visible');
            window.setTimeout(() => { item.hidden = true; }, prefersReducedMotion ? 0 : 180);
          }
        });
      });
    });

    items.forEach((item) => item.classList.add('filter-visible'));
  }

  setupFilters('.page-gallery .filter-btn', '.page-gallery .gallery-card');
  setupFilters('.product-filter', '.product-card');

  // ---------------------------------------------------------------------------
  // In-page YouTube dialog shared by Home, History, Gallery, and other pages
  // ---------------------------------------------------------------------------
  let mediaDialog;
  let mediaFrame;
  let mediaFallback;
  let mediaTitle;

  function ensureMediaDialog() {
    if (mediaDialog) return mediaDialog;
    mediaDialog = document.createElement('dialog');
    mediaDialog.className = 'media-dialog';
    mediaDialog.innerHTML = `
      <div class="media-dialog-shell">
        <div class="media-dialog-header">
          <div><span class="platform-badge youtube-badge">YouTube</span><h2 id="media-dialog-title">Queen video</h2></div>
          <button class="dialog-close" type="button" aria-label="Close video">×</button>
        </div>
        <div class="responsive-embed"><iframe title="Queen YouTube video" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe></div>
        <div class="media-dialog-footer"><p>Playback stays inside this website when embedding is allowed by the video owner.</p><a class="btn btn-secondary" target="_blank" rel="noopener">Open YouTube fallback</a></div>
      </div>`;
    document.body.append(mediaDialog);
    mediaFrame = $('iframe', mediaDialog);
    mediaFallback = $('a', mediaDialog);
    mediaTitle = $('#media-dialog-title', mediaDialog);

    $('.dialog-close', mediaDialog).addEventListener('click', () => mediaDialog.close());
    mediaDialog.addEventListener('click', (event) => {
      if (event.target === mediaDialog) mediaDialog.close();
    });
    mediaDialog.addEventListener('close', () => {
      mediaFrame.src = 'about:blank';
      document.body.classList.remove('dialog-open');
    });
    return mediaDialog;
  }

  $$('a[data-youtube-id], .js-video-link').forEach((link) => {
    link.addEventListener('click', (event) => {
      const videoId = link.dataset.youtubeId;
      if (!videoId) return;
      event.preventDefault();
      const dialog = ensureMediaDialog();
      const title = link.dataset.youtubeTitle || link.getAttribute('aria-label') || 'Queen video';
      mediaTitle.textContent = title.replace(/^Open\s+/i, '').replace(/\s+on YouTube$/i, '');
      mediaFrame.src = `https://www.youtube-nocookie.com/embed/${encodeURIComponent(videoId)}?autoplay=1&rel=0&modestbranding=1`;
      mediaFallback.href = link.href;
      document.body.classList.add('dialog-open');
      if (typeof dialog.showModal === 'function') dialog.showModal();
      else window.location.href = link.href;
    });
  });

  // ---------------------------------------------------------------------------
  // Music page: one source of truth for song library, stories, and lyrics finder
  // ---------------------------------------------------------------------------
  const songs = [
    {
      title: 'Bohemian Rhapsody', year: 1975, era: '1970s', album: 'A Night at the Opera', writer: 'Freddie Mercury', type: 'Opera rock', image: 'album-night-at-the-opera.webp', youtube: 'fJ9rUzIMcZQ',
      summary: 'A multi-section composition that moves from intimate ballad to operatic drama and hard rock.',
      story: 'The song presents contrasting musical sections as one continuous theatrical experience. Its layered vocals, sudden changes, and ambitious studio construction became a defining example of Queen treating the recording studio as part of the composition.'
    },
    {
      title: "You're My Best Friend", year: 1975, era: '1970s', album: 'A Night at the Opera', writer: 'John Deacon', type: 'Pop rock', image: 'album-night-at-the-opera.webp', youtube: 'HaZpZQG2z10',
      summary: 'A warm, melodic song built around electric piano and an affectionate directness.',
      story: 'John Deacon wrote the song with a simpler and more intimate emotional tone than many of Queen’s theatrical tracks. The electric-piano foundation and compact arrangement give it a bright character while the harmonies keep the recognizable Queen sound.'
    },
    {
      title: 'Love of My Life', year: 1975, era: '1970s', album: 'A Night at the Opera', writer: 'Freddie Mercury', type: 'Ballad', image: 'album-night-at-the-opera.webp', youtube: 'sUJkCXE4sAA',
      summary: 'A piano-led ballad that later became a powerful audience sing-along in concert.',
      story: 'The studio version is delicate and carefully arranged, while live performances transformed the song into a shared moment between the band and the audience. That contrast demonstrates how Queen often allowed songs to develop a second identity on stage.'
    },
    {
      title: 'Somebody to Love', year: 1976, era: '1970s', album: 'A Day at the Races', writer: 'Freddie Mercury', type: 'Gospel rock', image: 'queen-group-shadow-music.webp', youtube: 'kijpcUv-b8M',
      summary: 'Gospel-inspired vocal layering supports a song about longing, doubt, and emotional intensity.',
      story: 'Rather than using a large choir, the band built the choir effect through repeated vocal overdubs. The arrangement combines gospel influence with rock dynamics, giving the central emotional question a large and communal sound.'
    },
    {
      title: 'Good Old-Fashioned Lover Boy', year: 1976, era: '1970s', album: 'A Day at the Races', writer: 'Freddie Mercury', type: 'Music hall pop', image: 'queen-group-classic-music.webp', youtube: '',
      summary: 'A playful, tightly arranged song influenced by music hall and theatrical pop.',
      story: 'The song uses polished harmonies, light rhythmic shifts, and humorous elegance. It represents the band’s ability to place older theatrical traditions inside a modern rock album without losing their own identity.'
    },
    {
      title: 'We Will Rock You', year: 1977, era: '1970s', album: 'News of the World', writer: 'Brian May', type: 'Arena anthem', image: 'album-news-of-the-world.webp', youtube: '-tJYN-eG1zk',
      summary: 'The famous stomp-stomp-clap pattern turns listeners into part of the performance.',
      story: 'Brian May designed the song around participation rather than dense instrumentation. The rhythm leaves space for a crowd to join immediately, and the guitar ending delivers the final release after the deliberately minimal body of the track.'
    },
    {
      title: 'We Are the Champions', year: 1977, era: '1970s', album: 'News of the World', writer: 'Freddie Mercury', type: 'Victory anthem', image: 'album-news-of-the-world.webp', youtube: '04854XqcfCY',
      summary: 'A piano-led anthem that balances struggle, persistence, and public celebration.',
      story: 'Although widely associated with victory, the verses emphasize effort and difficulty before the large chorus arrives. That structure makes the final declaration feel earned and helps explain its continuing use in collective celebrations.'
    },
    {
      title: 'Spread Your Wings', year: 1977, era: '1970s', album: 'News of the World', writer: 'John Deacon', type: 'Narrative rock', image: 'album-news-of-the-world.webp', youtube: '',
      summary: 'A character-driven song about leaving a limited situation and pursuing a larger life.',
      story: 'John Deacon frames the song as a compact narrative. Its direct encouragement and gradually expanding arrangement give the central character’s decision a sense of movement and possibility.'
    },
    {
      title: 'Bicycle Race', year: 1978, era: '1970s', album: 'Jazz', writer: 'Freddie Mercury', type: 'Art pop', image: 'queen-group-classic-music.webp', youtube: 'GugsCdLHm-Q',
      summary: 'Fast changes, stacked vocals, and playful contradictions create an eccentric pop-rock piece.',
      story: 'The arrangement deliberately changes direction and tone, matching the song’s rapid sequence of ideas. Its unusual structure and theatrical delivery show Queen’s comfort with making a single feel unpredictable while remaining memorable.'
    },
    {
      title: 'Fat Bottomed Girls', year: 1978, era: '1970s', album: 'Jazz', writer: 'Brian May', type: 'Hard rock', image: 'queen-group-classic-music.webp', youtube: 'VMnjF1O4eH0',
      summary: 'A heavy guitar groove and broad vocal harmonies give the song its direct rock character.',
      story: 'The track emphasizes guitar weight, rhythmic confidence, and a chorus designed for immediate impact. It is often paired conceptually with Bicycle Race, showing two very different sides of the same album era.'
    },
    {
      title: "Don't Stop Me Now", year: 1978, era: '1970s', album: 'Jazz', writer: 'Freddie Mercury', type: 'Piano rock', image: 'queen-group-classic-music.webp', youtube: 'HgzGwKwLmgM',
      summary: 'Rapid piano, accelerating vocals, and a bright chorus create uninterrupted momentum.',
      story: 'The arrangement is built to feel as though it is continuously gaining speed. Piano drives much of the track, while guitar is used selectively, allowing the rhythm and vocal performance to carry its sense of exhilaration.'
    },
    {
      title: 'Crazy Little Thing Called Love', year: 1979, era: '1970s', album: 'The Game', writer: 'Freddie Mercury', type: 'Rockabilly', image: 'album-the-game.webp', youtube: 'zO6D_BAuYCI',
      summary: 'A concise rockabilly-inspired song that highlights Queen’s stylistic flexibility.',
      story: 'Instead of the band’s densest studio style, the track uses a leaner arrangement and a retro rhythmic feel. Its simplicity is deliberate, demonstrating that Queen could create a strong identity without relying on maximal production.'
    },
    {
      title: 'Play the Game', year: 1980, era: '1980s', album: 'The Game', writer: 'Freddie Mercury', type: 'Pop rock', image: 'album-the-game.webp', youtube: '6_5O-nUiZ_0',
      summary: 'A romantic song that blends piano-led writing with a polished electronic texture.',
      story: 'The track marks a period in which Queen became more open to synthesizer textures while preserving vocal harmonies and guitar detail. Its smooth arrangement serves as an entry point into the broader sound of The Game.'
    },
    {
      title: 'Another One Bites the Dust', year: 1980, era: '1980s', album: 'The Game', writer: 'John Deacon', type: 'Funk rock', image: 'album-the-game.webp', youtube: 'rY0WxgSXdEE',
      summary: 'A distinctive bass line anchors Queen’s move into funk- and dance-influenced rock.',
      story: 'John Deacon’s bass part is the central organizing idea. The arrangement leaves significant space around it, using controlled guitar, percussion, and vocal effects to create a sound notably different from the band’s 1970s theatrical work.'
    },
    {
      title: 'Flash', year: 1980, era: '1980s', album: 'Flash Gordon', writer: 'Brian May', type: 'Soundtrack rock', image: 'album-the-game.webp', youtube: 'LfmrHTdXgK4',
      summary: 'A compact soundtrack theme built around dramatic calls, synthesizers, and guitar.',
      story: 'Created for a science-fiction film, the song functions as both a theme and a rock single. Dialogue fragments and cinematic transitions are integrated into the arrangement, connecting the track directly to its visual context.'
    },
    {
      title: 'Under Pressure', year: 1981, era: '1980s', album: 'Hot Space', writer: 'Queen & David Bowie', type: 'Art rock', image: 'album-greatest-hits-ii.webp', youtube: 'a01QQZyl-_I',
      summary: 'A collaborative performance built around an instantly recognizable bass line and contrasting voices.',
      story: 'The recording brings Queen and David Bowie into a shared arrangement where different vocal personalities answer and overlap one another. The song grows from tension toward a final appeal for empathy and responsibility.'
    },
    {
      title: 'Radio Ga Ga', year: 1984, era: '1980s', album: 'The Works', writer: 'Roger Taylor', type: 'Synth rock', image: 'album-greatest-hits-ii.webp', youtube: 'azdwsXLmrHE',
      summary: 'Synth-driven verses and a large hand-clap chorus examine changing relationships with radio.',
      story: 'Roger Taylor combines nostalgia for radio’s cultural role with concern about changing media habits. In concert, the synchronized audience clapping turned the electronic arrangement into a highly physical crowd moment.'
    },
    {
      title: 'I Want to Break Free', year: 1984, era: '1980s', album: 'The Works', writer: 'John Deacon', type: 'Pop rock', image: 'album-greatest-hits-ii.webp', youtube: 'f4Mc-NYPHaQ',
      summary: 'A clean pop-rock arrangement supports a direct statement about independence and release.',
      story: 'John Deacon’s writing is concise and melodic, allowing the repeated central phrase to carry multiple interpretations. The memorable video added another visual identity that became strongly associated with the track.'
    },
    {
      title: 'Hammer to Fall', year: 1984, era: '1980s', album: 'The Works', writer: 'Brian May', type: 'Hard rock', image: 'album-greatest-hits-ii.webp', youtube: 'JU5LMG3WFBw',
      summary: 'A guitar-forward track with urgent rhythm and a forceful live-performance character.',
      story: 'The song brings Queen back toward direct hard rock while keeping the polished scale of their 1980s production. Its strong rhythmic accents and guitar structure made it especially effective in large live settings.'
    },
    {
      title: 'A Kind of Magic', year: 1986, era: '1980s', album: 'A Kind of Magic', writer: 'Roger Taylor', type: 'Pop rock', image: 'album-greatest-hits-ii.webp', youtube: '0p_1QSUsbsM',
      summary: 'A sleek, optimistic song whose repeated hook gives it a cinematic and celebratory quality.',
      story: 'The song is connected with the Highlander film project but also works independently as a polished Queen single. Its arrangement balances electronic rhythm, guitar color, and an immediately recognizable refrain.'
    },
    {
      title: 'Who Wants to Live Forever', year: 1986, era: '1980s', album: 'A Kind of Magic', writer: 'Brian May', type: 'Orchestral rock', image: 'album-greatest-hits-ii.webp', youtube: '_Jtpf8N5IDE',
      summary: 'Orchestral scale and restrained vocals explore mortality, time, and the value of love.',
      story: 'Written for a dramatic film context, the song expands from quiet reflection into a large orchestral climax. The contrast between immortality and limited human time gives the arrangement its emotional weight.'
    },
    {
      title: 'I Want It All', year: 1989, era: '1980s', album: 'The Miracle', writer: 'Queen', type: 'Hard rock anthem', image: 'album-greatest-hits-ii.webp', youtube: 'hFDcoX7s6rE',
      summary: 'A direct, guitar-heavy anthem driven by ambition, urgency, and a large chorus.',
      story: 'The song uses a compact statement of determination and surrounds it with heavy guitar and group vocals. Its assertive structure helped it become one of the clearest rock anthems from Queen’s later period.'
    },
    {
      title: 'Innuendo', year: 1991, era: '1990s', album: 'Innuendo', writer: 'Queen', type: 'Progressive rock', image: 'album-greatest-hits-ii.webp', youtube: 'g2N0TkfrQhY',
      summary: 'An expansive late-period composition with dramatic sections and progressive-rock scale.',
      story: 'The track revisits Queen’s interest in large forms, moving through contrasting textures and rhythmic ideas. Its scale links the band’s early experimental ambition with the darker atmosphere of their final studio era with Freddie Mercury.'
    },
    {
      title: 'The Show Must Go On', year: 1991, era: '1990s', album: 'Innuendo', writer: 'Queen', type: 'Arena rock', image: 'album-greatest-hits-ii.webp', youtube: 't99KH0TR-J4',
      summary: 'A powerful late-period song about endurance, performance, and continuing despite difficulty.',
      story: 'The arrangement is built around rising tension and a demanding vocal line. Its language of performance and persistence gained additional emotional significance within the context of Queen’s final recordings with Freddie Mercury.'
    }
  ];

  const songGrid = $('#song-library-grid');
  const storyGrid = $('#story-grid');
  const lyricsResults = $('#lyrics-results');
  const songSearch = $('#song-search');
  const songEra = $('#song-era');
  const resetSongLibrary = $('#reset-song-library');
  const songResultsSummary = $('#song-results-summary');
  const lyricsSearch = $('#lyrics-search');
  const lyricsResultsSummary = $('#lyrics-results-summary');
  const songDialog = $('#song-dialog');

  const normalize = (value) => String(value || '').toLocaleLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  const searchableText = (song) => normalize([song.title, song.year, song.era, song.album, song.writer, song.type, song.summary].join(' '));
  const lyricsUrl = (song) => `https://www.google.com/search?q=${encodeURIComponent(`Queen ${song.title} official lyrics`)}`;

  function songCardMarkup(song, index) {
    const videoAction = song.youtube
      ? `<button class="btn btn-primary song-play-button" data-play-song="${index}" type="button">Play video</button>`
      : '<span class="unavailable-note">Story + lyrics search</span>';
    return `
      <article class="song-result-card" data-song-index="${index}">
        <div class="song-cover-wrap"><img src="assets/images/${song.image}" alt="${song.album} visual for ${song.title}" loading="lazy" decoding="async"><span class="song-year">${song.year}</span></div>
        <div class="song-result-body">
          <div class="track-meta"><span>${song.album}</span><span>${song.type}</span></div>
          <h3>${song.title}</h3>
          <p>${song.summary}</p>
          <p class="song-writer">Written by ${song.writer}</p>
          <div class="card-actions">${videoAction}<button class="btn btn-secondary" data-story-song="${index}" type="button">Read story</button></div>
        </div>
      </article>`;
  }

  function storyCardMarkup(song, index) {
    return `
      <article class="story-card-dynamic">
        <span class="story-number">${String(index + 1).padStart(2, '0')}</span>
        <div><p class="story-meta">${song.year} · ${song.album}</p><h3>${song.title}</h3><p>${song.story}</p></div>
        <button class="text-button" data-story-song="${index}" type="button">Open details →</button>
      </article>`;
  }

  function lyricsRowMarkup(song, index) {
    return `
      <article class="lyrics-result">
        <div><span class="lyrics-index">${String(index + 1).padStart(2, '0')}</span><div><h3>${song.title}</h3><p>${song.album} · ${song.year} · ${song.writer}</p></div></div>
        <div class="result-actions"><button class="btn btn-secondary" data-story-song="${songs.indexOf(song)}" type="button">Story</button><a class="btn btn-ghost" href="${lyricsUrl(song)}" target="_blank" rel="noopener">Find lyrics</a></div>
      </article>`;
  }

  function filteredSongs(query = '', era = 'all') {
    const normalizedQuery = normalize(query.trim());
    return songs.filter((song) => {
      const matchesQuery = !normalizedQuery || searchableText(song).includes(normalizedQuery);
      const matchesEra = era === 'all' || song.era === era;
      return matchesQuery && matchesEra;
    });
  }

  function renderSongLibrary() {
    if (!songGrid) return;
    const matches = filteredSongs(songSearch?.value || '', songEra?.value || 'all');
    songGrid.innerHTML = matches.map((song) => songCardMarkup(song, songs.indexOf(song))).join('');
    if (songResultsSummary) songResultsSummary.textContent = `${matches.length} of ${songs.length} songs shown.`;
    bindDynamicSongActions(songGrid);
  }

  function renderStories() {
    if (!storyGrid) return;
    storyGrid.innerHTML = songs.map(storyCardMarkup).join('');
    bindDynamicSongActions(storyGrid);
  }

  function renderLyrics() {
    if (!lyricsResults) return;
    const matches = filteredSongs(lyricsSearch?.value || '', 'all');
    lyricsResults.innerHTML = matches.length
      ? matches.map(lyricsRowMarkup).join('')
      : '<div class="empty-state"><h3>No matching song found.</h3><p>Try a shorter title, album name, year, or songwriter.</p></div>';
    if (lyricsResultsSummary) lyricsResultsSummary.textContent = `${matches.length} result${matches.length === 1 ? '' : 's'} found.`;
    bindDynamicSongActions(lyricsResults);
  }

  function loadSongVideo(song) {
    if (!song.youtube) return;
    const player = $('#youtube-main-player');
    const playerTitle = $('#youtube-player-title');
    const status = $('#youtube-player-status');
    if (!player) return;
    player.src = `https://www.youtube-nocookie.com/embed/${encodeURIComponent(song.youtube)}?autoplay=1&rel=0&modestbranding=1`;
    player.title = `${song.title} by Queen on YouTube`;
    if (playerTitle) playerTitle.textContent = song.title;
    if (status) status.textContent = `${song.title} is loaded in the in-page YouTube player.`;
    $('#media-center')?.scrollIntoView({ behavior: prefersReducedMotion ? 'auto' : 'smooth', block: 'start' });
  }

  function openSongDialog(song, index) {
    if (!songDialog) return;
    $('#song-dialog-meta').textContent = `${song.year} · ${song.album} · ${song.type}`;
    $('#song-dialog-title').textContent = song.title;
    $('#song-dialog-story').textContent = song.story;
    $('#song-dialog-details').innerHTML = `<span><strong>Writer</strong>${song.writer}</span><span><strong>Era</strong>${song.era}</span><span><strong>Library no.</strong>${String(index + 1).padStart(2, '0')}</span>`;
    const actions = $('#song-dialog-actions');
    actions.innerHTML = '';
    if (song.youtube) {
      const playButton = document.createElement('button');
      playButton.className = 'btn btn-primary';
      playButton.type = 'button';
      playButton.textContent = 'Play in website';
      playButton.addEventListener('click', () => {
        songDialog.close();
        loadSongVideo(song);
      });
      actions.append(playButton);
    }
    const lyricsLink = document.createElement('a');
    lyricsLink.className = 'btn btn-secondary';
    lyricsLink.href = lyricsUrl(song);
    lyricsLink.target = '_blank';
    lyricsLink.rel = 'noopener';
    lyricsLink.textContent = 'Find lyrics source';
    actions.append(lyricsLink);
    document.body.classList.add('dialog-open');
    if (typeof songDialog.showModal === 'function') songDialog.showModal();
  }

  function bindDynamicSongActions(context) {
    $$('[data-play-song]', context).forEach((button) => {
      button.addEventListener('click', () => loadSongVideo(songs[Number(button.dataset.playSong)]));
    });
    $$('[data-story-song]', context).forEach((button) => {
      button.addEventListener('click', () => {
        const index = Number(button.dataset.storySong);
        openSongDialog(songs[index], index);
      });
    });
  }

  if (songGrid || storyGrid || lyricsResults) {
    renderSongLibrary();
    renderStories();
    renderLyrics();

    songSearch?.addEventListener('input', renderSongLibrary);
    songEra?.addEventListener('change', renderSongLibrary);
    resetSongLibrary?.addEventListener('click', () => {
      if (songSearch) songSearch.value = '';
      if (songEra) songEra.value = 'all';
      renderSongLibrary();
      songSearch?.focus();
    });
    lyricsSearch?.addEventListener('input', renderLyrics);

    $$('[data-close-dialog]', songDialog).forEach((button) => button.addEventListener('click', () => songDialog.close()));
    songDialog?.addEventListener('click', (event) => {
      if (event.target === songDialog) songDialog.close();
    });
    songDialog?.addEventListener('close', () => document.body.classList.remove('dialog-open'));
  }

  // ---------------------------------------------------------------------------
  // Store demo: persistent cart count and visual confirmation
  // ---------------------------------------------------------------------------
  const cartDisplay = $('#cart-count');
  const cartTotalDisplay = $('#cart-total');
  let cartTotal = 0;
  const safeStorage = {
    get(key, fallback = '0') {
      try { return window.localStorage.getItem(key) ?? fallback; }
      catch (error) { return fallback; }
    },
    set(key, value) {
      try { window.localStorage.setItem(key, value); }
      catch (error) { /* Storage can be unavailable in private or sandboxed contexts. */ }
    }
  };
  let cartCount = Number(safeStorage.get('queenCartCount', '0'));
  if (cartDisplay) cartDisplay.textContent = String(cartCount);

$$('[data-add-cart]').forEach((button) => {

    button.addEventListener('click',()=>{


        const price = Number(button.dataset.price || 0);


        cartCount += 1;

        cartTotal += price;


        safeStorage.set(
            'queenCartCount',
            String(cartCount)
        );


        if(cartDisplay){

            cartDisplay.textContent = String(cartCount);

        }


        if(cartTotalDisplay){

            cartTotalDisplay.textContent = `$${cartTotal}`;

        }



        const originalText =
        button.dataset.originalText || button.textContent;


        button.dataset.originalText = originalText;


        button.textContent="Added ✓";


        setTimeout(()=>{

            button.textContent=originalText;

        },1000);



    });


});

  // ---------------------------------------------------------------------------
  // Fan Zone form: client-side feedback and saved demo submission
  // ---------------------------------------------------------------------------
  const communityForm = $('#community-form');
  if (communityForm) {
    const message = $('#form-message');
    communityForm.addEventListener('submit', (event) => {
      event.preventDefault();
      const formData = new FormData(communityForm);
      const name = String(formData.get('name') || $('#name')?.value || 'Queen fan').trim();
      if (message) message.textContent = `Welcome, ${name || 'Queen fan'}! Your interest has been recorded on this device.`;
      communityForm.classList.add('form-success');
      window.setTimeout(() => communityForm.classList.remove('form-success'), 900);
      communityForm.reset();
    });
  }

  // ---------------------------------------------------------------------------
  // History awards carousel: buttons, keyboard support, and responsive distance
  // ---------------------------------------------------------------------------
  const awardsWrapper = $('#awardsWrapper');
  $$('[data-awards-direction]').forEach((button) => {
    button.addEventListener('click', () => {
      if (!awardsWrapper) return;
      const direction = Number(button.dataset.awardsDirection) || 1;
      const card = $('.award-card', awardsWrapper);
      const distance = card ? card.getBoundingClientRect().width + 20 : 320;
      awardsWrapper.scrollBy({ left: direction * distance, behavior: prefersReducedMotion ? 'auto' : 'smooth' });
    });
  });

  awardsWrapper?.addEventListener('keydown', (event) => {
    if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
    event.preventDefault();
    awardsWrapper.scrollBy({ left: (event.key === 'ArrowRight' ? 1 : -1) * 320, behavior: prefersReducedMotion ? 'auto' : 'smooth' });
  });

  // Global Escape behavior for open navigation and dialogs.
  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    closeNavigation();
    if (mediaDialog?.open) mediaDialog.close();
    if (songDialog?.open) songDialog.close();
  });
})();

// =====================
// QUEEN IMAGE LIGHTBOX
// =====================


const galleryImages =
document.querySelectorAll(".lightbox-img");


const lightbox =
document.getElementById("imageLightbox");


const lightboxImage =
document.getElementById("lightboxImage");


const lightboxCaption =
document.getElementById("lightboxCaption");


const closeLightbox =
document.querySelector(".lightbox-close");



if (lightbox && lightboxImage && lightboxCaption && closeLightbox) {

galleryImages.forEach(image=>{


image.style.cursor="zoom-in";


image.addEventListener("click",()=>{


lightboxImage.src=image.src;


lightboxImage.alt=image.alt;


lightboxCaption.innerHTML=image.alt;


lightbox.classList.add("active");


});


});



closeLightbox.addEventListener("click",()=>{


lightbox.classList.remove("active");


});



lightbox.addEventListener("click",(e)=>{


if(e.target === lightbox){

lightbox.classList.remove("active");

}


});



document.addEventListener("keydown",(e)=>{

  if(e.key==="Escape"){
    lightbox.classList.remove("active");
  }

});

}

// STORE CATEGORY FILTER

const productFilters = document.querySelectorAll(".product-filter");
const productCards = document.querySelectorAll(".product-card");


productFilters.forEach(filter=>{

    filter.addEventListener("click",()=>{


        const category = filter.dataset.filter;


        productFilters.forEach(btn=>{
            btn.classList.remove("active");
        });


        filter.classList.add("active");



        productCards.forEach(card=>{


            if(
                category === "all" ||
                card.dataset.category === category
            ){

                card.style.display="flex";

            }else{

                card.style.display="none";

            }


        });


    });


});
