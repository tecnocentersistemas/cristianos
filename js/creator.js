// =============================================
// FaithTunes Creator - Chat + AI Video Generator
// =============================================

// ===== Theme =====
function toggleTheme() {
  var html = document.documentElement;
  var cur = html.getAttribute('data-theme') || 'dark';
  var next = cur === 'dark' ? 'light' : 'dark';
  html.setAttribute('data-theme', next);
  localStorage.setItem('ft_theme', next);
  var icon = document.querySelector('#themeBtn i');
  if (icon) icon.className = next === 'light' ? 'fas fa-sun' : 'fas fa-moon';
}
(function() {
  var saved = localStorage.getItem('ft_theme') || 'light';
  document.documentElement.setAttribute('data-theme', saved);
  var icon = document.querySelector('#themeBtn i');
  if (icon) icon.className = saved === 'light' ? 'fas fa-sun' : 'fas fa-moon';
})();

// ===== i18n (translations loaded from i18n.js) =====
var currentLang = localStorage.getItem('ft_lang') || 'es';
function t(key) { return (CL[currentLang] || CL.es)[key] || CL.es[key] || key; }
function applyCreatorLang(lang) {
  currentLang = lang; document.documentElement.lang = lang;
  document.documentElement.dir = RTL_LANGS.indexOf(lang) >= 0 ? 'rtl' : 'ltr';
  document.querySelectorAll('[data-i18n]').forEach(function(el) { var k = el.getAttribute('data-i18n'); var text = t(k); if (text) el.innerHTML = text; });
  document.querySelectorAll('[data-i18n-placeholder]').forEach(function(el) { var k = el.getAttribute('data-i18n-placeholder'); var text = t(k); if (text) el.placeholder = text; });
  var currentEl = document.querySelector('.lang-globe-current');
  if (currentEl) currentEl.textContent = lang.toUpperCase();
  document.querySelectorAll('.lang-dropdown-item').forEach(function(item) {
    item.classList.toggle('active', item.dataset.lang === lang);
  });
}
window.setLang = function(l) { if (!CL[l]) return; localStorage.setItem('ft_lang', l); applyCreatorLang(l); };

function buildLangDropdown() {
  var dd = document.getElementById('langDropdown');
  if (!dd) return;
  var html = '';
  LANGS.forEach(function(lang) {
    html += '<div class="lang-dropdown-item' + (lang.code === currentLang ? ' active' : '') + '" data-lang="' + lang.code + '" onclick="selectLang(\'' + lang.code + '\')">'
      + '<span class="lang-flag">' + lang.flag + '</span>'
      + '<span class="lang-name">' + lang.name + '</span>'
      + '</div>';
  });
  dd.innerHTML = html;
}
function toggleLangDropdown() { var dd = document.getElementById('langDropdown'); if (!dd) return; dd.classList.toggle('active'); var ov = document.getElementById('langOverlay'); if (ov) ov.classList.toggle('active', dd.classList.contains('active')); }
function selectLang(code) { setLang(code); var dd = document.getElementById('langDropdown'); if (dd) dd.classList.remove('active'); var ov = document.getElementById('langOverlay'); if (ov) ov.classList.remove('active'); }
document.addEventListener('click', function(e) { if (!e.target.closest('.lang-globe-wrapper')) { var dd = document.getElementById('langDropdown'); if (dd) dd.classList.remove('active'); var ov = document.getElementById('langOverlay'); if (ov) ov.classList.remove('active'); } });

// ===== Faith Inline Declaration =====
function isFaithAccepted() {
  return localStorage.getItem('ft_faith_accepted') === 'yes';
}

function initFaithInline() {
  var chk = document.getElementById('faithInlineCheck');
  var container = document.getElementById('faithInline');
  var status = document.getElementById('faithInlineStatus');
  if (!chk || !container) return;
  // Auto-check if already accepted
  if (isFaithAccepted()) {
    chk.checked = true;
    container.classList.add('accepted');
    if (status) { status.innerHTML = '<i class="fas fa-check-circle"></i>'; status.title = t('faith.acceptedTip'); }
  }
  chk.addEventListener('change', function() {
    var msg = document.getElementById('faithInlineMsg');
    if (chk.checked) {
      localStorage.setItem('ft_faith_accepted', 'yes');
      container.classList.add('accepted');
      container.classList.remove('shake');
      if (status) { status.innerHTML = '<i class="fas fa-check-circle"></i>'; status.title = t('faith.acceptedTip'); }
      if (msg) msg.style.display = 'none';
    } else {
      localStorage.removeItem('ft_faith_accepted');
      container.classList.remove('accepted');
      if (status) { status.innerHTML = ''; status.title = ''; }
    }
  });
}

