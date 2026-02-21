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

// ===== Catalog Data =====
var CATALOG = [
  { id:1,  title:{es:'Montañas de Fe',en:'Mountains of Faith',pt:'Montanhas de Fé'}, genre:'country', theme:'faith', langs:['es','en','pt'], bg:'linear-gradient(135deg,#2d5016,#065f46)', icon:'fa-mountain-sun' },
  { id:2,  title:{es:'Águilas del Cielo',en:'Eagles of Heaven',pt:'Águias do Céu'}, genre:'rock', theme:'hope', langs:['es','en'], bg:'linear-gradient(135deg,#7f1d1d,#92400e)', icon:'fa-dove' },
  { id:3,  title:{es:'Ríos de Paz',en:'Rivers of Peace',pt:'Rios de Paz'}, genre:'worship', theme:'peace', langs:['es','en','pt'], bg:'linear-gradient(135deg,#0c4a6e,#155e75)', icon:'fa-water' },
  { id:4,  title:{es:'Cordero de Dios',en:'Lamb of God',pt:'Cordeiro de Deus'}, genre:'gospel', theme:'faith', langs:['es','en','pt'], bg:'linear-gradient(135deg,#581c87,#4c1d95)', icon:'fa-cross' },
  { id:5,  title:{es:'Amanecer de Gracia',en:'Dawn of Grace',pt:'Amanhecer da Graça'}, genre:'ballad', theme:'love', langs:['es','en','pt'], bg:'linear-gradient(135deg,#9a3412,#c2410c)', icon:'fa-sun' },
  { id:6,  title:{es:'Bosque de Esperanza',en:'Forest of Hope',pt:'Floresta de Esperança'}, genre:'folk', theme:'hope', langs:['es','pt'], bg:'linear-gradient(135deg,#14532d,#166534)', icon:'fa-tree' },
  { id:7,  title:{es:'Alabanza Eterna',en:'Eternal Praise',pt:'Louvor Eterno'}, genre:'worship', theme:'gratitude', langs:['es','en','pt'], bg:'linear-gradient(135deg,#1e3a5f,#312e81)', icon:'fa-hands-praying' },
  { id:8,  title:{es:'Valle de Amor',en:'Valley of Love',pt:'Vale do Amor'}, genre:'country', theme:'love', langs:['es','en'], bg:'linear-gradient(135deg,#365314,#4d7c0f)', icon:'fa-heart' },
  { id:9,  title:{es:'Cielos Abiertos',en:'Open Skies',pt:'Céus Abertos'}, genre:'rock', theme:'faith', langs:['es','en','pt'], bg:'linear-gradient(135deg,#1e40af,#3730a3)', icon:'fa-cloud-sun' },
  { id:10, title:{es:'Palomas de Paz',en:'Doves of Peace',pt:'Pombas da Paz'}, genre:'gospel', theme:'peace', langs:['es','en','pt'], bg:'linear-gradient(135deg,#0f766e,#0d9488)', icon:'fa-dove' },
  { id:11, title:{es:'Gratitud Infinita',en:'Infinite Gratitude',pt:'Gratidão Infinita'}, genre:'ballad', theme:'gratitude', langs:['es','en','pt'], bg:'linear-gradient(135deg,#7c2d12,#a16207)', icon:'fa-star' },
  { id:12, title:{es:'Senderos de Luz',en:'Paths of Light',pt:'Caminhos de Luz'}, genre:'folk', theme:'hope', langs:['es','en','pt'], bg:'linear-gradient(135deg,#064e3b,#047857)', icon:'fa-road' },
  { id:13, title:{es:'Fortaleza en la Roca',en:'Strength in the Rock',pt:'Fortaleza na Rocha'}, genre:'rock', theme:'strength', langs:['es','en','pt'], bg:'linear-gradient(135deg,#451a03,#78350f)', icon:'fa-shield-halved' },
  { id:14, title:{es:'Leones de Judá',en:'Lions of Judah',pt:'Leões de Judá'}, genre:'gospel', theme:'strength', langs:['es','en','pt'], bg:'linear-gradient(135deg,#92400e,#b45309)', icon:'fa-shield' },
  { id:15, title:{es:'Cascadas de Gracia',en:'Waterfalls of Grace',pt:'Cachoeiras de Graça'}, genre:'worship', theme:'love', langs:['es','en','pt'], bg:'linear-gradient(135deg,#0e7490,#0891b2)', icon:'fa-droplet' },
  { id:16, title:{es:'Ciervos del Alba',en:'Deer at Dawn',pt:'Cervos da Aurora'}, genre:'country', theme:'hope', langs:['es','en','pt'], bg:'linear-gradient(135deg,#3f6212,#65a30d)', icon:'fa-leaf' },
  { id:17, title:{es:'Estrellas del Creador',en:'Stars of the Creator',pt:'Estrelas do Criador'}, genre:'ballad', theme:'faith', langs:['es','en','pt'], bg:'linear-gradient(135deg,#1e1b4b,#312e81)', icon:'fa-star' },
  { id:18, title:{es:'Jardín de Oración',en:'Garden of Prayer',pt:'Jardim de Oração'}, genre:'folk', theme:'peace', langs:['es','en','pt'], bg:'linear-gradient(135deg,#166534,#15803d)', icon:'fa-seedling' },
  { id:19, title:{es:'Océano de Misericordia',en:'Ocean of Mercy',pt:'Oceano de Misericórdia'}, genre:'worship', theme:'love', langs:['es','en','pt'], bg:'linear-gradient(135deg,#0c4a6e,#0369a1)', icon:'fa-water' },
  { id:20, title:{es:'Cumbres de Victoria',en:'Peaks of Victory',pt:'Cumes de Vitória'}, genre:'rock', theme:'strength', langs:['es','en','pt'], bg:'linear-gradient(135deg,#1e3a8a,#1d4ed8)', icon:'fa-flag' },
  { id:21, title:{es:'Colibríes del Edén',en:'Hummingbirds of Eden',pt:'Beija-flores do Éden'}, genre:'folk', theme:'gratitude', langs:['es','en','pt'], bg:'linear-gradient(135deg,#047857,#059669)', icon:'fa-feather' },
  { id:22, title:{es:'Desierto Floreciente',en:'Blooming Desert',pt:'Deserto Florescente'}, genre:'country', theme:'hope', langs:['es','en','pt'], bg:'linear-gradient(135deg,#a16207,#ca8a04)', icon:'fa-sun-plant-wilt' },
  { id:23, title:{es:'Arcoíris de Promesas',en:'Rainbow of Promises',pt:'Arco-íris de Promessas'}, genre:'gospel', theme:'hope', langs:['es','en','pt'], bg:'linear-gradient(135deg,#7c3aed,#c026d3)', icon:'fa-rainbow' },
  { id:24, title:{es:'Nido de Protección',en:'Nest of Protection',pt:'Ninho de Proteção'}, genre:'ballad', theme:'peace', langs:['es','en','pt'], bg:'linear-gradient(135deg,#78350f,#a16207)', icon:'fa-feather-pointed' },
  { id:25, title:{es:'Cosecha de Bendiciones',en:'Harvest of Blessings',pt:'Colheita de Bênçãos'}, genre:'country', theme:'gratitude', langs:['es','en','pt'], bg:'linear-gradient(135deg,#854d0e,#a16207)', icon:'fa-wheat-awn' },
  { id:26, title:{es:'Aurora Boreal Divina',en:'Divine Northern Lights',pt:'Aurora Boreal Divina'}, genre:'worship', theme:'faith', langs:['es','en','pt'], bg:'linear-gradient(135deg,#4c1d95,#7c3aed)', icon:'fa-wand-magic-sparkles' },
  { id:27, title:{es:'Mariposas de Transformación',en:'Butterflies of Transformation',pt:'Borboletas de Transformação'}, genre:'folk', theme:'hope', langs:['es','en','pt'], bg:'linear-gradient(135deg,#7e22ce,#a855f7)', icon:'fa-spa' },
  { id:28, title:{es:'Rebaño del Pastor',en:"The Shepherd's Flock",pt:'Rebanho do Pastor'}, genre:'gospel', theme:'love', langs:['es','en','pt'], bg:'linear-gradient(135deg,#166534,#4d7c0f)', icon:'fa-hands-holding-child' },
  { id:29, title:{es:'Torrente de Alabanza',en:'Torrent of Praise',pt:'Torrente de Louvor'}, genre:'rock', theme:'gratitude', langs:['es','en','pt'], bg:'linear-gradient(135deg,#1e40af,#0ea5e9)', icon:'fa-bolt' },
  { id:30, title:{es:'Camino al Hogar',en:'Journey Home',pt:'Caminho ao Lar'}, genre:'ballad', theme:'faith', langs:['es','en','pt'], bg:'linear-gradient(135deg,#92400e,#d97706)', icon:'fa-house-chimney' },
];

