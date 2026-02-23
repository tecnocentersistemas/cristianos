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

// ===== i18n =====
var L = {
  es: {
    'nav.how':'Cómo Funciona','nav.genres':'Géneros','nav.catalog':'Catálogo','nav.pricing':'Precios','nav.explore':'Explorar',
    'hero.badge':'Videos musicales cristianos con IA',
    'hero.title':'Melodías de <span class="gradient-text">fe</span> con paisajes inspiradores',
    'hero.desc':'Disfruta de videos musicales cristianos con paisajes y animales de fondo. Country, rock, gospel y más géneros. Disponible en múltiples idiomas.',
    'hero.cta':'Ver Catálogo','hero.create':'Crear con IA','hero.how':'Cómo Funciona',
    'hero.videos':'Videos disponibles','hero.genreCount':'Géneros musicales','hero.langCount':'Idiomas',
    'how.badge':'Así de fácil','how.title':'Cómo funciona','how.subtitle':'En 3 simples pasos tenés tu video musical cristiano',
    'how.s1t':'Elegí un tema','how.s1d':'Fe, esperanza, amor, paz, gratitud... elegí el mensaje que querés transmitir.',
    'how.s2t':'Seleccioná el género','how.s2d':'Country, rock, gospel, folk, worship o balada. Cada uno con su estilo único.',
    'how.s3t':'Disfrutá tu video','how.s3d':'Mirá o descargá un video con música, paisajes inspiradores y versículos bíblicos.',
    'genre.badge':'Géneros','genre.title':'Múltiples estilos para cada momento','genre.subtitle':'Desde country suave hasta rock inspirador',
    'genre.country':'Melodías suaves con guitarra acústica y paisajes rurales',
    'genre.rock':'Energía y fuerza con guitarras eléctricas y montañas',
    'genre.gospel':'Coros poderosos con cielos abiertos y luz divina',
    'genre.folk':'Sonidos acústicos con bosques y ríos cristalinos',
    'genre.worship':'Alabanza contemporánea con atardeceres y naturaleza',
    'genre.balladName':'Balada','genre.ballad':'Piano y cuerdas con valles verdes y animales en paz',
    'genre.listen':'Escuchar','genre.playing':'Reproduciendo','genre.nowplaying':'Escuchando ahora',
    'cat.badge':'Catálogo','cat.aititle':'Canciones Creadas con IA','cat.aisubtitle':'Canciones cristianas reales generadas por nuestra comunidad usando inteligencia artificial','cat.createbtn':'Crear mi propia canción',
    'feat.badge':'Características','feat.title':'¿Por qué elegir FaithTunes?',
    'feat.f1t':'Multilingüe','feat.f1d':'Videos en español, inglés y portugués. La interfaz se adapta automáticamente.',
    'feat.f2t':'Paisajes Inspiradores','feat.f2d':'Montañas, ríos, atardeceres y bosques que elevan el espíritu.',
    'feat.f3t':'Animales de la Creación','feat.f3d':'Águilas, palomas, corderos. La belleza de la creación en cada video.',
    'feat.f4t':'Versículos Bíblicos','feat.f4d':'Cada video incluye versículos relevantes con tipografía elegante.',
    'feat.f5t':'Alta Calidad HD','feat.f5d':'Listos para compartir en redes sociales, iglesias o devocionales.',
    'feat.f6t':'Potenciado por IA','feat.f6d':'Inteligencia artificial para contenido único y personalizado.',
    'price.badge':'Precios','price.title':'Planes simples y accesibles','price.subtitle':'Empezá gratis y accedé a todo el catálogo',
    'price.free':'Gratis','price.freeDesc':'Para conocer la plataforma',
    'price.f1':'5 videos por mes','price.f2':'Todos los géneros','price.f3':'Calidad estándar','price.f4':'3 idiomas',
    'price.startFree':'Empezar Gratis',
    'price.proDesc':'Acceso completo al catálogo',
    'price.p1':'Videos ilimitados','price.p2':'Todos los géneros','price.p3':'Calidad HD',
    'price.p4':'Descarga directa MP4','price.p5':'Videos personalizados con IA','price.p6':'Nuevos videos cada semana',
    'price.getPremium':'Elegir Premium',
    'cta.title':'¿Listo para inspirarte con música y fe?',
    'cta.desc':'Explorá nuestro catálogo de videos musicales cristianos y compartí la palabra de Dios.',
    'cta.btn':'Explorar Videos',
    'footer.copy':'© 2025 FaithTunes. Todos los derechos reservados.'
  },
  en: {
    'nav.how':'How It Works','nav.genres':'Genres','nav.catalog':'Catalog','nav.pricing':'Pricing','nav.explore':'Explore',
    'hero.badge':'Christian music videos with AI',
    'hero.title':'Melodies of <span class="gradient-text">faith</span> with inspiring landscapes',
    'hero.desc':'Enjoy Christian music videos with landscapes and animals in the background. Country, rock, gospel and more genres. Available in multiple languages.',
    'hero.cta':'View Catalog','hero.create':'Create with AI','hero.how':'How It Works',
    'hero.videos':'Videos available','hero.genreCount':'Music genres','hero.langCount':'Languages',
    'how.badge':'Easy as 1-2-3','how.title':'How it works','how.subtitle':'Get your Christian music video in 3 simple steps',
    'how.s1t':'Choose a theme','how.s1d':'Faith, hope, love, peace, gratitude... choose the message you want to share.',
    'how.s2t':'Select the genre','how.s2d':'Country, rock, gospel, folk, worship or ballad. Each with its unique style.',
    'how.s3t':'Enjoy your video','how.s3d':'Watch or download a video with music, inspiring landscapes and Bible verses.',
    'genre.badge':'Genres','genre.title':'Multiple styles for every moment','genre.subtitle':'From soft country to inspiring rock',
    'genre.country':'Soft melodies with acoustic guitar and rural landscapes',
    'genre.rock':'Energy and strength with electric guitars and mountains',
    'genre.gospel':'Powerful choirs with open skies and divine light',
    'genre.folk':'Acoustic sounds with forests and crystal-clear rivers',
    'genre.worship':'Contemporary praise with sunsets and nature',
    'genre.balladName':'Ballad','genre.ballad':'Piano and strings with green valleys and peaceful animals',
    'genre.listen':'Listen','genre.playing':'Playing','genre.nowplaying':'Now playing',
    'cat.badge':'Catalog',
    'feat.badge':'Features','feat.title':'Why choose FaithTunes?',
    'feat.f1t':'Multilingual','feat.f1d':'Videos in Spanish, English and Portuguese. The interface adapts automatically.',
    'feat.f2t':'Inspiring Landscapes','feat.f2d':'Mountains, rivers, sunsets and forests that uplift the spirit.',
    'feat.f3t':'Animals of Creation','feat.f3d':'Eagles, doves, lambs. The beauty of creation in every video.',
    'feat.f4t':'Bible Verses','feat.f4d':'Each video includes relevant verses with elegant typography.',
    'feat.f5t':'HD Quality','feat.f5d':'Ready to share on social media, churches or personal devotionals.',
    'feat.f6t':'AI Powered','feat.f6d':'Artificial intelligence for unique and personalized content.',
    'price.badge':'Pricing','price.title':'Simple and affordable plans','price.subtitle':'Start free and access the full catalog',
    'price.free':'Free','price.freeDesc':'To explore the platform',
    'price.f1':'5 videos per month','price.f2':'All genres','price.f3':'Standard quality','price.f4':'3 languages',
    'price.startFree':'Start Free',
    'price.proDesc':'Full catalog access',
    'price.p1':'Unlimited videos','price.p2':'All genres','price.p3':'HD quality',
    'price.p4':'Direct MP4 download','price.p5':'AI-personalized videos','price.p6':'New videos every week',
    'price.getPremium':'Get Premium',
    'cta.title':'Ready to be inspired by music and faith?',
    'cta.desc':'Explore our catalog of Christian music videos and share the word of God.',
    'cta.btn':'Explore Videos',
    'footer.copy':'© 2025 FaithTunes. All rights reserved.'
  },
  pt: {
    'nav.how':'Como Funciona','nav.genres':'Gêneros','nav.catalog':'Catálogo','nav.pricing':'Preços','nav.explore':'Explorar',
    'hero.badge':'Vídeos musicais cristãos com IA',
    'hero.title':'Melodias de <span class="gradient-text">fé</span> com paisagens inspiradoras',
    'hero.desc':'Desfrute de vídeos musicais cristãos com paisagens e animais de fundo. Country, rock, gospel e mais gêneros. Disponível em vários idiomas.',
    'hero.cta':'Ver Catálogo','hero.create':'Criar com IA','hero.how':'Como Funciona',
    'hero.videos':'Vídeos disponíveis','hero.genreCount':'Gêneros musicais','hero.langCount':'Idiomas',
    'how.badge':'Simples assim','how.title':'Como funciona','how.subtitle':'Em 3 passos simples você tem seu vídeo musical cristão',
    'how.s1t':'Escolha um tema','how.s1d':'Fé, esperança, amor, paz, gratidão... escolha a mensagem que deseja transmitir.',
    'how.s2t':'Selecione o gênero','how.s2d':'Country, rock, gospel, folk, worship ou balada. Cada um com seu estilo único.',
    'how.s3t':'Curta seu vídeo','how.s3d':'Assista ou baixe um vídeo com música, paisagens inspiradoras e versículos bíblicos.',
    'genre.badge':'Gêneros','genre.title':'Múltiplos estilos para cada momento','genre.subtitle':'Do country suave ao rock inspirador',
    'genre.country':'Melodias suaves com violão e paisagens rurais',
    'genre.rock':'Energia e força com guitarras elétricas e montanhas',
    'genre.gospel':'Corais poderosos com céus abertos e luz divina',
    'genre.folk':'Sons acústicos com florestas e rios cristalinos',
    'genre.worship':'Louvor contemporâneo com pôr do sol e natureza',
    'genre.balladName':'Balada','genre.ballad':'Piano e cordas com vales verdes e animais em paz',
    'genre.listen':'Ouvir','genre.playing':'Reproduzindo','genre.nowplaying':'Ouvindo agora',
    'cat.badge':'Catálogo','cat.aititle':'Canções Criadas com IA','cat.aisubtitle':'Canções cristãs reais geradas pela nossa comunidade usando inteligência artificial','cat.createbtn':'Criar minha própria canção',
    'feat.badge':'Recursos','feat.title':'Por que escolher FaithTunes?',
    'feat.f1t':'Multilíngue','feat.f1d':'Vídeos em espanhol, inglês e português. A interface se adapta automaticamente.',
    'feat.f2t':'Paisagens Inspiradoras','feat.f2d':'Montanhas, rios, pôr do sol e florestas que elevam o espírito.',
    'feat.f3t':'Animais da Criação','feat.f3d':'Águias, pombas, cordeiros. A beleza da criação em cada vídeo.',
    'feat.f4t':'Versículos Bíblicos','feat.f4d':'Cada vídeo inclui versículos relevantes com tipografia elegante.',
    'feat.f5t':'Alta Qualidade HD','feat.f5d':'Prontos para compartilhar em redes sociais, igrejas ou devocionais.',
    'feat.f6t':'Potencializado por IA','feat.f6d':'Inteligência artificial para conteúdo único e personalizado.',
    'price.badge':'Preços','price.title':'Planos simples e acessíveis','price.subtitle':'Comece grátis e acesse todo o catálogo',
    'price.free':'Grátis','price.freeDesc':'Para conhecer a plataforma',
    'price.f1':'5 vídeos por mês','price.f2':'Todos os gêneros','price.f3':'Qualidade padrão','price.f4':'3 idiomas',
    'price.startFree':'Começar Grátis',
    'price.proDesc':'Acesso completo ao catálogo',
    'price.p1':'Vídeos ilimitados','price.p2':'Todos os gêneros','price.p3':'Qualidade HD',
    'price.p4':'Download direto MP4','price.p5':'Vídeos personalizados com IA','price.p6':'Novos vídeos toda semana',
    'price.getPremium':'Escolher Premium',
    'cta.title':'Pronto para se inspirar com música e fé?',
    'cta.desc':'Explore nosso catálogo de vídeos musicais cristãos e compartilhe a palavra de Deus.',
    'cta.btn':'Explorar Vídeos',
    'footer.copy':'© 2025 FaithTunes. Todos os direitos reservados.'
  }
};