function showFaithRequired() {
  var container = document.getElementById('faithInline');
  var msg = document.getElementById('faithInlineMsg');
  if (container) {
    container.classList.remove('shake');
    void container.offsetWidth; // force reflow for re-animation
    container.classList.add('shake');
    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
  if (msg) {
    msg.innerHTML = '<i class="fas fa-info-circle"></i> ' + t('faith.required');
    msg.style.display = 'flex';
    setTimeout(function() { msg.style.display = 'none'; }, 6000);
  }
}

// Initialize faith inline on load
initFaithInline();

// ===== Chat =====
function addMessage(text, type) {
  var container = document.getElementById('chatMessages');
  var div = document.createElement('div');
  div.className = 'chat-msg ' + type;
  var iconClass = type === 'ai' ? 'fa-cross' : 'fa-user';
  div.innerHTML = '<div class="chat-msg-avatar"><i class="fas ' + iconClass + '"></i></div><div class="chat-msg-bubble"><p>' + text + '</p></div>';
  container.appendChild(div);
  container.scrollTop = container.scrollHeight;
  return div;
}
function addTyping() {
  var container = document.getElementById('chatMessages');
  var div = document.createElement('div');
  div.className = 'chat-msg ai'; div.id = 'typingIndicator';
  div.innerHTML = '<div class="chat-msg-avatar"><i class="fas fa-cross"></i></div><div class="chat-msg-bubble"><div class="typing-dots"><span></span><span></span><span></span></div></div>';
  container.appendChild(div); container.scrollTop = container.scrollHeight;
}
function removeTyping() { var el = document.getElementById('typingIndicator'); if (el) el.remove(); }
function useSuggestion(btn) {
  if (!isFaithAccepted()) { document.getElementById('chatInput').value = btn.textContent; showFaithRequired(); return; }
  document.getElementById('chatInput').value = btn.textContent; sendMessage();
}

// Quick-access shortcuts for content types
function useShortcut(type) {
  var prompts = {
    animals: {
      es: 'Creame una canción cristiana con animales de la creación: águilas, delfines, corderos en la naturaleza',
      en: 'Create a Christian song with animals of creation: eagles, dolphins, lambs in nature',
      pt: 'Crie uma música cristã com animais da criação: águias, golfinhos, cordeiros na natureza'
    },
    landscapes: {
      es: 'Un video musical con montañas majestuosas, ríos cristalinos y valles verdes',
      en: 'A music video with majestic mountains, crystal rivers and green valleys',
      pt: 'Um vídeo musical com montanhas majestosas, rios cristalinos e vales verdes'
    },
    plants: {
      es: 'Canción cristiana con jardines de flores, árboles frondosos y campos de trigo',
      en: 'Christian song with flower gardens, lush trees and wheat fields',
      pt: 'Música cristã com jardins de flores, árvores frondosas e campos de trigo'
    },
    sunsets: {
      es: 'Video de worship con atardeceres espectaculares sobre el océano y la naturaleza',
      en: 'Worship video with spectacular sunsets over the ocean and nature',
      pt: 'Vídeo de worship com pôr do sol espetaculares sobre o oceano e a natureza'
    }
  };
  var lang = currentLang;
  var prompt = prompts[type] ? (prompts[type][lang] || prompts[type].es) : '';
  if (prompt) {
    document.getElementById('chatInput').value = prompt;
    if (!isFaithAccepted()) { showFaithRequired(); return; }
    sendMessage();
  }
}

// Unlock audio on user interaction (required by browsers)
var _audioUnlocked = false;
function unlockAudio() {
  if (_audioUnlocked) return;
  _audioUnlocked = true;
  var audioEl = document.getElementById('bgAudio');
  audioEl.muted = true;
  audioEl.play().then(function() {
    audioEl.pause();
    audioEl.muted = false;
    audioEl.currentTime = 0;
  }).catch(function() {
    audioEl.muted = false;
    _audioUnlocked = false; // Retry on next interaction
  });
}
// Re-unlock on EVERY interaction (not once)
document.addEventListener('click', unlockAudio);
document.addEventListener('touchstart', unlockAudio);

// Re-try audio when user returns to tab
document.addEventListener('visibilitychange', function() {
  if (document.visibilityState === 'visible' && slideshow.playing) {
    var a = document.getElementById('bgAudio');
    if (a.src && a.paused) {
      _audioUnlocked = false;
      unlockAudio();
      setTimeout(function() { a.play().catch(function(){}); }, 200);
    }
  }
});

function sendMessage() {
  var input = document.getElementById('chatInput');
  var text = input.value.trim();
  if (!text) return;
  if (!isFaithAccepted()) { showFaithRequired(); return; }
  unlockAudio();
  input.value = '';
  addMessage(text, 'user');
  input.disabled = true;
  document.getElementById('chatSendBtn').disabled = true;
  addTyping();
  var sunoEnabled = document.getElementById('sunoToggle') && document.getElementById('sunoToggle').checked;
  var genMsg = addMessage('<i class="fas fa-spinner fa-spin"></i> ' + t(sunoEnabled ? 'cr.sunoGenerating' : 'cr.generating'), 'ai');

  fetch('api/ai.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ prompt: text, lang: currentLang, sunoMode: sunoEnabled })
  })
  .then(function(res) {
    return res.text().then(function(body) {
      if (!body || !body.trim()) return { error: t('cr.errEmpty') };
      try { return JSON.parse(body); }
      catch(e) { return { error: t('cr.errParse') }; }
    });
  })
  .then(function(data) {
    removeTyping();
    if (genMsg) genMsg.remove();
    input.disabled = false; document.getElementById('chatSendBtn').disabled = false; input.focus();
    if (!data || data.error) {
      var errDetail = data && data.error ? data.error : '';
      var errIcon = '<i class="fas fa-exclamation-triangle" style="color:#f59e0b"></i> ';
      addMessage(errIcon + t('cr.error') + (errDetail ? '<br><small style="opacity:0.7">' + errDetail + '</small>' : ''), 'ai');
      return;
    }
    if (data.success && data.video) {
      try {
        var isMobile = window.innerWidth <= 768;
        if (sunoEnabled) {
          window._pendingVideoData = data.video;
          var waitInfo = '<div style="text-align:center;padding:1rem 0">';
          waitInfo += '<div style="font-size:1.1rem;font-weight:700;color:var(--primary);margin-bottom:0.5rem">' + (data.video.title || '') + '</div>';
          if (data.video.verses && data.video.verses[0]) {
            waitInfo += '<div style="font-style:italic;color:var(--gray);font-size:0.85rem;margin-bottom:0.5rem">"' + data.video.verses[0].text + '"</div>';
            waitInfo += '<div style="color:var(--primary);font-size:0.75rem">' + data.video.verses[0].ref + '</div>';
          }
          waitInfo += '</div>';
          addMessage(waitInfo, 'ai');
          startSunoGeneration(null, data.video);
        } else {
          addMessage('<i class="fas fa-check-circle" style="color:#22c55e"></i> ' + t(isMobile ? 'cr.readyMobile' : 'cr.ready'), 'ai');
          startVideoExperience(data.video);
        }
      } catch(e) {
        console.error('Video render error:', e);
        addMessage('<i class="fas fa-exclamation-triangle" style="color:#f59e0b"></i> ' + t('cr.errRender'), 'ai');
      }
    }
  })
  .catch(function(err) {
    removeTyping();
    if (genMsg) genMsg.remove();
    input.disabled = false; document.getElementById('chatSendBtn').disabled = false;
    var errMsg = t('cr.errConnection');
    if (err && err.message && err.message.indexOf('timeout') >= 0) errMsg = t('cr.errTimeout');
    addMessage('<i class="fas fa-wifi" style="color:#f59e0b"></i> ' + errMsg, 'ai');
    console.error('Fetch error:', err);
  });
}
document.getElementById('chatInput').addEventListener('keydown', function(e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); } });

