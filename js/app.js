// =============================================
// FaithTunes - Main Application JS
// =============================================

// ===== Particles =====
(function createParticles() {
  var c = document.getElementById('particles');
  if (!c) return;
  var n = window.innerWidth <= 768 ? 20 : 40;
  for (var i = 0; i < n; i++) {
    var p = document.createElement('div');
    p.className = 'particle';
    p.style.left = Math.random() * 100 + '%';
    p.style.animationDelay = Math.random() * 20 + 's';
    p.style.animationDuration = (15 + Math.random() * 10) + 's';
    c.appendChild(p);
  }
})();

// ===== Theme Toggle =====
function toggleTheme() {
  var html = document.documentElement;
  var cur = html.getAttribute('data-theme') || 'dark';
  var next = cur === 'dark' ? 'light' : 'dark';
  html.setAttribute('data-theme', next);
  localStorage.setItem('ft_theme', next);
  var icon = document.querySelector('#themeBtn i');
  if (icon) icon.className = next === 'light' ? 'fas fa-sun' : 'fas fa-moon';
}
(function applyTheme() {
  var saved = localStorage.getItem('ft_theme') || 'light';
  document.documentElement.setAttribute('data-theme', saved);
  var icon = document.querySelector('#themeBtn i');
  if (icon) icon.className = saved === 'light' ? 'fas fa-sun' : 'fas fa-moon';
})();

// ===== i18n (translations loaded from i18n.js) =====

function detectLang() {
  var s = localStorage.getItem('ft_lang');
  if (s && L[s]) return s;
  var navLangs = navigator.languages || [navigator.language || ''];
  for (var i = 0; i < navLangs.length; i++) {
    var c = navLangs[i].split('-')[0].toLowerCase();
    if (c === 'no') c = 'nb';
    if (L[c]) return c;
  }
  try {
    var tz = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
    if (/Sao_Paulo|Fortaleza|Recife|Belem|Manaus|Cuiaba|Bahia/.test(tz)) return 'pt';
    if (/New_York|Chicago|Denver|Los_Angeles|Toronto|London/.test(tz)) return 'en';
    if (/Berlin|Vienna|Zurich/.test(tz)) return 'de';
    if (/Paris/.test(tz)) return 'fr';
    if (/Rome/.test(tz)) return 'it';
    if (/Warsaw/.test(tz)) return 'pl';
    if (/Moscow/.test(tz)) return 'ru';
    if (/Kiev/.test(tz)) return 'uk';
    if (/Stockholm/.test(tz)) return 'sv';
    if (/Helsinki/.test(tz)) return 'fi';
    if (/Oslo/.test(tz)) return 'nb';
    if (/Riga/.test(tz)) return 'lv';
    if (/Ljubljana/.test(tz)) return 'sl';
    if (/Tokyo/.test(tz)) return 'ja';
    if (/Seoul/.test(tz)) return 'ko';
  } catch(e) {}
  return 'es';
}

function applyLang(lang) {
  var d = L[lang] || L.es;
  document.querySelectorAll('[data-i18n]').forEach(function(el) {
    var k = el.getAttribute('data-i18n');
    if (d[k]) el.innerHTML = d[k];
  });
  document.documentElement.lang = lang;
  document.documentElement.dir = RTL_LANGS.indexOf(lang) >= 0 ? 'rtl' : 'ltr';
  document.title = 'FaithTunes - ' + (d['hero.badge'] || L.es['hero.badge']).replace(/<[^>]*>/g, '');
  var currentEl = document.querySelector('.lang-globe-current');
  if (currentEl) currentEl.textContent = lang.toUpperCase();
  document.querySelectorAll('.lang-dropdown-item').forEach(function(item) {
    item.classList.toggle('active', item.dataset.lang === lang);
  });
}

window.setLang = function(l) {
  if (!L[l]) return;
  localStorage.setItem('ft_lang', l);
  applyLang(l);
};

// ===== Language Globe Dropdown =====
function buildLangDropdown() {
  var dd = document.getElementById('langDropdown');
  if (!dd) return;
  var cur = detectLang();
  var html = '';
  LANGS.forEach(function(lang) {
    html += '<div class="lang-dropdown-item' + (lang.code === cur ? ' active' : '') + '" data-lang="' + lang.code + '" onclick="selectLang(\'' + lang.code + '\')">'
      + '<span class="lang-flag">' + lang.flag + '</span>'
      + '<span class="lang-name">' + lang.name + '</span>'
      + '</div>';
  });
  dd.innerHTML = html;
}

