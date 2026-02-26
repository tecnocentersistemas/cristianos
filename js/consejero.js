// =============================================
// FaithTunes - Biblical Counselor (consejero.js)
// =============================================

// ===== Faith Declaration Modal =====
(function initFaithModal() {
  var accepted = localStorage.getItem('ft_faith_accepted');
  var modal = document.getElementById('faithModal');
  if (!modal) return;
  if (accepted === 'yes') {
    modal.classList.add('hidden');
    return;
  }
  if (accepted === 'no') {
    showFaithBlocked();
    return;
  }
  var check = document.getElementById('faithCheck');
  var btn = document.getElementById('faithBtn');
  if (check && btn) {
    check.addEventListener('change', function() { btn.disabled = !this.checked; });
  }
})();

function acceptFaith() {
  var check = document.getElementById('faithCheck');
  if (!check || !check.checked) return;
  localStorage.setItem('ft_faith_accepted', 'yes');
  var modal = document.getElementById('faithModal');
  if (modal) { modal.style.opacity = '0'; modal.style.transition = 'opacity 0.3s'; setTimeout(function() { modal.classList.add('hidden'); modal.style.opacity = ''; }, 300); }
}

function rejectFaith() {
  localStorage.setItem('ft_faith_accepted', 'no');
  showFaithBlocked();
}

function showFaithBlocked() {
  var modal = document.getElementById('faithModal');
  var content = document.getElementById('faithContent');
  if (!modal || !content) return;
  modal.classList.remove('hidden');
  content.innerHTML = '<div class="faith-blocked">'
    + '<div class="faith-blocked-icon"><i class="fas fa-ban"></i></div>'
    + '<h2 data-i18n="faith.blockedTitle">' + (t('faith.blockedTitle') || 'Acceso Restringido') + '</h2>'
    + '<p data-i18n="faith.blockedDesc">' + (t('faith.blockedDesc') || 'Esta plataforma es exclusivamente para personas que aceptan a Yeshua (Jesús) como Señor y Salvador. No es posible utilizar FaithTunes sin aceptar la declaración de fe.') + '</p>'
    + '<button class="faith-btn-retry" onclick="retryFaith()">'
    + '<i class="fas fa-redo"></i> ' + (t('faith.retry') || 'Volver a intentar') + '</button>'
    + '</div>';
}

function retryFaith() {
  localStorage.removeItem('ft_faith_accepted');
  location.reload();
}

var _counselorMode = false;
var _counselAudio = null;
var _counselSlideTimer = null;
var _isRecording = false;
var _mediaRecorder = null;

function switchMode(mode) {
  _counselorMode = (mode === 'counsel');
  var tabSong = document.getElementById('tabSong');
  var tabCounsel = document.getElementById('tabCounsel');
  var sunoArea = document.querySelector('.suno-toggle-area');
  var chatInput = document.getElementById('chatInput');
  var chatMessages = document.getElementById('chatMessages');
  var playerEmpty = document.getElementById('playerEmpty');
  var playerActive = document.getElementById('playerActive');
  var micBtn = document.getElementById('micBtn');

  tabSong.classList.toggle('active', !_counselorMode);
  tabCounsel.classList.toggle('active', _counselorMode);

  // Toggle Suno area
  if (sunoArea) sunoArea.style.display = _counselorMode ? 'none' : 'flex';
  // Toggle mic button
  if (micBtn) micBtn.style.display = _counselorMode ? 'flex' : 'none';

  // Update placeholder
  if (chatInput) {
    chatInput.placeholder = _counselorMode ? t('co.placeholder') : t('cr.placeholder');
    chatInput.setAttribute('data-i18n-placeholder', _counselorMode ? 'co.placeholder' : 'cr.placeholder');
  }

  // Reset chat
  if (chatMessages) {
    chatMessages.innerHTML = '';
    if (_counselorMode) {
      addCounselWelcome();
    } else {
      // Re-add song creator welcome
      addCreatorWelcome();
    }
  }

  // Reset player
  if (playerEmpty) playerEmpty.style.display = 'flex';
  if (playerActive) playerActive.style.display = 'none';
  stopCounselAudio();
  _counselHistory = [];

  // Update empty state
  var emptyIcon = playerEmpty ? playerEmpty.querySelector('.player-empty-icon i') : null;
  var emptyTitle = playerEmpty ? playerEmpty.querySelector('h3') : null;
  var emptyDesc = playerEmpty ? playerEmpty.querySelector('p') : null;
  if (_counselorMode) {
    if (emptyIcon) emptyIcon.className = 'fas fa-book-bible';
    if (emptyTitle) { emptyTitle.textContent = t('co.emptyTitle'); emptyTitle.setAttribute('data-i18n', 'co.emptyTitle'); }
    if (emptyDesc) { emptyDesc.textContent = t('co.emptyDesc'); emptyDesc.setAttribute('data-i18n', 'co.emptyDesc'); }
  } else {
    if (emptyIcon) emptyIcon.className = 'fas fa-film';
    if (emptyTitle) { emptyTitle.textContent = t('cr.emptyTitle'); emptyTitle.setAttribute('data-i18n', 'cr.emptyTitle'); }
    if (emptyDesc) { emptyDesc.textContent = t('cr.emptyDesc'); emptyDesc.setAttribute('data-i18n', 'cr.emptyDesc'); }
  }
}