// ===== Slideshow Video Player (DYNAMIC slides) =====
var slideshow = { images: [], texts: [], audio: null, currentSlide: 0, interval: null, playing: false, slideDuration: 8000 };

function startVideoExperience(video) {
  document.getElementById('playerEmpty').style.display = 'none';
  document.getElementById('playerActive').style.display = 'block';
  document.getElementById('playerTitle').textContent = video.title || 'FaithTunes Video';
  document.getElementById('playerDesc').textContent = video.description || '';
  document.getElementById('playerTheme').textContent = video.theme || '';
  document.getElementById('playerGenre').textContent = video.genre || '';
  document.getElementById('playerMood').textContent = video.mood || '';

  // Poem
  var poemEl = document.getElementById('playerPoem');
  if (video.poem && video.poem.length) {
    poemEl.innerHTML = video.poem.map(function(line) { return '<div>' + line + '</div>'; }).join('');
    poemEl.style.display = 'block';
  } else { poemEl.style.display = 'none'; }

  // Verses
  var versesEl = document.getElementById('playerVerses');
  if (video.verses && video.verses.length) {
    versesEl.innerHTML = video.verses.map(function(v) {
      return '<div class="player-verse-item"><i class="fas fa-bible"></i><div><span class="player-verse-ref">' + v.ref + '</span> — ' + v.text + '</div></div>';
    }).join('');
  }

  // Build image data with credits
  slideshow.images = (video.images || []).map(function(img) {
    return { url: img.url || img, alt: img.alt || '', credit: img.credit || '' };
  });

  // Ensure minimum 5 slides
  while (slideshow.images.length < 5 && slideshow.images.length > 0) {
    slideshow.images.push(slideshow.images[slideshow.images.length - 1]);
  }

  // Build text slides: ONLY Bible verses (no poem/lyrics - they go out of sync)
  slideshow.texts = [];
  if (video.verses) { video.verses.forEach(function(v) { slideshow.texts.push({ text: '"' + v.text + '"', ref: '— ' + v.ref }); }); }

  // CREATE SLIDES DYNAMICALLY
  var slidesContainer = document.getElementById('slideshowSlides');
  slidesContainer.innerHTML = '';
  slideshow.images.forEach(function(img, idx) {
    var div = document.createElement('div');
    div.className = 'slideshow-slide' + (idx === 0 ? ' active' : '');
    div.style.backgroundImage = 'url(' + img.url + ')';
    slidesContainer.appendChild(div);
  });

  // Audio - preload and play when ready
  var audioEl = document.getElementById('bgAudio');
  audioEl.pause(); audioEl.currentTime = 0;
  audioEl.muted = false;
  // Remove old timeupdate listeners
  audioEl.onended = null;
  audioEl.onerror = null;
  audioEl.oncanplay = null;

  if (video.audio) {
    audioEl.src = video.audio;
    audioEl.volume = 0.7;
    audioEl.load();
    // Fallback audio if the main one fails to load (only for non-Suno)
    if (!video._isSuno) {
      var _audioFallbacks = [
        'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Eternal%20Hope.mp3',
        'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Americana.mp3',
        'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Gymnopedie%20No%201.mp3'
      ];
      var _fallbackTried = false;
      audioEl.onerror = function() {
        if (_fallbackTried) return;
        _fallbackTried = true;
        var fb = _audioFallbacks[Math.floor(Math.random() * _audioFallbacks.length)];
        audioEl.src = fb; audioEl.load();
        audioEl.oncanplay = function() { audioEl.play().catch(function(){}); audioEl.oncanplay = null; };
      };
    }
    // Play as soon as enough data is buffered
    audioEl.oncanplay = function() {
      audioEl.play().then(function() {
        hideTapToPlay();
      }).catch(function(err) {
        console.warn('Autoplay blocked:', err);
        showTapToPlay();
      });
      audioEl.oncanplay = null;
    };
    // Fallback if oncanplay already fired
    if (audioEl.readyState >= 3) {
      audioEl.play().then(function() { hideTapToPlay(); }).catch(function() { showTapToPlay(); });
    }
  }
  var npEl = document.getElementById('nowPlaying');
  var npText = document.getElementById('nowPlayingText');
  if (video.audioName) { npText.textContent = video.audioName; npEl.style.display = 'flex'; }

  slideshow.currentSlide = 0;
  startPlayback();
  if (window.innerWidth <= 768) { document.getElementById('creatorPlayer').scrollIntoView({ behavior: 'smooth' }); }

  // Show branding when audio ends
  var audioForBranding = document.getElementById('bgAudio');
  audioForBranding.onended = function() {
    showCreatorBranding();
  };
}