function toggleLangDropdown() {
  var dd = document.getElementById('langDropdown');
  if (!dd) return;
  dd.classList.toggle('active');
  // Mobile overlay
  var ov = document.getElementById('langOverlay');
  if (ov) ov.classList.toggle('active', dd.classList.contains('active'));
}

function selectLang(code) {
  setLang(code);
  var dd = document.getElementById('langDropdown');
  if (dd) dd.classList.remove('active');
  var ov = document.getElementById('langOverlay');
  if (ov) ov.classList.remove('active');
}

document.addEventListener('click', function(e) {
  if (!e.target.closest('.lang-globe-wrapper')) {
    var dd = document.getElementById('langDropdown');
    if (dd) dd.classList.remove('active');
    var ov = document.getElementById('langOverlay');
    if (ov) ov.classList.remove('active');
  }
});

// Init
(function() {
  var lang = detectLang();
  applyLang(lang);
  buildLangDropdown();
  loadAISongs();
})();

// ===== AI Songs Gallery =====
function loadAISongs() {
  var grid = document.getElementById('aiSongsGrid');
  if (!grid) return;
  fetch('api/save-song.php?action=list')
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (!data.songs || data.songs.length === 0) {
      grid.innerHTML = '<div style="text-align:center;color:var(--gray);padding:2rem;grid-column:1/-1"><i class="fas fa-music" style="font-size:2rem;opacity:0.3;display:block;margin-bottom:0.5rem"></i>Aun no hay canciones. Se la primera persona en crear una!</div>';
      return;
    }
    grid.innerHTML = '';
    data.songs.forEach(function(s) {
      var hasVideo = !!s.videoUrl;
      var card = document.createElement('div');
      card.className = 'ai-song-card';

      // Always use gradient fallback, load image on top if available
      var thumbContent = '<div class="ai-song-gradient"><i class="fas fa-music"></i></div>';
      if (s.imageUrl) {
        thumbContent = '<img src="' + s.imageUrl + '" alt="' + (s.title||'') + '" onerror="this.style.display=\'none\'">'
          + '<div class="ai-song-gradient ai-song-fallback"><i class="fas fa-music"></i></div>';
      }

      var actBtns = '';
      if (hasVideo) actBtns += '<button class="ai-song-btn dl-vid" onclick="event.stopPropagation();playAISong(\'' + s.id + '\')"><i class="fas fa-video"></i> Video</button>';
      actBtns += '<a class="ai-song-btn dl-aud" href="' + s.audioUrl + '" download><i class="fas fa-music"></i> Audio</a>';
      actBtns += '<a class="ai-song-btn share" href="' + s.shareUrl + '" target="_blank"><i class="fas fa-share-alt"></i></a>';

      var creator = s.creator ? 'Por: ' + s.creator : '';
      var tagsShort = (s.tags || '').split(',').slice(0,2).join(', ');

      card.innerHTML = '<div class="ai-song-thumb" onclick="playAISong(\'' + s.id + '\')" style="cursor:pointer">' + thumbContent + '<div class="ai-song-play"><i class="fas fa-play"></i></div></div>'
        + '<div class="ai-song-body"><div class="ai-song-title">' + (s.title || 'Sin titulo') + '</div>'
        + (creator ? '<div class="ai-song-creator">' + creator + '</div>' : '')
        + (tagsShort ? '<div class="ai-song-tags">' + tagsShort + '</div>' : '')
        + '<div class="ai-song-actions">' + actBtns + '</div></div>';
      grid.appendChild(card);
    });
  })
  .catch(function() {
    grid.innerHTML = '<div style="text-align:center;color:var(--gray);padding:2rem;grid-column:1/-1">Error al cargar canciones</div>';
  });
}

