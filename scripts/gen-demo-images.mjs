// Generate a small library of distinctive cover images for the live-preview
// demos (featured images, hero art). License-clean (ours, GPL), no external
// dependencies — written to docs/demo/ and served from the deployed site so
// WordPress Playground can sideload them.
import { join } from 'node:path';
import sharp from 'sharp';
import { DOCS_DIR, ensureDir } from './lib.mjs';

const W = 1600, H = 1000;

const COVERS = [
  { name: 'ember', a: '#ff9a4d', b: '#b5132a', c: '#ffd27a', d: '#7a0f2b' },
  { name: 'tide',  a: '#33c2cf', b: '#11366b', c: '#a6ede6', d: '#0c1f44' },
  { name: 'grove', a: '#79b765', b: '#1f4d33', c: '#d6ecb6', d: '#13301f' },
  { name: 'dusk',  a: '#9b6cff', b: '#2a1a5e', c: '#c9b8ff', d: '#170f33' },
  { name: 'slate', a: '#48516b', b: '#0b0d12', c: '#00e5a0', d: '#05060a' },
  { name: 'sand',  a: '#ecc996', b: '#a9743f', c: '#fff3df', d: '#7c5126' },
];

function cover({ a, b, c, d }) {
  return `<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="${a}"/><stop offset="1" stop-color="${b}"/>
    </linearGradient>
    <radialGradient id="b1" cx="22%" cy="28%" r="42%">
      <stop offset="0" stop-color="${c}" stop-opacity="0.85"/><stop offset="1" stop-color="${c}" stop-opacity="0"/>
    </radialGradient>
    <radialGradient id="b2" cx="82%" cy="70%" r="50%">
      <stop offset="0" stop-color="${d}" stop-opacity="0.9"/><stop offset="1" stop-color="${d}" stop-opacity="0"/>
    </radialGradient>
    <radialGradient id="b3" cx="65%" cy="18%" r="30%">
      <stop offset="0" stop-color="#ffffff" stop-opacity="0.28"/><stop offset="1" stop-color="#ffffff" stop-opacity="0"/>
    </radialGradient>
  </defs>
  <rect width="${W}" height="${H}" fill="url(#bg)"/>
  <rect width="${W}" height="${H}" fill="url(#b1)"/>
  <rect width="${W}" height="${H}" fill="url(#b2)"/>
  <rect width="${W}" height="${H}" fill="url(#b3)"/>
  <circle cx="1180" cy="300" r="180" fill="${c}" opacity="0.10"/>
  <circle cx="360" cy="760" r="240" fill="${d}" opacity="0.16"/>
  <path d="M0,${H} Q${W * 0.35},${H * 0.6} ${W},${H * 0.88} L${W},${H} Z" fill="#000000" opacity="0.10"/>
</svg>`;
}

const outDir = join(DOCS_DIR, 'demo');
ensureDir(outDir);

for (const c of COVERS) {
  await sharp(Buffer.from(cover(c)), { density: 150 })
    .resize(W, H, { fit: 'cover' })
    .jpeg({ quality: 82 })
    .toFile(join(outDir, `${c.name}.jpg`));
  console.log(`  cover ${c.name}.jpg`);
}
// A square avatar for the demo author.
const avatar = `<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">
  <defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#7c8cff"/><stop offset="1" stop-color="#ff9a4d"/></linearGradient></defs>
  <rect width="400" height="400" fill="url(#g)"/>
  <circle cx="200" cy="160" r="70" fill="#ffffff" opacity="0.92"/>
  <path d="M70,360 a130,130 0 0 1 260,0 Z" fill="#ffffff" opacity="0.92"/>
</svg>`;
await sharp(Buffer.from(avatar)).resize(400, 400).jpeg({ quality: 85 }).toFile(join(outDir, 'avatar.jpg'));
console.log('  cover avatar.jpg');
console.log(`✓ ${COVERS.length + 1} demo image(s) → docs/demo/`);