function startPlayback() {
  slideshow.playing = true;
  document.getElementById('playPauseBtn').innerHTML = '<i class="fas fa-pause"></i>';
  // Remove branding overlay if present
  var brandingEl = document.querySelector('.slideshow-branding-end');
  if (brandingEl) brandingEl.remove();
  // Audio already auto-plays via oncanplay in startVideoExperience
  var audioEl = document.getElementById('bgAudio');
  if (audioEl.src && audioEl.paused) { audioEl.play().catch(function(){}); }
  showSlide(0);
  slideshow.interval = setInterval(function() {
    slideshow.currentSlide++;
    if (slideshow.currentSlide >= slideshow.images.length) slideshow.currentSlide = 0;
    showSlide(slideshow.currentSlide);
  }, slideshow.slideDuration);
  updateProgress();
}

function showSlide(index) {
  var slides = document.querySelectorAll('#slideshowSlides .slideshow-slide');
  slides.forEach(function(el, i) { el.classList.toggle('active', i === index); });

  // Show photographer credit
  var creditEl = document.getElementById('slideshowCredit');
  if (creditEl) {
    var img = slideshow.images[index];
    if (img && img.credit) { creditEl.textContent = '📷 ' + img.credit; creditEl.classList.add('visible'); }
    else { creditEl.classList.remove('visible'); }
  }

  // Animate text - sync to audio time if available, else use slide index
  var textEl = document.getElementById('slideshowText');
  var refEl = document.getElementById('slideshowRef');
  textEl.classList.remove('visible'); refEl.classList.remove('visible');
  setTimeout(function() {
    var textIdx = index;
    var audioEl = document.getElementById('bgAudio');
    if (audioEl && audioEl.duration > 0 && slideshow.texts.length > 0 && !isNaN(audioEl.currentTime)) {
      var pct = audioEl.currentTime / audioEl.duration;
      textIdx = Math.min(Math.floor(pct * slideshow.texts.length), slideshow.texts.length - 1);
    }
    var td = slideshow.texts[textIdx % slideshow.texts.length];
    if (td) { textEl.textContent = td.text; refEl.textContent = td.ref; textEl.classList.add('visible'); refEl.classList.add('visible'); }
  }, 500);
}

function updateProgress() {
  var bar = document.getElementById('progressBar');
  var total = slideshow.images.length * slideshow.slideDuration;
  var start = Date.now();
  function tick() {
    if (!slideshow.playing) return;
    var pct = Math.min(((Date.now() - start) / total) * 100, 100);
    bar.style.width = pct + '%';
    if (pct >= 100) start = Date.now();
    requestAnimationFrame(tick);
  }
  requestAnimationFrame(tick);
}

function togglePlayback() {
  var audioEl = document.getElementById('bgAudio');
  if (slideshow.playing) {
    slideshow.playing = false; clearInterval(slideshow.interval); audioEl.pause();
    document.getElementById('playPauseBtn').innerHTML = '<i class="fas fa-play"></i>';
  } else { startPlayback(); }
}