function detectLang() {
  var s = localStorage.getItem('ft_lang');
  if (s && L[s]) return s;
  var navLangs = navigator.languages || [navigator.language || ''];
  for (var i = 0; i < navLangs.length; i++) {
    var c = navLangs[i].split('-')[0].toLowerCase();
    if (L[c]) return c;
  }
  try {
    var tz = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
    if (/Sao_Paulo|Fortaleza|Recife|Belem|Manaus|Cuiaba|Bahia/.test(tz)) return 'pt';
    if (/New_York|Chicago|Denver|Los_Angeles|Toronto|London/.test(tz)) return 'en';
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
  var titles = {
    es: 'FaithTunes - Videos Musicales Cristianos con IA',
    en: 'FaithTunes - Christian Music Videos with AI',
    pt: 'FaithTunes - Vídeos Musicais Cristãos com IA'
  };
  document.title = titles[lang] || titles.es;
  document.querySelectorAll('.lang-btn').forEach(function(b) {
    b.classList.toggle('active', b.textContent.trim().toLowerCase() === lang);
  });
}

window.setLang = function(l) {
  if (!L[l]) return;
  localStorage.setItem('ft_lang', l);
  applyLang(l);
};

// Init
(function() {
  var lang = detectLang();
  applyLang(lang);
  // Load AI songs gallery
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
        if (player) { player.src = s.videoUrl; player.play().catch(function(){}); }
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
  if (e.key === 'Escape') { closeVideoModal(); closeGenreSongs(); }
});

// ===== Genre Songs Player =====
var _catalogCache = null;
var _genreAudio = null;
var _currentGenre = '';

function loadCatalog(cb) {
  if (_catalogCache) return cb(_catalogCache);
  fetch('data/catalog.json')
  .then(function(r) { return r.json(); })
  .then(function(data) { _catalogCache = data; cb(data); })
  .catch(function() { cb(null); });
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
  panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

  // Highlight active genre card
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
      var mins = Math.floor((s.duration || 0) / 60);
      var secs = (s.duration || 0) % 60;
      var durStr = mins + ':' + (secs < 10 ? '0' : '') + secs;

      var item = document.createElement('div');
      item.className = 'genre-song-item';
      item.setAttribute('data-id', s.id);
      item.innerHTML = '<div class="genre-song-thumb" style="background:' + (s.bg || 'var(--primary)') + '">'
        + (s.thumbnail ? '<img src="' + s.thumbnail + '" alt="' + title + '" onerror="this.style.display=\'none\'">' : '')
        + '<div class="genre-song-play-overlay"><i class="fas fa-play"></i></div></div>'
        + '<div class="genre-song-info"><div class="genre-song-title">' + title + '</div>'
        + '<div class="genre-song-desc">' + desc + '</div>'
        + '<div class="genre-song-meta"><span class="genre-song-duration"><i class="fas fa-clock"></i> ' + durStr + '</span>'
        + (s.verses ? '<span class="genre-song-verses"><i class="fas fa-book-bible"></i> ' + s.verses.join(', ') + '</span>' : '')
        + '</div></div>';
      item.onclick = function() { playGenreSong(s); };
      list.appendChild(item);
    });
  });
}