function addCounselWelcome() {
  var container = document.getElementById('chatMessages');
  var div = document.createElement('div');
  div.className = 'chat-msg ai';
  div.innerHTML = '<div class="chat-msg-avatar"><i class="fas fa-book-bible"></i></div>'
    + '<div class="chat-msg-bubble">'
    + '<p data-i18n="co.welcome">' + t('co.welcome') + '</p>'
    + '<div class="chat-suggestions">'
    + '<button class="chat-suggestion" onclick="useSuggestion(this)" data-i18n="co.sug1">' + t('co.sug1') + '</button>'
    + '<button class="chat-suggestion" onclick="useSuggestion(this)" data-i18n="co.sug2">' + t('co.sug2') + '</button>'
    + '<button class="chat-suggestion" onclick="useSuggestion(this)" data-i18n="co.sug3">' + t('co.sug3') + '</button>'
    + '<button class="chat-suggestion" onclick="useSuggestion(this)" data-i18n="co.sug4">' + t('co.sug4') + '</button>'
    + '</div></div>';
  container.appendChild(div);
}

function addCreatorWelcome() {
  var container = document.getElementById('chatMessages');
  var div = document.createElement('div');
  div.className = 'chat-msg ai';
  div.innerHTML = '<div class="chat-msg-avatar"><i class="fas fa-cross"></i></div>'
    + '<div class="chat-msg-bubble">'
    + '<p data-i18n="cr.welcome">' + t('cr.welcome') + '</p>'
    + '<div class="chat-suggestions">'
    + '<button class="chat-suggestion" onclick="useSuggestion(this)" data-i18n="cr.sug1">' + t('cr.sug1') + '</button>'
    + '<button class="chat-suggestion" onclick="useSuggestion(this)" data-i18n="cr.sug2">' + t('cr.sug2') + '</button>'
    + '<button class="chat-suggestion" onclick="useSuggestion(this)" data-i18n="cr.sug3">' + t('cr.sug3') + '</button>'
    + '<button class="chat-suggestion" onclick="useSuggestion(this)" data-i18n="cr.sug4">' + t('cr.sug4') + '</button>'
    + '</div></div>';
  container.appendChild(div);
}

// ===== Conversation history =====
var _counselHistory = [];

// ===== Send counsel request =====
function sendCounselMessage(text) {
  if (!text || !text.trim()) return;
  addMessage(text, 'user');
  _counselHistory.push({ type: 'user', text: text });
  var typingDiv = addTyping();

  fetch('api/consejo.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ topic: text, lang: currentLang, history: _counselHistory })
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (typingDiv) typingDiv.remove();
    if (data.error) {
      addMessage('<i class="fas fa-exclamation-circle"></i> ' + data.error, 'ai');
      return;
    }
    _counselHistory.push({ type: 'ai', title: data.title, counsel: data.counsel });
    showCounselResult(data);
  })
  .catch(function(err) {
    if (typingDiv) typingDiv.remove();
    addMessage('<i class="fas fa-exclamation-circle"></i> Error de conexión', 'ai');
  });
}

