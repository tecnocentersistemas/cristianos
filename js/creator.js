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

// ===== i18n =====
var CL = {
  es: {
    'cr.title':'Crear Video Musical','cr.subtitle':'Describí qué video querés y la IA lo crea para vos',
    'cr.welcome':'¡Hola! Soy FaithTunes AI. Decime qué tipo de video musical cristiano querés crear. Por ejemplo:',
    'cr.sug1':'Creame una canción de amor a Dios con montañas','cr.sug2':'Una canción de fe con águilas y atardeceres',
    'cr.sug3':'Un video de esperanza con bosques y ríos','cr.sug4':'Video country de paz con corderos y campos verdes',
    'cr.placeholder':'Describí tu video musical cristiano...','cr.emptyTitle':'Tu video aparecerá aquí',
    'cr.emptyDesc':'Escribí en el chat qué tipo de video querés crear y la IA lo generará para vos.',
    'cr.generating':'Creando tu video musical... Esto puede tardar unos segundos.',
    'cr.ready':'¡Tu video está listo! Miralo en el panel de la derecha. 🎬',
    'cr.error':'Hubo un error al crear el video. Intentá de nuevo.',
    'cr.readyMobile':'¡Tu video está listo! Deslizá hacia abajo para verlo. 🎬',
    'cr.sunoLabel':'🎤 Canción cantada con IA',
    'cr.sunoBeta':'BETA',
    'cr.sunoGenerating':'🎤 Generando canción cantada con Suno AI... Esto tarda 1-3 minutos.',
    'cr.sunoReady':'🎤 ¡Tu canción cantada está lista! Escuchala en el panel de la derecha.',
    'cr.sunoReadyMobile':'🎤 ¡Tu canción cantada está lista! Deslizá hacia abajo para escucharla.',
    'cr.sunoWaiting':'⏳ La canción se está generando... ({seconds}s)',
    'cr.sunoWaitingShort':'Esperando canción de Suno AI...',
    'cr.download':'Descargar','cr.share':'Compartir','cr.copyLink':'Copiar link',
    'cr.downloadVideo':'Video MP4','cr.downloadAudio':'Audio MP3',
    'cr.lyricsTitle':'Letra de la canción','cr.copied':'¡Link copiado!','cr.downloadStarted':'Descarga iniciada...'
  },
  en: {
    'cr.title':'Create Music Video','cr.subtitle':'Describe what video you want and AI creates it for you',
    'cr.welcome':'Hi! I\'m FaithTunes AI. Tell me what kind of Christian music video you want to create. For example:',
    'cr.sug1':'Create a love song to God with mountains','cr.sug2':'A song about faith with eagles and sunsets',
    'cr.sug3':'A hope video with forests and rivers','cr.sug4':'Country peace video with lambs and green fields',
    'cr.placeholder':'Describe your Christian music video...','cr.emptyTitle':'Your video will appear here',
    'cr.emptyDesc':'Type in the chat what kind of video you want and AI will generate it for you.',
    'cr.generating':'Creating your music video... This may take a few seconds.',
    'cr.ready':'Your video is ready! Watch it in the right panel. 🎬',
    'cr.error':'There was an error creating the video. Try again.',
    'cr.readyMobile':'Your video is ready! Scroll down to watch it. 🎬',
    'cr.sunoLabel':'🎤 AI Sung Song',
    'cr.sunoBeta':'BETA',
    'cr.sunoGenerating':'🎤 Generating sung song with Suno AI... This takes 1-3 minutes.',
    'cr.sunoReady':'🎤 Your sung song is ready! Listen in the right panel.',
    'cr.sunoReadyMobile':'🎤 Your sung song is ready! Scroll down to listen.',
    'cr.sunoWaiting':'⏳ Song is being generated... ({seconds}s)',
    'cr.sunoWaitingShort':'Waiting for Suno AI song...',
    'cr.download':'Download','cr.share':'Share','cr.copyLink':'Copy link',
    'cr.lyricsTitle':'Song Lyrics','cr.copied':'Link copied!','cr.downloadStarted':'Download started...'
  },
  pt: {
    'cr.title':'Criar Vídeo Musical','cr.subtitle':'Descreva que vídeo você quer e a IA cria para você',
    'cr.welcome':'Olá! Sou FaithTunes AI. Me diga que tipo de vídeo musical cristão quer criar. Por exemplo:',
    'cr.sug1':'Crie uma canção de amor a Deus com montanhas','cr.sug2':'Uma canção de fé com águias e pôr do sol',
    'cr.sug3':'Um vídeo de esperança com florestas e rios','cr.sug4':'Vídeo country de paz com cordeiros e campos verdes',
    'cr.placeholder':'Descreva seu vídeo musical cristão...','cr.emptyTitle':'Seu vídeo aparecerá aqui',
    'cr.emptyDesc':'Digite no chat que tipo de vídeo quer criar e a IA vai gerá-lo para você.',
    'cr.generating':'Criando seu vídeo musical... Isso pode levar alguns segundos.',
    'cr.ready':'Seu vídeo está pronto! Assista no painel da direita. 🎬',
    'cr.error':'Houve um erro ao criar o vídeo. Tente novamente.',
    'cr.readyMobile':'Seu vídeo está pronto! Role para baixo para assistir. 🎬',
    'cr.sunoLabel':'🎤 Música cantada com IA',
    'cr.sunoBeta':'BETA',
    'cr.sunoGenerating':'🎤 Gerando música cantada com Suno AI... Isso leva 1-3 minutos.',
    'cr.sunoReady':'🎤 Sua música cantada está pronta! Ouça no painel da direita.',
    'cr.sunoReadyMobile':'🎤 Sua música cantada está pronta! Role para baixo para ouvir.',
    'cr.sunoWaiting':'⏳ A música está sendo gerada... ({seconds}s)',
    'cr.sunoWaitingShort':'Esperando música do Suno AI...',
    'cr.download':'Baixar','cr.share':'Compartilhar','cr.copyLink':'Copiar link',
    'cr.lyricsTitle':'Letra da música','cr.copied':'Link copiado!','cr.downloadStarted':'Download iniciado...'
  }
};
var currentLang = localStorage.getItem('ft_lang') || 'es';
function t(key) { return (CL[currentLang] || CL.es)[key] || CL.es[key] || key; }
function applyCreatorLang(lang) {
  currentLang = lang; document.documentElement.lang = lang;
  document.querySelectorAll('[data-i18n]').forEach(function(el) { var k = el.getAttribute('data-i18n'); var text = t(k); if (text) el.innerHTML = text; });
  document.querySelectorAll('[data-i18n-placeholder]').forEach(function(el) { var k = el.getAttribute('data-i18n-placeholder'); var text = t(k); if (text) el.placeholder = text; });
  document.querySelectorAll('.lang-btn').forEach(function(b) { b.classList.toggle('active', b.textContent.trim().toLowerCase() === lang); });
}
window.setLang = function(l) { if (!CL[l]) return; localStorage.setItem('ft_lang', l); applyCreatorLang(l); };

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
function useSuggestion(btn) { document.getElementById('chatInput').value = btn.textContent; sendMessage(); }