var THEME_LABELS = {
  faith:     {es:'Fe',en:'Faith',pt:'Fé'},
  hope:      {es:'Esperanza',en:'Hope',pt:'Esperança'},
  love:      {es:'Amor',en:'Love',pt:'Amor'},
  peace:     {es:'Paz',en:'Peace',pt:'Paz'},
  gratitude: {es:'Gratitud',en:'Gratitude',pt:'Gratidão'},
  strength:  {es:'Fortaleza',en:'Strength',pt:'Fortaleza'}
};

// ===== Video Files Available =====
// Maps catalog IDs to real video files on server
var VIDEO_FILES = {
  1:  { file: 'montanas_de_fe.mp4', thumb: 'montanas_de_fe_thumb.jpg' },
  2:  { file: 'aguilas_del_cielo.mp4', thumb: 'aguilas_del_cielo_thumb.jpg' },
  3:  { file: 'rios_de_paz.mp4', thumb: 'rios_de_paz_thumb.jpg' }
};

function renderCatalog(filter) {
  var grid = document.getElementById('catalogGrid');
  if (!grid) return;
  var lang = document.documentElement.lang || 'es';
  var items = filter && filter !== 'all' ? CATALOG.filter(function(v){ return v.theme === filter; }) : CATALOG;
  grid.innerHTML = items.map(function(v) {
    var t = v.title[lang] || v.title.es;
    var thLabel = THEME_LABELS[v.theme] ? (THEME_LABELS[v.theme][lang] || THEME_LABELS[v.theme].es) : v.theme;
    var hasVideo = VIDEO_FILES[v.id];
    var thumbStyle = hasVideo
      ? 'background:url(media/videos/'+hasVideo.thumb+') center/cover no-repeat, '+v.bg
      : 'background:'+v.bg;
    var playClass = hasVideo ? 'catalog-play catalog-play-real' : 'catalog-play';
    var videoBadge = hasVideo ? '<span class="video-ready-badge"><i class="fas fa-check-circle"></i> VIDEO</span>' : '';
    return '<div class="catalog-card" data-theme-filter="'+v.theme+'" data-video-id="'+v.id+'" onclick="openVideo('+v.id+')">' +
      '<div class="catalog-thumb" style="'+thumbStyle+'">' +
        (hasVideo ? '' : '<i class="fas '+v.icon+' catalog-thumb-icon"></i>') +
        videoBadge +
        '<div class="'+playClass+'"><i class="fas fa-play"></i></div>' +
        '<span class="catalog-genre-tag">'+v.genre.charAt(0).toUpperCase()+v.genre.slice(1)+'</span>' +
        '<div class="catalog-langs">'+v.langs.map(function(l){return '<span class="catalog-lang-badge">'+l.toUpperCase()+'</span>';}).join('')+'</div>' +
      '</div>' +
      '<div class="catalog-info"><h4>'+t+'</h4><span class="catalog-theme-tag">'+thLabel+'</span></div>' +
    '</div>';
  }).join('');
}

