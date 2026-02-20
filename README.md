# FaithTunes - Videos Musicales Cristianos con IA

Plataforma web para generar y disfrutar videos musicales cristianos con paisajes, animales y versículos bíblicos.

## Tecnologías
- **Frontend**: HTML5, CSS3, JavaScript vanilla
- **i18n**: ES / EN / PT (auto-detección)
- **Tema**: Dark / Light mode
- **Backend** (futuro): Node.js + FFmpeg + OpenAI
- **Media**: Imágenes pre-cargadas + instrumentales pre-generados

## Estructura
```
cristianos/
├── index.html          Landing page
├── css/app.css          Estilos
├── js/app.js            JS + i18n + catálogo
├── media/
│   ├── images/          Paisajes, animales (futuro)
│   └── audio/           Instrumentales por género (futuro)
├── api/                 Backend endpoints (futuro)
└── README.md
```

## Roadmap
1. ✅ Landing page con catálogo demo
2. ⬜ Cargar imágenes reales (Pexels/Unsplash)
3. ⬜ Generar instrumentales base con Suno (una vez)
4. ⬜ Backend: FFmpeg para combinar audio + imágenes → MP4
5. ⬜ Sistema de RAG para metadatos de recursos
6. ⬜ Panel admin para gestionar contenido
7. ⬜ Sistema de usuarios y planes

## Despliegue
- Local → GitHub → VPS (auto-deploy)
- `pm2 restart all` después del push
