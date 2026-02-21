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
    'cr.readyMobile':'¡Tu video está listo! Deslizá hacia abajo para verlo. 🎬'
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
    'cr.readyMobile':'Your video is ready! Scroll down to watch it. 🎬'
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
    'cr.readyMobile':'Seu vídeo está pronto! Role para baixo para assistir. 🎬'
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

function sendMessage() {
  var input = document.getElementById('chatInput');
  var text = input.value.trim();
  if (!text) return;
  input.value = '';
  addMessage(text, 'user');
  input.disabled = true;
  document.getElementById('chatSendBtn').disabled = true;
  addTyping();
  addMessage('<i class="fas fa-spinner fa-spin"></i> ' + t('cr.generating'), 'ai');

  fetch('api/ai.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ prompt: text, lang: currentLang }) })
  .then(function(res) {
    if (!res.ok) return res.text().then(function(t) { try { return JSON.parse(t); } catch(e) { return { error: 'Server error ' + res.status }; } });
    return res.json();
  })
  .then(function(data) {
    removeTyping();
    input.disabled = false; document.getElementById('chatSendBtn').disabled = false; input.focus();
    if (data.error) { addMessage('<i class="fas fa-exclamation-circle"></i> ' + t('cr.error') + ' (' + data.error + ')', 'ai'); return; }
    if (data.success && data.video) {
      var isMobile = window.innerWidth <= 768;
      addMessage('<i class="fas fa-check-circle" style="color:#22c55e"></i> ' + t(isMobile ? 'cr.readyMobile' : 'cr.ready'), 'ai');
      startVideoExperience(data.video);
    }
  })
  .catch(function(err) {
    removeTyping(); input.disabled = false; document.getElementById('chatSendBtn').disabled = false;
    addMessage('<i class="fas fa-exclamation-circle"></i> ' + t('cr.error'), 'ai');
    console.error('API Error:', err);
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

  // Audio
  var audioEl = document.getElementById('bgAudio');
  audioEl.pause(); audioEl.currentTime = 0;
  if (video.audio) { audioEl.src = video.audio; audioEl.volume = 0.7; audioEl.load(); }
  var npEl = document.getElementById('nowPlaying');
  var npText = document.getElementById('nowPlayingText');
  if (video.audioName) { npText.textContent = video.audioName; npEl.style.display = 'flex'; }

  slideshow.currentSlide = 0;
  startPlayback();
  if (window.innerWidth <= 768) { document.getElementById('creatorPlayer').scrollIntoView({ behavior: 'smooth' }); }
}

function startPlayback() {
  slideshow.playing = true;
  document.getElementById('playPauseBtn').innerHTML = '<i class="fas fa-pause"></i>';
  var audioEl = document.getElementById('bgAudio');
  if (audioEl.src) {
    audioEl.play().catch(function() {
      audioEl.addEventListener('canplaythrough', function retry() { audioEl.play().catch(function(){}); audioEl.removeEventListener('canplaythrough', retry); }, { once: true });
    });
  }
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

// ===== Init =====
(function() {
  var lang = localStorage.getItem('ft_lang') || 'es';
  var navLangs = navigator.languages || [navigator.language || ''];
  if (!localStorage.getItem('ft_lang')) { for (var i = 0; i < navLangs.length; i++) { var c = navLangs[i].split('-')[0].toLowerCase(); if (CL[c]) { lang = c; break; } } }
  applyCreatorLang(lang);
})();