// ===== Video Player =====
function openVideo(id) {
  var vf = VIDEO_FILES[id];
  if (!vf) {
    var lang = document.documentElement.lang || 'es';
    var msgs = {
      es: 'Este video estará disponible pronto. ¡Estamos generando contenido nuevo!',
      en: 'This video will be available soon. We are generating new content!',
      pt: 'Este vídeo estará disponível em breve. Estamos gerando novo conteúdo!'
    };
    alert(msgs[lang] || msgs.es);
    return;
  }
  var item = CATALOG.find(function(c){ return c.id === id; });
  var lang = document.documentElement.lang || 'es';
  var modal = document.getElementById('videoModal');
  var player = document.getElementById('videoPlayer');
  var title = document.getElementById('videoTitle');
  var desc = document.getElementById('videoDesc');

  player.src = 'media/videos/' + vf.file;
  title.textContent = item ? (item.title[lang] || item.title.es) : '';

  var descs = {
    es: 'Video musical cristiano con paisajes inspiradores y versículos bíblicos.',
    en: 'Christian music video with inspiring landscapes and Bible verses.',
    pt: 'Vídeo musical cristão com paisagens inspiradoras e versículos bíblicos.'
  };
  desc.textContent = descs[lang] || descs.es;

  modal.classList.add('active');
  document.body.style.overflow = 'hidden';
  player.play().catch(function(){});
}