function playAISong(id) {
  fetch('api/save-song.php?id=' + id)
  .then(function(r) { return r.json(); })
  .then(function(s) {
    if (s.videoUrl) {
      var modal = document.getElementById('videoModal');
      if (modal) {
        var player = document.getElementById('videoPlayer');
        if (player) {
          player.src = s.videoUrl;
          player.play().catch(function(){});
          // Show branding at end of video
          player.onended = function() {
            var acts = document.getElementById('videoActions');
            if (acts) {
              var brandEl = acts.querySelector('.video-branding');
              if (!brandEl) {
                brandEl = document.createElement('div');
                brandEl.className = 'video-branding';
                brandEl.style.cssText = 'width:100%;text-align:center;margin-top:0.5rem;font-weight:800;color:var(--primary);font-size:1.1rem;';
                brandEl.textContent = 'yeshuacristiano.com';
                acts.appendChild(brandEl);
              }
            }
          };
        }
        var title = document.getElementById('videoTitle');
        if (title) title.textContent = s.title || '';
        var desc = document.getElementById('videoDesc');
        if (desc) desc.textContent = s.tags || '';
        var acts = document.getElementById('videoActions');
        if (acts) acts.innerHTML = '<a href="' + s.videoUrl + '" download style="color:var(--primary);text-decoration:none;font-size:0.85rem;"><i class="fas fa-download"></i> Descargar video</a>'
          + ' &nbsp; <a href="' + (s.shareUrl||'') + '" target="_blank" style="color:var(--primary);text-decoration:none;font-size:0.85rem;"><i class="fas fa-share-alt"></i> Compartir</a>';
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
      }
    } else if (s.audioUrl) {
      window.open(s.shareUrl || s.audioUrl, '_blank');
    }
  });
}

function closeVideoModal() {
  var modal = document.getElementById('videoModal');
  var player = document.getElementById('videoPlayer');
  if (player) { player.pause(); player.src = ''; }
  if (modal) modal.classList.remove('active');
  document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') { closeVideoModal(); closeGenreSongs(); closeSlideshowModal(); }
});

// ===== Genre Songs Player =====
var _catalogCache = null;
var _genreAudio = null;
var _currentGenre = '';
var _slideshow = { images: [], current: 0, interval: null, playing: false };

function loadCatalog(cb) {
  if (_catalogCache) return cb(_catalogCache);
  fetch('data/catalog-enhanced.json')
  .then(function(r) { return r.json(); })
  .then(function(data) { _catalogCache = data; cb(data); })
  .catch(function() {
    fetch('data/catalog.json')
    .then(function(r) { return r.json(); })
    .then(function(data) { _catalogCache = data; cb(data); })
    .catch(function() { cb(null); });
  });
}

function showGenreSongs(genre) {
  _currentGenre = genre;
  var panel = document.getElementById('genreSongsPanel');
  var titleEl = document.getElementById('genreSongsTitle');
  var list = document.getElementById('genreSongsList');
  if (!panel || !list) return;

  var genreNames = { country:'Country', rock:'Rock', gospel:'Gospel', folk:'Folk', worship:'Worship', ballad:'Balada' };
  titleEl.textContent = (genreNames[genre] || genre);
  list.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--gray)"><i class="fas fa-spinner fa-spin"></i></div>';
  panel.style.display = 'block';

  // Scroll to the panel so it's visible (especially on mobile)
  setTimeout(function() { panel.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 100);

  // Push history state for mobile back button
  if (window.innerWidth <= 768) {
    history.pushState({ genre: genre }, '', '#genre-' + genre);
  }

  document.querySelectorAll('.genre-card').forEach(function(c) { c.classList.remove('genre-active'); });
  var cards = document.querySelectorAll('.genre-card');
  var genres = ['country','rock','gospel','folk','worship','ballad'];
  var idx = genres.indexOf(genre);
  if (idx >= 0 && cards[idx]) cards[idx].classList.add('genre-active');

  loadCatalog(function(data) {
    if (!data || !data.videos) {
      list.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--gray)">Error loading catalog</div>';
      return;
    }
    var lang = localStorage.getItem('ft_lang') || 'es';
    var songs = data.videos.filter(function(v) { return v.genre === genre; });
    if (songs.length === 0) {
      list.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--gray)">No songs found</div>';
      return;
    }
    list.innerHTML = '';
    songs.forEach(function(s) {
      var title = (typeof s.title === 'object') ? (s.title[lang] || s.title.es || s.title.en) : s.title;
      var desc = (typeof s.description === 'object') ? (s.description[lang] || s.description.es || '') : (s.description || '');
      var thumbUrl = '';
      if (s.images && s.images.length > 0) {
        thumbUrl = s.images[0].url || s.images[0];
      } else if (s.thumbnail) {
        thumbUrl = s.thumbnail;
      }
      var verseRefs = [];
      if (s.verses) {
        s.verses.forEach(function(v) { verseRefs.push(typeof v === 'object' ? v.ref : v); });
      }

      var item = document.createElement('div');
      item.className = 'genre-song-item';
      item.setAttribute('data-id', s.id);
      item.innerHTML = '<div class="genre-song-thumb" style="background:' + (s.bg || 'var(--primary)') + '">'
        + (thumbUrl ? '<img src="' + thumbUrl + '" alt="' + title + '" onerror="this.style.display=\'none\'" loading="lazy">' : '')
        + '<div class="genre-song-play-overlay"><i class="fas fa-play"></i></div></div>'
        + '<div class="genre-song-info"><div class="genre-song-title">' + title + '</div>'
        + '<div class="genre-song-desc">' + desc + '</div>'
        + '<div class="genre-song-meta"><span class="genre-song-verses"><i class="fas fa-book-bible"></i> ' + verseRefs.slice(0,2).join(', ') + '</span>'
        + '</div></div>';
      item.onclick = function() { openSlideshowModal(s); };
      list.appendChild(item);
    });
  });
}