function playGenreSong(song) {
  if (_genreAudio) { _genreAudio.pause(); }

  // Remove playing state from all items
  document.querySelectorAll('.genre-song-item').forEach(function(el) { el.classList.remove('playing'); });

  // Find and mark current
  var item = document.querySelector('.genre-song-item[data-id="' + song.id + '"]');
  if (item) item.classList.add('playing');

  _genreAudio = new Audio(song.audioUrl);
  _genreAudio.volume = 0.7;
  _genreAudio.play().catch(function(e) { console.log('Audio play error:', e); });
  _genreAudio.onended = function() {
    if (item) item.classList.remove('playing');
    // Auto-play next song in the genre
    autoPlayNext(song);
  };
}

function autoPlayNext(currentSong) {
  if (!_catalogCache || !_currentGenre) return;
  var songs = _catalogCache.videos.filter(function(v) { return v.genre === _currentGenre; });
  var idx = -1;
  for (var i = 0; i < songs.length; i++) {
    if (songs[i].id === currentSong.id) { idx = i; break; }
  }
  if (idx >= 0 && idx < songs.length - 1) {
    playGenreSong(songs[idx + 1]);
  }
}

function closeGenreSongs() {
  var panel = document.getElementById('genreSongsPanel');
  if (panel) panel.style.display = 'none';
  if (_genreAudio) { _genreAudio.pause(); _genreAudio = null; }
  document.querySelectorAll('.genre-card').forEach(function(c) { c.classList.remove('genre-active'); });
  document.querySelectorAll('.genre-song-item').forEach(function(el) { el.classList.remove('playing'); });
}