function restartPlayback() {
  clearInterval(slideshow.interval);
  document.getElementById('bgAudio').currentTime = 0;
  slideshow.currentSlide = 0;
  startPlayback();
}

// Tap-to-play overlay for autoplay-blocked scenarios
function showTapToPlay() {
  var container = document.getElementById('slideshowContainer');
  if (!container || container.querySelector('.tap-to-play')) return;
  var overlay = document.createElement('div');
  overlay.className = 'tap-to-play';
  overlay.innerHTML = '<div class="tap-icon"><i class="fas fa-play"></i></div><div class="tap-text">Toca para escuchar</div>';
  overlay.onclick = function() {
    var a = document.getElementById('bgAudio');
    a.muted = false; a.volume = 0.7;
    a.play().then(function() {
      hideTapToPlay();
      var mb = document.getElementById('muteBtn');
      if (mb) mb.innerHTML = '<i class="fas fa-volume-up"></i>';
    }).catch(function(){});
  };
  container.appendChild(overlay);
  var mb = document.getElementById('muteBtn');
  if (mb) mb.innerHTML = '<i class="fas fa-volume-mute"></i>';
}
function hideTapToPlay() {
  var el = document.querySelector('.tap-to-play');
  if (el) el.remove();
  var mb = document.getElementById('muteBtn');
  if (mb) mb.innerHTML = '<i class="fas fa-volume-up"></i>';
}

function showCreatorBranding() {
  var container = document.getElementById('slideshowContainer');
  if (!container) return;
  var existing = container.querySelector('.slideshow-branding-end');
  if (existing) return;
  var overlay = document.createElement('div');
  overlay.className = 'slideshow-branding-end';
  overlay.innerHTML = '<div class="branding-url">yeshuacristiano.com</div><div class="branding-sub">FaithTunes \u2022 M\u00fasica cristiana con IA</div>';
  container.appendChild(overlay);
}

function toggleMute() {
  var a = document.getElementById('bgAudio');
  var btn = document.getElementById('muteBtn');
  if (!btn) return;
  if (a.muted || a.volume === 0 || a.paused) {
    a.muted = false; a.volume = 0.7;
    a.play().then(function() {
      hideTapToPlay();
      btn.innerHTML = '<i class="fas fa-volume-up"></i>';
    }).catch(function() {
      btn.innerHTML = '<i class="fas fa-volume-mute"></i>';
    });
  } else {
    a.muted = true;
    btn.innerHTML = '<i class="fas fa-volume-mute"></i>';
  }
}

// ===== Suno AI - Real sung song generation =====
var _sunoTaskId = null;
var _sunoPolling = null;
var _sunoStatusMsg = null;

function startSunoGeneration(userPrompt, videoData) {
  // Build an intelligent prompt using OpenAI's structured output
  var sunoPayload = {};
  var lang = (videoData && videoData.lang) || currentLang;
  var langNames = { es:'Spanish',en:'English',pt:'Portuguese',de:'German',fr:'French',it:'Italian',pl:'Polish',ru:'Russian',uk:'Ukrainian',sv:'Swedish',fi:'Finnish',nb:'Norwegian',lv:'Latvian',sl:'Slovenian',ja:'Japanese',ko:'Korean',zh:'Chinese',ar:'Arabic',fa:'Persian',af:'Afrikaans',sw:'Swahili',zu:'Zulu',el:'Greek',km:'Khmer',hi:'Hindi' };
  var langName = langNames[lang] || 'Spanish';

  if (videoData && videoData.poem && videoData.poem.length > 0) {
    // Custom mode: send the poem as lyrics + genre as style
    var lyrics = videoData.poem.join('\n');
    var style = (videoData.genre || 'worship') + ', christian, ' + (videoData.mood || 'uplifting') + ', sung in ' + langName;
    sunoPayload = {
      prompt: lyrics,
      style: style,
      title: videoData.title || 'FaithTunes Song'
    };
  } else {
    // Fallback: simple mode with description
    sunoPayload = {
      prompt: 'Christian ' + (videoData ? videoData.genre || 'worship' : '') + ' song in ' + langName + ': ' + (videoData ? videoData.title || userPrompt : userPrompt)
    };
  }

  fetch('api/suno-generate.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(sunoPayload)
  })
  .then(function(res) { return res.json(); })
  .then(function(data) {
    if (data.success && data.taskId) {
      _sunoTaskId = data.taskId;
      _sunoStatusMsg = addMessage('<i class="fas fa-spinner fa-spin" style="color:var(--primary)"></i> ' + t('cr.sunoGenerating'), 'ai');
      // Poll aggressively: first at 3s, then every 5s
      var elapsed = 0;
      setTimeout(function() { elapsed += 3; pollSunoStatus(_sunoTaskId, elapsed); }, 3000);
      _sunoPolling = setInterval(function() {
        elapsed += 5;
        pollSunoStatus(_sunoTaskId, elapsed);
      }, 5000);
    } else {
      console.warn('Suno generation failed:', data.error || data);
    }
  })
  .catch(function(err) {
    console.warn('Suno API error:', err);
  });
}