function closeVideoModal() {
  var modal = document.getElementById('videoModal');
  var player = document.getElementById('videoPlayer');
  player.pause();
  player.src = '';
  modal.classList.remove('active');
  document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeVideoModal();
});

// Catalog filters
document.addEventListener('click', function(e) {
  var btn = e.target.closest('.filter-btn');
  if (!btn) return;
  document.querySelectorAll('.filter-btn').forEach(function(b){ b.classList.remove('active'); });
  btn.classList.add('active');
  var filter = btn.getAttribute('data-filter');
  if (filter === 'all') {
    loadSongsFromAPI('', '');
  } else {
    loadSongsFromAPI('', filter); // filter buttons are themes (faith, hope, etc)
  }
});

// ===== Dynamic Songs from API =====
var _currentAudio = null;
var _currentPlayBtn = null;

function filterByGenre(genre) {
  // Scroll to catalog and load songs
  var cat = document.getElementById('catalog');
  if (cat) cat.scrollIntoView({ behavior: 'smooth' });
  // Reset filter buttons
  document.querySelectorAll('.filter-btn').forEach(function(b){ b.classList.remove('active'); });
  // Load songs by genre
  loadSongsFromAPI(genre, '');
}

function loadSongsFromAPI(genre, theme) {
  var grid = document.getElementById('catalogGrid');
  if (!grid) return;
  var url = 'api/songs.php?limit=50';
  if (genre) url += '&genre=' + encodeURIComponent(genre);
  if (theme) url += '&theme=' + encodeURIComponent(theme);
  grid.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--gray)"><i class="fas fa-spinner fa-spin"></i></div>';
  fetch(url).then(function(r){ return r.json(); }).then(function(data) {
    if (!data.songs || data.songs.length === 0) {
      grid.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--gray)">No hay canciones en esta categoría</div>';
      return;
    }
    renderSongCards(data.songs, grid);
  }).catch(function() {
    // Fallback to static catalog
    renderCatalog(genre || 'all');
  });
}

