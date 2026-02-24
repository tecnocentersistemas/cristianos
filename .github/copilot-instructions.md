# Copilot Instructions - FaithTunes (cristianos)

## Proyecto
Plataforma web para videos musicales cristianos con IA. Nombre de trabajo: "cristianos". Marca: "FaithTunes".  
URL de producción: **https://cristianos.centralchat.pro/**

## Stack técnico
- **Frontend**: HTML5 + CSS3 + JavaScript vanilla (NO frameworks, NO TypeScript, NO bundlers)
- **Backend** (futuro): Node.js en VPS
- **Generación de video** (futuro): FFmpeg en servidor
- **IA** (futuro): OpenAI GPT para letras/composición + RAG para selección inteligente de recursos
- **NO hay .sln ni compilación**. NUNCA usar `run_build`.

## Repositorio Git
- **Repo**: `https://github.com/tecnocentersistemas/cristianos.git`
- **Branch principal**: `main`
- **Carpeta local**: `C:\cristianos`

## Flujo de trabajo y deploy
- SIEMPRE modificar archivos en LOCAL (`C:\cristianos`).
- Hacer commit y push a GitHub:
   ```powershell
   cd C:\cristianos; git add -A; git commit -m "descripción del cambio"; git push origin main
   ```
- El despliegue al VPS es AUTOMÁTICO vía webhook - NUNCA hacer ssh para git pull ni actualizar manualmente el VPS.
- Verificar en: https://cristianos.centralchat.pro/
- NUNCA subir archivos con `scp`. Todo va por git.
- NUNCA compilar ni usar `run_build`.
- Usar `;` como separador en PowerShell, NO `&&`.
- **NO modificar archivos en VPS por comandos directos; todo debe hacerse a través de `git push` y `git pull`.**

## VPS
- IP: `172.96.8.245`
- SSH: `ssh -i "$env:USERPROFILE\.ssh\nueva_llave" root@172.96.8.245` (con identity file, sin passphrase/firma)
- Ruta del proyecto en VPS: `/var/www/cristianos`
- Servidor web: Nginx
- Subdominio: `cristianos.centralchat.pro`

## Estructura del proyecto
```
cristianos/
├── index.html           Landing page principal
├── css/app.css           Estilos globales (dark/light, responsive)
├── js/app.js             JS principal + i18n (ES/EN/PT) + catálogo dinámico
├── media/
│   ├── images/           Paisajes y animales
│   ├── audio/            Instrumentales por género
│   └── videos/           Videos finales MP4
├── data/                 JSON de versículos, catálogo, metadatos RAG
├── api/                  Backend Node.js (futuro)
├── deploy/               Config del VPS (nginx)
└── .github/copilot-instructions.md
```

## Paleta de colores
- Primary: `#d97706` (amber/gold)
- Secondary: `#7c3aed` (violet)
- Gradiente: `linear-gradient(135deg, #d97706, #7c3aed)`
- Dark base: `#0f172a` / Light base: `#f1f5f9`

## Reglas de código
- Archivos modulares de 500-700 líneas máximo.
- Mobile-first: todo responsive.
- No agregar dependencias externas (solo Font Awesome y Google Fonts Inter que ya están).

## Traducciones (i18n) - OBLIGATORIO
- SIEMPRE que se agregue o modifique texto visible en el frontend, agregar traducciones en ES, EN y PT automáticamente.
- Sistema: atributos `data-i18n` en HTML + objeto `L` en `js/app.js`.

## Git workflow
- Cuando el usuario diga "haz commit y push", ejecutar sin preguntar:
  ```powershell
  cd C:\cristianos; git add -A; git commit -m "mensaje descriptivo"; git push origin main
  ```
## Configuración en VPS
- Cuando necesites configurar cosas en el VPS, NO ejecutar comandos SSH largos. En su lugar, crea un archivo de script local, súbelo a través de git y luego ejecútalo en el VPS con un comando SSH corto. Esto evita frustraciones por comandos largos o que se agoten el tiempo de espera.  
- **Evitar múltiples comandos SSH secuenciales.** Utiliza scripts locales para simplificar la ejecución en el VPS.

## Relación con CentralChat
- Proyecto INDEPENDIENTE de CentralChat. Reutiliza patrones (i18n, theme toggle, CSS variables, responsive) pero NO comparte archivos.
- Repo CentralChat: `tecnocentersistemas/chatbot-centralchat` (separado).
- Futuro: reutilizar lógica de RAG y OpenAI de CentralChat adaptada para selección de recursos multimedia.

## Mantenimiento de HTML
- Al usar `multi_replace_string_in_file` para actualizar los cache busters en archivos HTML, verifica que las etiquetas HTML permanezcan intactas. Esto incluye asegurarse de que `<link rel="stylesheet" href="...">` y `<script src="..."></script>` no se conviertan en solo texto. Siempre revisa las líneas afectadas después de las actualizaciones de cache buster.