function pollSunoStatus(taskId, elapsed) {
  fetch('api/suno-generate.php?taskId=' + taskId)
  .then(function(res) { return res.json(); })
  .then(function(data) {
    if (data.status === 'complete' && data.songs && data.songs.length > 0) {
      clearInterval(_sunoPolling); _sunoPolling = null;
      if (_sunoStatusMsg) _sunoStatusMsg.remove();
      var song = data.songs[0];
      // Use streamAudioUrl FIRST (available in 30-40s) — audioUrl takes 2-3 min
      var playUrl = song.streamAudioUrl || song.audioUrl;
      var saveUrl = song.audioUrl || song.streamAudioUrl;
      if (!playUrl) return;
      window._sunoSong = song;
      window._sunoAudioUrl = playUrl;

      var isMobile = window.innerWidth <= 768;
      addMessage('<i class="fas fa-check-circle" style="color:#22c55e"></i> ' + t(isMobile ? 'cr.sunoReadyMobile' : 'cr.sunoReady') + (song.title ? ' &mdash; <strong>' + song.title + '</strong>' : ''), 'ai');

      // NOW start the full slideshow experience with the REAL Suno audio
      var videoData = window._pendingVideoData;
      if (videoData) {
        videoData.audio = playUrl;
        videoData.audioName = song.title || 'FaithTunes';
        videoData._isSuno = true;
        startVideoExperience(videoData);
      }

      var audioEl = document.getElementById('bgAudio');

      // Show FULL lyrics - use the LONGEST between Suno's response and our original poem
      var sunoLyrics = song.prompt || '';
      var originalPoem = (window._pendingVideoData && window._pendingVideoData.poem) ? window._pendingVideoData.poem.join('\n') : '';
      var lyricsText = (sunoLyrics.length >= originalPoem.length) ? sunoLyrics : originalPoem;
      if (lyricsText) {
        var lP = document.getElementById('playerLyrics'), lC = document.getElementById('lyricsContent');
        if (lP && lC) {
          renderLyricsHtml(lyricsText);
          lP.style.display = 'block';
          initLyricsLang(lyricsText);
        }
        // Update poem panel with full lyrics too
        var pe = document.getElementById('playerPoem');
        if (pe) {
          pe.innerHTML = lyricsText.replace(/\[([^\]]+)\]/g, '<strong style="color:var(--primary)">$1</strong>').replace(/\n/g, '<br>');
          pe.style.display = 'block';
        }
        // Sync lyrics highlight with audio
        var les = document.querySelectorAll('#lyricsContent .lyrics-line');
        if (les.length > 0 && song.duration > 0) {
          var spl = song.duration / les.length;
          audioEl.addEventListener('timeupdate', function syncLyrics() {
            var ci = Math.floor(audioEl.currentTime / spl);
            les.forEach(function(e, idx) { e.classList.toggle('active', idx === ci); });
            if (les[ci]) les[ci].scrollIntoView({ behavior: 'smooth', block: 'center' });
          });
        }
      }

      if (song.title) { var te = document.getElementById('playerTitle'); if (te) te.textContent = song.title; }
      var ae = document.getElementById('playerActions'); if (ae) ae.style.display = 'flex';

      // Fetch REAL timestamped lyrics from Suno (for perfect sync)
      if (taskId && song.id) {
        fetch('api/suno-lyrics.php', {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ taskId: taskId, audioId: song.id })
        }).then(function(r) { return r.json(); }).then(function(lr) {
          if (lr.success && lr.lyrics && lr.lyrics.length > lyricsText.length) {
            // Update lyrics display with real Suno lyrics
            lyricsText = lr.lyrics;
            renderLyricsHtml(lyricsText);
            initLyricsLang(lyricsText);
            var pe = document.getElementById('playerPoem');
            if (pe) { pe.innerHTML = lyricsText.replace(/\[([^\]]+)\]/g, '<strong style="color:var(--primary)">$1</strong>').replace(/\n/g, '<br>'); }
          }
          // Apply timestamped sync if we have line data
          if (lr.success && lr.lines && lr.lines.length > 0) {
            window._sunoTimedLines = lr.lines;
            var les2 = document.querySelectorAll('#lyricsContent .lyrics-line');
            if (les2.length > 0) {
              // Remove old timeupdate listener and add precise one
              var audioEl2 = document.getElementById('bgAudio');
              audioEl2.addEventListener('timeupdate', function timedSync() {
                var ct = audioEl2.currentTime;
                var lines = window._sunoTimedLines;
                les2.forEach(function(e, idx) {
                  if (idx < lines.length) {
                    var active = ct >= lines[idx].startS && ct < lines[idx].endS + 0.5;
                    e.classList.toggle('active', active);
                    if (active) e.scrollIntoView({ behavior: 'smooth', block: 'center' });
                  }
                });
              });
            }
          }
        }).catch(function(e) { console.warn('Lyrics fetch error:', e); });
      }

      // Save to VPS in BACKGROUND (don't block playback!)
      var slideImgs = [];
      if (slideshow.images) slideshow.images.forEach(function(img) { if (img.url) slideImgs.push(img.url); });

      // Small delay to let timestamped lyrics load first
      setTimeout(function() {
        var finalLyrics = lyricsText;
        fetch('api/save-song.php', {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            audioUrl: saveUrl, title: song.title || '', lyrics: finalLyrics,
            tags: song.tags || '', duration: song.duration || 0,
            genre: (window._pendingVideoData && window._pendingVideoData.genre) || '',
            imageUrl: song.imageUrl || '', taskId: taskId,
            slideImages: slideImgs, creator: '', songId: song.id || ''
          })
        }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.success && d.song) {
          window._savedSong = d.song;
          if (d.song.audioUrl) window._sunoAudioUrl = d.song.audioUrl;
          // Check for video periodically
          var vc = 0;
          var vi = setInterval(function() {
            vc++;
            if (vc > 18) { clearInterval(vi); return; }
            fetch('api/save-song.php?id=' + d.song.id).then(function(r) { return r.json(); }).then(function(s) {
              if (s.videoUrl) {
                clearInterval(vi);
                window._savedSong.videoUrl = s.videoUrl;
                var vb = document.getElementById('downloadVideoBtn');
                if (vb) vb.style.display = 'flex';
              }
            }).catch(function() {});
          }, 10000);
        }
      }).catch(function(e) { console.warn('Save song error:', e, 'saveUrl:', saveUrl); });
      }, 3000); // delay to allow timestamped lyrics to load

    } else if (data.status === 'error') {
      clearInterval(_sunoPolling); _sunoPolling = null;
      if (_sunoStatusMsg) _sunoStatusMsg.remove();
      addMessage('<i class="fas fa-exclamation-circle"></i> Suno: ' + (data.error || 'Error'), 'ai');
    } else {
      if (_sunoStatusMsg) {
        _sunoStatusMsg.querySelector('p').innerHTML = '<i class="fas fa-spinner fa-spin" style="color:var(--primary)"></i> ' + t('cr.sunoWaiting').replace('{seconds}', elapsed);
      }
      if (elapsed >= 360) {
        clearInterval(_sunoPolling); _sunoPolling = null;
        if (_sunoStatusMsg) _sunoStatusMsg.remove();
        addMessage('<i class="fas fa-exclamation-circle"></i> Suno: Timeout.', 'ai');
      }
    }
  })
  .catch(function(err) { console.warn('Suno poll error:', err); });
}