function showCounselResult(data) {
  // Chat message
  var versesHtml = '';
  (data.verses || []).forEach(function(v) {
    versesHtml += '<div class="counsel-verse"><strong>' + v.ref + '</strong> ' + v.text + '</div>';
  });

  var msgHtml = '<div class="counsel-response">'
    + '<h4><i class="fas fa-book-bible"></i> ' + (data.title || '') + '</h4>'
    + '<p class="counsel-text">' + (data.counsel || '').replace(/\n/g, '<br>') + '</p>'
    + '<div class="counsel-verses">' + versesHtml + '</div>';
  if (data.prayer) {
    msgHtml += '<div class="counsel-prayer"><i class="fas fa-pray"></i> ' + data.prayer.replace(/\n/g, '<br>') + '</div>';
  }
  if (data.audioUrl) {
    msgHtml += '<button class="counsel-play-btn" onclick="playCounselInChat(this, \'' + data.audioUrl + '\')"><i class="fas fa-play"></i> ' + t('co.listenBtn') + '</button>';
  }
  msgHtml += '</div>';
  addMessage(msgHtml, 'ai');

  // Show in player panel
  showCounselPlayer(data);
}

function showCounselPlayer(data) {
  var playerEmpty = document.getElementById('playerEmpty');
  var playerActive = document.getElementById('playerActive');
  if (playerEmpty) playerEmpty.style.display = 'none';
  if (playerActive) playerActive.style.display = 'block';

  // Hide song-specific elements
  var lyricsPanel = document.getElementById('playerLyrics');
  if (lyricsPanel) lyricsPanel.style.display = 'none';

  // Player info - show TEXT immediately
  var playerTitle = document.getElementById('playerTitle');
  var playerDesc = document.getElementById('playerDesc');
  var playerPoem = document.getElementById('playerPoem');
  var playerVerses = document.getElementById('playerVerses');
  if (playerTitle) playerTitle.textContent = data.title || '';
  if (playerDesc) playerDesc.textContent = '';
  if (playerPoem) {
    playerPoem.innerHTML = (data.counsel || '').replace(/\n/g, '<br>');
    if (data.prayer) playerPoem.innerHTML += '<br><br><em><i class="fas fa-pray"></i> ' + data.prayer.replace(/\n/g, '<br>') + '</em>';
    playerPoem.style.display = 'block';
  }
  if (playerVerses) {
    var vh = '';
    (data.verses || []).forEach(function(v) { vh += '<div class="verse-item"><span class="verse-ref">' + v.ref + '</span><span class="verse-text">' + v.text + '</span></div>'; });
    playerVerses.innerHTML = vh;
  }

  // AUDIO - play immediately
  if (data.audioUrl) {
    var audioEl = document.getElementById('bgAudio');
    if (audioEl) {
      audioEl.src = data.audioUrl;
      audioEl.play().catch(function(){});
    }
    var actions = document.getElementById('playerActions');
    if (actions) {
      actions.innerHTML = '<button class="player-action-btn" onclick="toggleCounselAudio()"><i class="fas fa-pause"></i> ' + t('co.pauseBtn') + '</button>'
        + '<button class="player-action-btn download-btn" onclick="downloadCounselAudio(\'' + data.audioUrl + '\')"><i class="fas fa-download"></i> ' + t('co.downloadBtn') + '</button>';
      actions.style.display = 'flex';
    }
  }

  // Overlay text
  var overlayText = document.getElementById('slideshowText');
  if (overlayText) {
    overlayText.innerHTML = '<div class="counsel-overlay-title">' + (data.title || '') + '</div>';
  }

  // Scroll to player on mobile
  if (window.innerWidth < 900) {
    setTimeout(function() { playerActive.scrollIntoView({ behavior: 'smooth' }); }, 300);
  }

  // IMAGES - slideshow
  if (data.images && data.images.length) {
    applyCounselSlideshow(data.images);
  }
}

function applyCounselSlideshow(images) {
  var slides = document.getElementById('slideshowSlides');
  if (!slides || !images.length) return;
  slides.innerHTML = '';
  images.forEach(function(img, i) {
    var d = document.createElement('div');
    d.className = 'slideshow-slide' + (i === 0 ? ' active' : '');
    d.style.backgroundImage = 'url(' + img.url + ')';
    slides.appendChild(d);
  });
  startCounselSlideshow();
}

function startCounselSlideshow() {
  if (_counselSlideTimer) clearInterval(_counselSlideTimer);
  var idx = 0;
  _counselSlideTimer = setInterval(function() {
    var slides = document.querySelectorAll('#slideshowSlides .slideshow-slide');
    if (!slides.length) return;
    slides[idx].classList.remove('active');
    idx = (idx + 1) % slides.length;
    slides[idx].classList.add('active');
  }, 6000);
}

