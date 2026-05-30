/**
 * ATLÉX-SPORT — Galerie : filtre par catégorie + lightbox.
 */
function filterGallery(cat, btn) {
    document.querySelectorAll('.g-cell').forEach(function (item) {
        var show = cat === 'all' || item.getAttribute('data-cat') === cat;
        item.classList.toggle('hidden', !show);
    });
    document.querySelectorAll('.g-filter').forEach(function (b) {
        b.classList.remove('bg-atlex-red', 'text-white');
        b.classList.add('bg-white/5', 'text-white/60');
    });
    if (btn) {
        btn.classList.remove('bg-white/5', 'text-white/60');
        btn.classList.add('bg-atlex-red', 'text-white');
    }
}

function openLightbox(src, alt) {
    var box = document.getElementById('lightbox');
    var img = document.getElementById('lightbox-img');
    if (!box || !img) { return; }
    img.setAttribute('src', src);
    img.setAttribute('alt', alt || '');
    box.classList.remove('hidden');
    box.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeLightbox(event) {
    var box = document.getElementById('lightbox');
    if (!box) { return; }
    if (event && event.target && event.target.id === 'lightbox-img') { return; }
    box.classList.add('hidden');
    box.classList.remove('flex');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { closeLightbox(); }
});