// ===== Download & Share =====
function downloadFile(type) {
  var saved = window._savedSong, song = window._sunoSong;
  var url = '', ext = '.mp3';
  if (type === 'video' && saved && saved.videoUrl) { url = saved.videoUrl; ext = '.mp4'; }
  else { url = (saved && saved.audioUrl) || window._sunoAudioUrl; ext = '.mp3'; }
  if (!url) return;
  var btnId = type === 'video' ? 'downloadVideoBtn' : 'downloadBtn';
  var btn = document.getElementById(btnId);
  if (btn) { btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ...'; btn.disabled = true; }
  fetch(url).then(function(r){return r.blob();}).then(function(b){
    var a = document.createElement('a');
    var ti = (song && song.title) ? song.title.replace(/[^\w\s\u00C0-\u024F]/g,'').trim() : 'FaithTunes';
    a.href = URL.createObjectURL(b); a.download = ti + ' - FaithTunes' + ext;
    document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(a.href);
    if(btn){btn.innerHTML='<i class="fas fa-'+(type==='video'?'video':'music')+'"></i> '+(type==='video'?'Video MP4':'Audio MP3');btn.disabled=false;}
  }).catch(function(){window.open(url,'_blank');if(btn)btn.disabled=false;});
}

function toggleShareMenu() {
  var m = document.getElementById('shareMenu'); m.style.display = m.style.display === 'none' ? 'flex' : 'none';
}

function shareOn(platform) {
  var song = window._sunoSong, saved = window._savedSong;
  var title = (song && song.title) ? song.title : 'FaithTunes';
  // Priority: shareUrl > videoUrl > audioUrl > site home (NEVER creator.html)
  var shareUrl = (saved && saved.shareUrl) ? saved.shareUrl : '';
  if (!shareUrl && saved && saved.videoUrl) shareUrl = saved.videoUrl;
  if (!shareUrl && saved && saved.audioUrl) shareUrl = saved.audioUrl;
  if (!shareUrl && window._sunoAudioUrl) shareUrl = window._sunoAudioUrl;
  if (!shareUrl) shareUrl = 'https://yeshuacristiano.com';
  var text = title + ' - Cancion cristiana creada con IA en FaithTunes';
  switch(platform) {
    case 'whatsapp': window.open('https://wa.me/?text=' + encodeURIComponent(text + '\n\n' + shareUrl), '_blank'); break;
    case 'facebook': window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(shareUrl), '_blank'); break;
    case 'twitter': window.open('https://twitter.com/intent/tweet?text=' + encodeURIComponent(text) + '&url=' + encodeURIComponent(shareUrl), '_blank'); break;
    case 'copy':
      var ct = text + '\n' + shareUrl;
      if (navigator.clipboard) { navigator.clipboard.writeText(ct).then(function(){ var bs = document.querySelectorAll('.share-option'); var b = bs[bs.length-1]; if(b){var o=b.innerHTML; b.innerHTML='<i class="fas fa-check"></i> '+t('cr.copied'); setTimeout(function(){b.innerHTML=o;},2000);} }); }
      else { var ta = document.createElement('textarea'); ta.value = ct; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); }
      break;
  }
  document.getElementById('shareMenu').style.display = 'none';
}