function playCounselInChat(btn, url) {
  if (_counselAudio && !_counselAudio.paused) {
    _counselAudio.pause();
    btn.innerHTML = '<i class="fas fa-play"></i> ' + t('co.listenBtn');
    return;
  }
  _counselAudio = new Audio(url);
  _counselAudio.play();
  btn.innerHTML = '<i class="fas fa-pause"></i> ' + t('co.pauseBtn');
  _counselAudio.onended = function() {
    btn.innerHTML = '<i class="fas fa-play"></i> ' + t('co.listenBtn');
  };
}

function toggleCounselAudio() {
  var audioEl = document.getElementById('bgAudio');
  if (!audioEl) return;
  if (audioEl.paused) { audioEl.play(); } else { audioEl.pause(); }
}

function stopCounselAudio() {
  if (_counselAudio) { _counselAudio.pause(); _counselAudio = null; }
  var audioEl = document.getElementById('bgAudio');
  if (audioEl) { audioEl.pause(); audioEl.src = ''; }
  if (_counselSlideTimer) { clearInterval(_counselSlideTimer); _counselSlideTimer = null; }
}

function downloadCounselAudio(url) {
  fetch(url).then(function(r) { return r.blob(); }).then(function(b) {
    var a = document.createElement('a');
    a.href = URL.createObjectURL(b);
    a.download = 'FaithTunes-Consejo.mp3';
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
  }).catch(function() { window.open(url, '_blank'); });
}

// ===== Voice input (microphone) =====
function toggleMic() {
  if (_isRecording) { stopRecording(); return; }
  if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
    alert('Tu navegador no soporta grabación de audio');
    return;
  }
  var micBtn = document.getElementById('micBtn');
  if (micBtn) { micBtn.classList.add('recording'); micBtn.innerHTML = '<i class="fas fa-stop"></i>'; }
  _isRecording = true;

  navigator.mediaDevices.getUserMedia({ audio: true }).then(function(stream) {
    _mediaRecorder = new MediaRecorder(stream, { mimeType: 'audio/webm' });
    var chunks = [];
    _mediaRecorder.ondataavailable = function(e) { if (e.data.size > 0) chunks.push(e.data); };
    _mediaRecorder.onstop = function() {
      stream.getTracks().forEach(function(t) { t.stop(); });
      var blob = new Blob(chunks, { type: 'audio/webm' });
      transcribeAudio(blob);
    };
    _mediaRecorder.start();
  }).catch(function(err) {
    _isRecording = false;
    if (micBtn) { micBtn.classList.remove('recording'); micBtn.innerHTML = '<i class="fas fa-microphone"></i>'; }
  });
}

function stopRecording() {
  _isRecording = false;
  var micBtn = document.getElementById('micBtn');
  if (micBtn) { micBtn.classList.remove('recording'); micBtn.innerHTML = '<i class="fas fa-microphone"></i>'; }
  if (_mediaRecorder && _mediaRecorder.state !== 'inactive') { _mediaRecorder.stop(); }
}

function transcribeAudio(blob) {
  var chatInput = document.getElementById('chatInput');
  if (chatInput) chatInput.placeholder = t('co.transcribing');

  var formData = new FormData();
  formData.append('audio', blob, 'voice.webm');
  formData.append('lang', currentLang);

  fetch('api/transcribe.php', { method: 'POST', body: formData })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (chatInput) chatInput.placeholder = t(_counselorMode ? 'co.placeholder' : 'cr.placeholder');
    if (data.text) {
      if (chatInput) chatInput.value = data.text;
      // Auto-send
      if (_counselorMode) { sendCounselMessage(data.text); chatInput.value = ''; }
      else { sendMessage(); }
    }
  })
  .catch(function() {
    if (chatInput) chatInput.placeholder = t(_counselorMode ? 'co.placeholder' : 'cr.placeholder');
  });
}

// ===== Override sendMessage to route to counsel =====
var _originalSendMessage = typeof sendMessage === 'function' ? sendMessage : null;
function patchSendMessage() {
  var origSend = window.sendMessage;
  window.sendMessage = function() {
    if (_counselorMode) {
      var input = document.getElementById('chatInput');
      var text = input ? input.value.trim() : '';
      if (text) { sendCounselMessage(text); input.value = ''; }
    } else {
      origSend.apply(this, arguments);
    }
  };
}
// Patch after creator.js loads
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', patchSendMessage);
} else {
  setTimeout(patchSendMessage, 100);
}