function openSlideshowModal(song) {
  closeSlideshowModal();
  var lang = localStorage.getItem('ft_lang') || 'es';
  var title = (typeof song.title === 'object') ? (song.title[lang] || song.title.es || song.title.en) : (song.title || '');
  var desc = (typeof song.description === 'object') ? (song.description[lang] || song.description.es || '') : (song.description || '');

  // Mark playing in list
  document.querySelectorAll('.genre-song-item').forEach(function(el) { el.classList.remove('playing'); });
  var item = document.querySelector('.genre-song-item[data-id="' + song.id + '"]');
  if (item) item.classList.add('playing');

  // Build images array
  _slideshow.images = [];
  if (song.images && song.images.length > 0) {
    song.images.forEach(function(img) {
      _slideshow.images.push({ url: img.url || img, alt: img.alt || '', credit: img.credit || '' });
    });
  }
  if (_slideshow.images.length === 0 && song.thumbnail) {
    _slideshow.images.push({ url: song.thumbnail, alt: title, credit: '' });
  }

  // Build verses HTML
  var versesHtml = '';
  if (song.verses && song.verses.length > 0) {
    song.verses.forEach(function(v) {
      if (typeof v === 'object') {
        versesHtml += '<div class="ss-verse"><i class="fas fa-book-bible"></i><div><strong>' + v.ref + '</strong><br>' + v.text + '</div></div>';
      }
    });
  }

  // Create modal
  var modal = document.createElement('div');
  modal.id = 'slideshowModal';
  modal.className = 'ss-modal';
  modal.innerHTML = '<div class="ss-backdrop" onclick="closeSlideshowModal()"></div>'
    + '<div class="ss-container">'
    + '<button class="ss-close" onclick="closeSlideshowModal()"><i class="fas fa-times"></i></button>'
    + '<div class="ss-player">'
    + '<div class="ss-slides" id="ssSlides"></div>'
    + '<div class="ss-overlay">'
    + '<div class="ss-title">' + title + '</div>'
    + '<div class="ss-verse-overlay" id="ssVerseOverlay"></div>'
    + '</div>'
    + '<div class="ss-credit" id="ssCredit"></div>'
    + '</div>'
    + '<div class="ss-info">'
    + '<div class="ss-desc">' + desc + '</div>'
    + '<div class="ss-genre-badge"><i class="fas ' + (song.icon || 'fa-music') + '"></i> ' + (song.genre || '') + '</div>'
    + (versesHtml ? '<div class="ss-verses">' + versesHtml + '</div>' : '')
    + '<div style="text-align:center;margin-top:1rem;padding-top:0.75rem;border-top:1px solid rgba(255,255,255,0.06);"><a href="creator.html" style="color:var(--primary);font-size:0.82rem;text-decoration:none;font-weight:600;"><i class="fas fa-wand-magic-sparkles"></i> ' + (L[localStorage.getItem('ft_lang')||'es']||L.es)['genre.createBtn'] + '</a></div>'
    + '</div>'
    + '</div>';
  document.body.appendChild(modal);
  document.body.style.overflow = 'hidden';

  // Build slides
  var slidesEl = document.getElementById('ssSlides');
  _slideshow.images.forEach(function(img, idx) {
    var slide = document.createElement('div');
    slide.className = 'ss-slide' + (idx === 0 ? ' active' : '');
    slide.style.backgroundImage = 'url(' + img.url + ')';
    slidesEl.appendChild(slide);
  });

  // Start slideshow
  _slideshow.current = 0;
  updateVerseOverlay(song, 0);
  updateCredit(0);
  _slideshow.interval = setInterval(function() {
    nextSlide(song);
  }, 7000);
  _slideshow.playing = true;

  // Start audio
  _genreAudio = new Audio(song.audioUrl);
  _genreAudio.volume = 0.7;
  _genreAudio.play().catch(function(e) { console.log('Audio play error:', e); });
  _genreAudio.onended = function() {
    // Show branding before moving to next song
    showSlideshowBranding(function() { autoPlayNext(song); });
  };

  setTimeout(function() { modal.classList.add('active'); }, 50);
}