// Unlock audio on first user interaction (required by browsers)
var _audioUnlocked = false;
function unlockAudio() {
  if (_audioUnlocked) return;
  _audioUnlocked = true;
  var audioEl = document.getElementById('bgAudio');
  // Simple silent play to mark element as user-activated (no AudioContext needed)
  audioEl.muted = true;
  audioEl.play().then(function() {
    audioEl.pause();
    audioEl.muted = false;
    audioEl.currentTime = 0;
  }).catch(function() {
    audioEl.muted = false;
  });
}
document.addEventListener('click', unlockAudio, { once: true });
document.addEventListener('touchstart', unlockAudio, { once: true });
document.addEventListener('touchend', unlockAudio, { once: true });

function sendMessage() {
  var input = document.getElementById('chatInput');
  var text = input.value.trim();
  if (!text) return;
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
      if (!body || !body.trim()) return { error: 'Servidor no respondió. Intentá de nuevo.' };
      try { return JSON.parse(body); }
      catch(e) { return { error: 'Respuesta inválida del servidor' }; }
    });
  })
  .then(function(data) {
    removeTyping();
    if (genMsg) genMsg.remove();
    input.disabled = false; document.getElementById('chatSendBtn').disabled = false; input.focus();
    if (!data || data.error) {
      addMessage('<i class="fas fa-exclamation-circle"></i> ' + t('cr.error') + (data && data.error ? ' (' + data.error + ')' : ''), 'ai');
      return;
    }
    if (data.success && data.video) {
      try {
        var isMobile = window.innerWidth <= 768;
        if (sunoEnabled) {
          // Store video data for later — DON'T show slideshow yet
          window._pendingVideoData = data.video;
          // Show a nice waiting state with title and verse
          var waitInfo = '<div style="text-align:center;padding:1rem 0">';
          waitInfo += '<div style="font-size:1.1rem;font-weight:700;color:var(--primary);margin-bottom:0.5rem">' + (data.video.title || '') + '</div>';
          if (data.video.verses && data.video.verses[0]) {
            waitInfo += '<div style="font-style:italic;color:var(--gray);font-size:0.85rem;margin-bottom:0.5rem">"' + data.video.verses[0].text + '"</div>';
            waitInfo += '<div style="color:var(--primary);font-size:0.75rem">' + data.video.verses[0].ref + '</div>';
          }
          waitInfo += '</div>';
          addMessage(waitInfo, 'ai');
          // Start Suno generation
          startSunoGeneration(null, data.video);
        } else {
          addMessage('<i class="fas fa-check-circle" style="color:#22c55e"></i> ' + t(isMobile ? 'cr.readyMobile' : 'cr.ready'), 'ai');
          startVideoExperience(data.video);
        }
      } catch(e) {
        console.error('Video render error:', e);
        addMessage('<i class="fas fa-exclamation-circle"></i> Error al mostrar el video. Intentá de nuevo.', 'ai');
      }
    }
  })
  .catch(function(err) {
    removeTyping();
    if (genMsg) genMsg.remove();
    input.disabled = false; document.getElementById('chatSendBtn').disabled = false;
    addMessage('<i class="fas fa-exclamation-circle"></i> ' + t('cr.error') + ' (conexión)', 'ai');
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

  // Build text slides: verses + poem
  slideshow.texts = [];
  if (video.verses) { video.verses.forEach(function(v) { slideshow.texts.push({ text: '"' + v.text + '"', ref: '— ' + v.ref }); }); }
  if (video.poem) { for (var i = 0; i < video.poem.length; i += 2) { var line = video.poem[i]; if (video.poem[i+1]) line += '\n' + video.poem[i+1]; slideshow.texts.push({ text: line, ref: '' }); } }

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

  // In Suno mode, skip instrumental audio entirely - wait for real song
  if (video._skipAudio) {
    var npEl = document.getElementById('nowPlaying');
    var npText = document.getElementById('nowPlayingText');
    if (npEl && npText) { npText.textContent = '🎤 ' + t('cr.sunoWaitingShort'); npEl.style.display = 'flex'; }
  } else if (video.audio) {
    audioEl.src = video.audio;
    audioEl.volume = 0.7;
    audioEl.load();
    // Fallback audio if the main one fails to load
    var _audioFallbacks = [
      'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Eternal%20Hope.mp3',
      'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Americana.mp3',
      'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Gymnopedie%20No%201.mp3'
    ];
    var _fallbackTried = false;
    audioEl.onerror = function() {
      if (_fallbackTried) return;
      _fallbackTried = true;
      console.warn('Audio failed to load, trying fallback...');
      var fb = _audioFallbacks[Math.floor(Math.random() * _audioFallbacks.length)];
      audioEl.src = fb;
      audioEl.load();
      audioEl.oncanplay = function() {
        audioEl.play().catch(function(){});
        audioEl.oncanplay = null;
      };
    };
    // Play as soon as enough data is buffered
    audioEl.oncanplay = function() {
      audioEl.play().then(function() {
        // Audio playing successfully
      }).catch(function(err) {
        console.warn('Audio autoplay blocked, retrying...', err);
        setTimeout(function() { audioEl.play().catch(function(){}); }, 500);
      });
      audioEl.oncanplay = null;
    };
  }
  if (!video._skipAudio) {
    var npEl = document.getElementById('nowPlaying');
    var npText = document.getElementById('nowPlayingText');
    if (video.audioName) { npText.textContent = video.audioName; npEl.style.display = 'flex'; }
  }

  slideshow.currentSlide = 0;
  startPlayback();
  if (window.innerWidth <= 768) { document.getElementById('creatorPlayer').scrollIntoView({ behavior: 'smooth' }); }
}

function startPlayback() {
  slideshow.playing = true;
  document.getElementById('playPauseBtn').innerHTML = '<i class="fas fa-pause"></i>';
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

  // Animate text
  var textEl = document.getElementById('slideshowText');
  var refEl = document.getElementById('slideshowRef');
  textEl.classList.remove('visible'); refEl.classList.remove('visible');
  setTimeout(function() {
    var td = slideshow.texts[index % slideshow.texts.length];
    if (td) { textEl.textContent = td.text; refEl.textContent = td.ref; textEl.classList.add('visible'); refEl.classList.add('visible'); }
  }, 1500);
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

// unmute removed - audio plays automatically after user interaction

function toggleMute() {
  var a = document.getElementById('bgAudio');
  var btn = document.getElementById('muteBtn');
  if (!btn) return;
  if (a.muted || a.volume === 0) { a.muted = false; a.volume = 0.7; btn.innerHTML = '<i class="fas fa-volume-up"></i>'; }
  else { a.muted = true; btn.innerHTML = '<i class="fas fa-volume-mute"></i>'; }
}

// ===== Suno AI - Real sung song generation =====
var _sunoTaskId = null;
var _sunoPolling = null;
var _sunoStatusMsg = null;

function startSunoGeneration(userPrompt, videoData) {
  // Build an intelligent prompt using OpenAI's structured output
  var sunoPayload = {};
  var lang = (videoData && videoData.lang) || currentLang;
  var langNames = { es: 'Spanish', en: 'English', pt: 'Portuguese' };
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

      // NOW start the full slideshow experience with the real audio
      var videoData = window._pendingVideoData;
      if (videoData) {
        videoData._skipAudio = true; // We'll set audio manually below
        startVideoExperience(videoData);
      }

      // Play audio IMMEDIATELY from stream URL
      var audioEl = document.getElementById('bgAudio');
      audioEl.pause(); audioEl.src = playUrl; audioEl.volume = 0.7; audioEl.load();
      audioEl.oncanplay = function() { audioEl.play().catch(function(){}); audioEl.oncanplay = null; };

      var npEl = document.getElementById('nowPlaying'), npText = document.getElementById('nowPlayingText');
      if (npEl && npText) { npText.textContent = song.title || 'FaithTunes'; npEl.style.display = 'flex'; }

      // Show FULL lyrics
      var lyricsText = song.prompt || '';
      if (lyricsText) {
        var lP = document.getElementById('playerLyrics'), lC = document.getElementById('lyricsContent');
        if (lP && lC) {
          var h = '';
          lyricsText.split('\n').forEach(function(l) {
            if (/^\[.+\]$/.test(l.trim())) h += '<span class="lyrics-section">' + l.replace(/[\[\]]/g, '') + '</span>';
            else if (l.trim()) h += '<span class="lyrics-line">' + l + '</span>';
          });
          lC.innerHTML = h;
          lP.style.display = 'block';
        }
        // Update poem panel with full lyrics too
        var pe = document.getElementById('playerPoem');
        if (pe) {
          pe.innerHTML = lyricsText.replace(/\[([^\]]+)\]/g, '<strong style="color:var(--primary)">$1</strong>').replace(/\n/g, '<br>');
          pe.style.display = 'block';
        }
        // Distribute lyrics across slideshow
        var cl = lyricsText.replace(/\[[^\]]+\]\n?/g, '').split('\n').filter(function(x) { return x.trim(); });
        var ns = Math.max(slideshow.images.length, 1);
        var lps = Math.max(2, Math.ceil(cl.length / ns));
        slideshow.texts = [];
        for (var i = 0; i < cl.length; i += lps) {
          slideshow.texts.push({ text: cl.slice(i, i + lps).join('\n'), ref: '' });
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

      // Save to VPS in BACKGROUND (don't block playback!)
      var slideImgs = [];
      if (slideshow.images) slideshow.images.forEach(function(img) { if (img.url) slideImgs.push(img.url); });
      fetch('api/save-song.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          audioUrl: saveUrl, title: song.title || '', lyrics: lyricsText,
          tags: song.tags || '', duration: song.duration || 0,
          imageUrl: song.imageUrl || '', taskId: taskId,
          slideImages: slideImgs, creator: ''
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
      }).catch(function(e) { console.warn('Save error:', e); });

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
  var shareUrl = (saved && saved.shareUrl) ? saved.shareUrl : window.location.href;
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

// ===== Init =====
(function() {
  var lang = localStorage.getItem('ft_lang') || 'es';
  var navLangs = navigator.languages || [navigator.language || ''];
  if (!localStorage.getItem('ft_lang')) { for (var i = 0; i < navLangs.length; i++) { var c = navLangs[i].split('-')[0].toLowerCase(); if (CL[c]) { lang = c; break; } } }
  applyCreatorLang(lang);
})();
