// Generate FaithTunes PNG icons for Google Play Store / TWA
// Usage: node tools/gen-png-icons.js
// Requires: npm install canvas (or uses built-in if available)

const { createCanvas } = require('canvas');
const fs = require('fs');
const path = require('path');

const sizes = [72, 96, 128, 144, 152, 192, 384, 512];
const outDir = path.join(__dirname, '..', 'media', 'icons');

sizes.forEach(function(size) {
  const canvas = createCanvas(size, size);
  const ctx = canvas.getContext('2d');

  // Background gradient
  const grad = ctx.createLinearGradient(0, 0, size, size);
  grad.addColorStop(0, '#d97706');
  grad.addColorStop(1, '#7c3aed');

  // Rounded rect background
  const r = size * 0.15;
  ctx.beginPath();
  ctx.moveTo(r, 0);
  ctx.lineTo(size - r, 0);
  ctx.quadraticCurveTo(size, 0, size, r);
  ctx.lineTo(size, size - r);
  ctx.quadraticCurveTo(size, size, size - r, size);
  ctx.lineTo(r, size);
  ctx.quadraticCurveTo(0, size, 0, size - r);
  ctx.lineTo(0, r);
  ctx.quadraticCurveTo(0, 0, r, 0);
  ctx.closePath();
  ctx.fillStyle = grad;
  ctx.fill();

  // Cross symbol
  const cx = size / 2;
  const cy = size * 0.38;
  const cw = size * 0.06;
  const ch = size * 0.35;
  ctx.fillStyle = 'rgba(255,255,255,0.95)';
  ctx.fillRect(cx - cw / 2, cy - ch * 0.15, cw, ch);
  ctx.fillRect(cx - ch * 0.35, cy + ch * 0.08, ch * 0.7, cw);

  // Music note
  const nx = size * 0.58;
  const ny = size * 0.55;
  const ns = size * 0.09;
  ctx.beginPath();
  ctx.arc(nx, ny + ns * 2.2, ns, 0, Math.PI * 2);
  ctx.fill();
  ctx.fillRect(nx + ns - size * 0.015, ny - ns * 0.5, size * 0.03, ns * 2.8);

  // "FT" text
  ctx.fillStyle = 'rgba(255,255,255,0.95)';
  ctx.font = 'bold ' + (size * 0.18) + 'px Arial, sans-serif';
  ctx.textAlign = 'center';
  ctx.textBaseline = 'middle';
  ctx.fillText('FT', size / 2, size * 0.85);

  // Save PNG
  const buffer = canvas.toBuffer('image/png');
  const filePath = path.join(outDir, 'icon-' + size + '.png');
  fs.writeFileSync(filePath, buffer);
  console.log('Created: ' + filePath);
});

console.log('\\nAll PNG icons generated!');
