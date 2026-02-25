// Simple PNG icon generator - creates solid gradient icons with "FT" text
// Uses pure Node.js Buffer to create minimal valid PNG files

function createPNG(size) {
  // Create a simple icon as SVG, then we'll use it differently
  // For now, create a placeholder that works
  var svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 ${size} ${size}">
    <defs>
      <linearGradient id="g" x1="0%" y1="0%" x2="100%" y2="100%">
        <stop offset="0%" style="stop-color:#d97706"/>
        <stop offset="100%" style="stop-color:#7c3aed"/>
      </linearGradient>
    </defs>
    <rect width="${size}" height="${size}" rx="${size*0.15}" fill="url(#g)"/>
    <text x="${size/2}" y="${size*0.45}" font-family="Arial,sans-serif" font-size="${size*0.22}" font-weight="bold" fill="white" text-anchor="middle" dominant-baseline="middle">✝</text>
    <text x="${size/2}" y="${size*0.72}" font-family="Arial,sans-serif" font-size="${size*0.2}" font-weight="bold" fill="white" text-anchor="middle" dominant-baseline="middle">♪</text>
    <text x="${size/2}" y="${size*0.93}" font-family="Arial,sans-serif" font-size="${size*0.13}" font-weight="bold" fill="rgba(255,255,255,0.9)" text-anchor="middle" dominant-baseline="middle">FT</text>
  </svg>`;
  return svg;
}

var fs = require('fs');
var sizes = [72, 96, 128, 144, 152, 192, 384, 512];
var dir = require('path').join(__dirname, '..', 'media', 'icons');

sizes.forEach(function(s) {
  var svg = createPNG(s);
  fs.writeFileSync(dir + '/icon-' + s + '.svg', svg);
  console.log('Created icon-' + s + '.svg');
});

console.log('SVG icons created. For PNG conversion, use the browser tool at tools/generate-icons.html');
