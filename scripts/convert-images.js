/**
 * Génère une version WebP de chaque image PNG/JPG de public/assets/images.
 * Les WebP sont écrits à côté de l'original (mêmes noms, extension .webp).
 *
 * Usage : npm run images
 */
const sharp = require('sharp');
const fs = require('fs');
const path = require('path');

const dir = path.join(__dirname, '..', 'public', 'assets', 'images');

(async () => {
  const files = fs.readdirSync(dir).filter((f) => /\.(png|jpe?g)$/i.test(f));
  if (files.length === 0) {
    console.log('Aucune image à convertir.');
    return;
  }

  for (const file of files) {
    const src = path.join(dir, file);
    const out = path.join(dir, file.replace(/\.(png|jpe?g)$/i, '.webp'));
    await sharp(src).webp({ quality: 80 }).toFile(out);
    const before = (fs.statSync(src).size / 1024).toFixed(0);
    const after = (fs.statSync(out).size / 1024).toFixed(0);
    console.log(`✓ ${file} (${before} Ko) → ${path.basename(out)} (${after} Ko)`);
  }

  console.log(`\nTerminé : ${files.length} image(s) converties.`);
})().catch((err) => {
  console.error('Erreur de conversion :', err);
  process.exit(1);
});