function nextSlide(song) {
  var slides = document.querySelectorAll('#ssSlides .ss-slide');
  if (slides.length === 0) return;
  slides[_slideshow.current].classList.remove('active');
  _slideshow.current = (_slideshow.current + 1) % slides.length;
  slides[_slideshow.current].classList.add('active');
  updateVerseOverlay(song, _slideshow.current);
  updateCredit(_slideshow.current);
}

function updateVerseOverlay(song, idx) {
  var el = document.getElementById('ssVerseOverlay');
  if (!el || !song.verses || song.verses.length === 0) return;
  var v = song.verses[idx % song.verses.length];
  if (typeof v === 'object') {
    el.innerHTML = '<div class="ss-verse-text">"' + v.text + '"</div><div class="ss-verse-ref">— ' + v.ref + '</div>';
  }
}

function updateCredit(idx) {
  var el = document.getElementById('ssCredit');
  if (!el) return;
  var img = _slideshow.images[idx];
  el.textContent = img && img.credit ? '📷 ' + img.credit : '';
}

function closeSlideshowModal() {
  var modal = document.getElementById('slideshowModal');
  if (modal) {
    modal.classList.remove('active');
    setTimeout(function() { modal.remove(); }, 300);
  }
  if (_slideshow.interval) { clearInterval(_slideshow.interval); _slideshow.interval = null; }
  _slideshow.playing = false;
  if (_genreAudio) { _genreAudio.pause(); _genreAudio = null; }
  document.body.style.overflow = '';
  document.querySelectorAll('.genre-song-item').forEach(function(el) { el.classList.remove('playing'); });
}

function showSlideshowBranding(callback) {
  var player = document.querySelector('.ss-player');
  if (!player) { if (callback) callback(); return; }
  var existing = player.querySelector('.ss-branding-end');
  if (existing) existing.remove();
  var overlay = document.createElement('div');
  overlay.className = 'ss-branding-end';
  overlay.innerHTML = '<div class="branding-url">yeshuacristiano.com</div><div class="branding-sub">FaithTunes \u2022 M\u00fasica cristiana con IA</div>';
  player.appendChild(overlay);
  setTimeout(function() {
    if (overlay.parentNode) overlay.remove();
    if (callback) callback();
  }, 3500);
}

function autoPlayNext(currentSong) {
  if (!_catalogCache || !_currentGenre) return;
  var songs = _catalogCache.videos.filter(function(v) { return v.genre === _currentGenre; });
  var idx = -1;
  for (var i = 0; i < songs.length; i++) {
    if (songs[i].id === currentSong.id) { idx = i; break; }
  }
  if (idx >= 0 && idx < songs.length - 1) {
    openSlideshowModal(songs[idx + 1]);
  } else {
    closeSlideshowModal();
  }
}

function closeGenreSongs() {
  var panel = document.getElementById('genreSongsPanel');
  if (panel) panel.style.display = 'none';
  closeSlideshowModal();
  document.querySelectorAll('.genre-card').forEach(function(c) { c.classList.remove('genre-active'); });
  // Scroll back to genres section on mobile
  var genresSection = document.getElementById('genres');
  if (genresSection && window.innerWidth <= 768) {
    genresSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
}

// Mobile back button support
window.addEventListener('popstate', function(e) {
  var panel = document.getElementById('genreSongsPanel');
  if (panel && panel.style.display === 'block') {
    closeGenreSongs();
  }
  var ssModal = document.getElementById('slideshowModal');
  if (ssModal) {
    closeSlideshowModal();
  }
});