// ===== Lyrics Translation =====
var _lyricsOriginal = '';
var _lyricsLang = '';
var _lyricsTranslateCache = {};

function buildLyricsLangDropdown() {
  var dd = document.getElementById('lyricsLangDropdown');
  if (!dd) return;
  var html = '';
  LANGS.forEach(function(lang) {
    html += '<div class="lyrics-lang-item' + (lang.code === _lyricsLang ? ' active' : '') + '" data-lang="' + lang.code + '" onclick="selectLyricsLang(\'' + lang.code + '\')">'
      + '<span class="lang-flag">' + lang.flag + '</span>'
      + '<span class="lang-name">' + lang.name + '</span>'
      + '</div>';
  });
  dd.innerHTML = html;
}

function toggleLyricsLang() {
  var dd = document.getElementById('lyricsLangDropdown');
  if (!dd) return;
  dd.classList.toggle('active');
}

function selectLyricsLang(code) {
  var dd = document.getElementById('lyricsLangDropdown');
  if (dd) dd.classList.remove('active');
  if (code === _lyricsLang) return;
  _lyricsLang = code;
  var cur = document.getElementById('lyricsLangCurrent');
  if (cur) cur.textContent = code.toUpperCase();
  dd.querySelectorAll('.lyrics-lang-item').forEach(function(item) {
    item.classList.toggle('active', item.dataset.lang === code);
  });
  translateLyrics(code);
}

function translateLyrics(targetLang) {
  if (!_lyricsOriginal) return;
  // If original language, restore
  var origLang = currentLang;
  if (targetLang === origLang || !targetLang) {
    renderLyricsHtml(_lyricsOriginal);
    return;
  }
  // Check cache
  var cacheKey = targetLang + '|' + _lyricsOriginal.substring(0, 50);
  if (_lyricsTranslateCache[cacheKey]) {
    renderLyricsHtml(_lyricsTranslateCache[cacheKey]);
    return;
  }
  // Show loading
  var tEl = document.getElementById('lyricsTranslating');
  var tText = document.getElementById('lyricsTranslatingText');
  if (tEl) tEl.style.display = 'flex';
  var translatingLabels = { es:'Traduciendo...', en:'Translating...', pt:'Traduzindo...', de:'Übersetzen...', fr:'Traduction...', it:'Traduzione...', ja:'翻訳中...' };
  if (tText) tText.textContent = translatingLabels[targetLang] || translatingLabels.en;

  fetch('api/translate.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ text: _lyricsOriginal, lang: targetLang })
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (tEl) tEl.style.display = 'none';
    if (data.success && data.translated) {
      _lyricsTranslateCache[cacheKey] = data.translated;
      renderLyricsHtml(data.translated);
    }
  })
  .catch(function() {
    if (tEl) tEl.style.display = 'none';
  });
}

function renderLyricsHtml(text) {
  var lC = document.getElementById('lyricsContent');
  if (!lC) return;
  var h = '';
  text.split('\n').forEach(function(l) {
    if (/^\[.+\]$/.test(l.trim())) h += '<span class="lyrics-section">' + l.replace(/[\[\]]/g, '') + '</span>';
    else if (l.trim()) h += '<span class="lyrics-line">' + l + '</span>';
  });
  lC.innerHTML = h;
}

function initLyricsLang(lyricsText) {
  _lyricsOriginal = lyricsText;
  _lyricsLang = currentLang;
  var cur = document.getElementById('lyricsLangCurrent');
  if (cur) cur.textContent = currentLang.toUpperCase();
  buildLyricsLangDropdown();
}

// Close lyrics dropdown on outside click
document.addEventListener('click', function(e) {
  if (!e.target.closest('.lyrics-lang-wrapper')) {
    var dd = document.getElementById('lyricsLangDropdown');
    if (dd) dd.classList.remove('active');
  }
});

// ===== Init =====
(function() {
  var lang = localStorage.getItem('ft_lang') || 'es';
  var navLangs = navigator.languages || [navigator.language || ''];
  if (!localStorage.getItem('ft_lang')) { for (var i = 0; i < navLangs.length; i++) { var c = navLangs[i].split('-')[0].toLowerCase(); if (c === 'no') c = 'nb'; if (CL[c]) { lang = c; break; } } }
  applyCreatorLang(lang);
  buildLangDropdown();
})();