function renderSongCards(songs, grid) {
  var genreColors = {
    country: 'linear-gradient(135deg,#d97706,#92400e)',
    rock: 'linear-gradient(135deg,#dc2626,#991b1b)',
    gospel: 'linear-gradient(135deg,#7c3aed,#5b21b6)',
    folk: 'linear-gradient(135deg,#16a34a,#15803d)',
    worship: 'linear-gradient(135deg,#0ea5e9,#0369a1)',
    ballad: 'linear-gradient(135deg,#ec4899,#be185d)'
  };
  var genreIcons = {
    country: 'fa-guitar', rock: 'fa-bolt', gospel: 'fa-church',
    folk: 'fa-leaf', worship: 'fa-hand-holding-heart', ballad: 'fa-heart'
  };
  grid.innerHTML = songs.map(function(s) {
    var bg = genreColors[s.genre] || genreColors.worship;
    var icon = genreIcons[s.genre] || 'fa-music';
    var dur = s.duration > 0 ? Math.floor(s.duration/60) + ':' + ('0'+Math.floor(s.duration%60)).slice(-2) : '';
    var badge = s.instrumental ? '<span style="position:absolute;top:8px;right:8px;background:rgba(0,0,0,.6);color:#d97706;padding:2px 8px;border-radius:12px;font-size:.65rem;font-weight:600">INSTRUMENTAL</span>' : '';
    var videoBadge = s.videoUrl ? '<span class="video-ready-badge"><i class="fas fa-check-circle"></i> VIDEO</span>' : '';
    return '<div class="catalog-card" onclick="playSong(this,\'' + s.id + '\',\'' + (s.audioUrl||'').replace(/'/g,"\\'") + '\')">' +
      '<div class="catalog-thumb" style="background:' + bg + '">' +
        '<i class="fas ' + icon + ' catalog-thumb-icon"></i>' +
        badge + videoBadge +
        '<div class="catalog-play"><i class="fas fa-play"></i></div>' +
        '<span class="catalog-genre-tag">' + (s.genre||'').charAt(0).toUpperCase() + (s.genre||'').slice(1) + '</span>' +
        (dur ? '<span style="position:absolute;bottom:8px;right:8px;background:rgba(0,0,0,.7);color:#fff;padding:2px 6px;border-radius:4px;font-size:.65rem">' + dur + '</span>' : '') +
      '</div>' +
      '<div class="catalog-info"><h4>' + (s.title||'Sin título') + '</h4>' +
        '<span class="catalog-theme-tag">' + (s.theme||'').charAt(0).toUpperCase() + (s.theme||'').slice(1) + '</span>' +
      '</div>' +
    '</div>';
  }).join('');
}

function playSong(card, id, audioUrl) {
  if (!audioUrl) return;
  // Stop current
  if (_currentAudio) { _currentAudio.pause(); _currentAudio = null; }
  if (_currentPlayBtn) { _currentPlayBtn.innerHTML = '<i class="fas fa-play"></i>'; _currentPlayBtn = null; }
  // Play new
  var playBtn = card.querySelector('.catalog-play');
  _currentAudio = new Audio(audioUrl);
  _currentAudio.volume = 0.7;
  _currentAudio.play().catch(function(){});
  if (playBtn) { playBtn.innerHTML = '<i class="fas fa-pause"></i>'; _currentPlayBtn = playBtn; }
  _currentAudio.onended = function() {
    if (playBtn) playBtn.innerHTML = '<i class="fas fa-play"></i>';
    _currentAudio = null; _currentPlayBtn = null;
  };
  // Click again to pause
  card.onclick = function(e) {
    e.stopPropagation();
    if (_currentAudio && !_currentAudio.paused) {
      _currentAudio.pause();
      if (playBtn) playBtn.innerHTML = '<i class="fas fa-play"></i>';
      card.onclick = function() { playSong(card, id, audioUrl); };
    }
  };
}

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
    'cat.badge':'Catálogo','cat.title':'Videos listos para disfrutar','cat.subtitle':'Explorá nuestra colección de videos musicales cristianos',
    'cat.all':'Todos','cat.faith':'Fe','cat.hope':'Esperanza','cat.love':'Amor','cat.peace':'Paz','cat.gratitude':'Gratitud','cat.strength':'Fortaleza',
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
    'cat.badge':'Catalog','cat.title':'Videos ready to enjoy','cat.subtitle':'Explore our collection of Christian music videos',
    'cat.all':'All','cat.faith':'Faith','cat.hope':'Hope','cat.love':'Love','cat.peace':'Peace','cat.gratitude':'Gratitude','cat.strength':'Strength',
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
    'cat.badge':'Catálogo','cat.title':'Vídeos prontos para curtir','cat.subtitle':'Explore nossa coleção de vídeos musicais cristãos',
    'cat.all':'Todos','cat.faith':'Fé','cat.hope':'Esperança','cat.love':'Amor','cat.peace':'Paz','cat.gratitude':'Gratidão','cat.strength':'Fortaleza',
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
  renderCatalog(document.querySelector('.filter-btn.active')?.getAttribute('data-filter') || 'all');
  // Load songs from API
  loadSongsFromAPI('', '');
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
      if (hasVideo) actBtns += '<a class="ai-song-btn dl-vid" href="' + s.videoUrl + '" download><i class="fas fa-video"></i> Video</a>';
      actBtns += '<a class="ai-song-btn dl-aud" href="' + s.audioUrl + '" download><i class="fas fa-music"></i> Audio</a>';
      actBtns += '<a class="ai-song-btn share" href="' + s.shareUrl + '" target="_blank"><i class="fas fa-share-alt"></i></a>';

      var creator = s.creator ? 'Por: ' + s.creator : '';
      var tagsShort = (s.tags || '').split(',').slice(0,2).join(', ');

      card.innerHTML = '<div class="ai-song-thumb">' + thumbContent + '<button class="ai-song-play" onclick="playAISong(\'' + s.id + '\')"><i class="fas fa-play"></i></button></div>'
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
      // Open video in modal
      var modal = document.getElementById('videoModal');
      if (modal) {
        var player = modal.querySelector('video');
        if (player) { player.src = s.videoUrl; player.play(); }
        var title = modal.querySelector('h3');
        if (title) title.textContent = s.title || '';
        var desc = modal.querySelector('p');
        if (desc) desc.textContent = s.creator ? 'Creado por: ' + s.creator : '';
        modal.classList.add('active');
      }
    } else if (s.audioUrl) {
      // Play audio
      window.open(s.shareUrl, '_blank');
    }
  });
}
