<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>LumbungPintar - Demo</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
  body { background: #f4f7f4; }
  .nav-pills .nav-link.active { background: #198754; }
  .thumb { width: 100%; height: 150px; object-fit: cover; background: #e9ecef; display: block; }
  .ph { display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: #adb5bd; }
  #msgs { height: 340px; overflow-y: auto; display: flex; flex-direction: column; gap: .4rem; }
  .bubble { max-width: 75%; padding: .4rem .75rem; border-radius: 1rem; white-space: pre-wrap; word-break: break-word; }
  .bubble.me { background: #198754; color: #fff; margin-left: auto; }
  .bubble.them { background: #e9ecef; }
</style>
</head>
<body>
<div id="busy" class="progress position-fixed top-0 start-0 w-100 d-none" style="height:3px;z-index:2000">
  <div class="progress-bar progress-bar-striped progress-bar-animated bg-success w-100"></div>
</div>
<div id="app"></div>
<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toasts"></div>
<input type="file" id="photoInput" accept="image/*" class="d-none">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
'use strict';

const API = '/api';   // ubah jika front end dijalankan dari alamat lain (dan atur CORS)
const $ = (s, el = document) => el.querySelector(s);
const S = {
  token: localStorage.getItem('lp_token'),
  user: JSON.parse(localStorage.getItem('lp_user') || 'null'),
  veg: [], filter: {}, pending: null, orders: {}, openThread: null, poll: null, photoTarget: null,
};

// ---------- Helper ----------
const rp = n => 'Rp ' + Number(n || 0).toLocaleString('id-ID');
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const tgl = s => s ? new Date(s).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-';
const today = () => new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 10);
const clean = o => Object.fromEntries(Object.entries(o).filter(([, v]) => v !== ''));
const empty = t => `<div class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block"></i>${t}</div>`;
const ROLE = { petani: 'Petani', agen_desa: 'Agen Desa', pembeli: 'Pembeli', admin_mitra: 'Admin Mitra', admin: 'Admin' };
const LS = { draft: 'secondary', menunggu_konfirmasi_petani: 'info', tayang: 'success', habis: 'dark', kedaluwarsa: 'secondary', ditarik: 'secondary' };
const FRESH = { fresh: ['success', 'Fresh'], segera_diskon: ['warning', 'Segera Diskon'], diskon_kesegaran: ['danger', 'Diskon Kesegaran'], tidak_layak_jual: ['secondary', 'Tidak Layak Jual'] };
const STATUS = { menunggu_konfirmasi: 'Menunggu', dikonfirmasi: 'Dikonfirmasi', diproses: 'Diproses', dikirim: 'Dikirim', selesai: 'Selesai' };
const FLOW = Object.keys(STATUS);
const NEXT = { menunggu_konfirmasi: ['dikonfirmasi', 'Konfirmasi pesanan'], dikonfirmasi: ['diproses', 'Mulai proses'], diproses: ['dikirim', 'Tandai dikirim'] };

const fb = s => { const [c, t] = FRESH[s] || ['light', s]; return `<span class="badge text-bg-${c}">${t}</span>`; };
const gb = g => g ? `<span class="badge text-bg-${g === 'A' ? 'success' : g === 'B' ? 'primary' : 'dark'}">Grade ${g}</span>` : '';
const thumb = photos => {
  const p = (photos || []).find(x => x.is_primary) || (photos || [])[0];
  return p ? `<img class="thumb" src="${esc(p.url)}" alt="">` : `<div class="thumb ph"><i class="bi bi-image"></i></div>`;
};
const priceHtml = (base, price, disc) => disc > 0
  ? `<span class="text-decoration-line-through text-muted small">${rp(base)}</span> <strong class="text-danger">${rp(price)}</strong> <span class="badge text-bg-danger">-${disc}%</span>`
  : `<strong>${rp(base)}</strong>`;
const stepper = (st, cancelled) => cancelled
  ? '<span class="badge text-bg-danger">Dibatalkan</span>'
  : `<div class="d-flex flex-wrap gap-1">${FLOW.map((k, i) => `<span class="badge ${i <= FLOW.indexOf(st) ? 'text-bg-success' : 'text-bg-light border'}">${STATUS[k]}</span>`).join('')}</div>`;
const modal = (id, show = true) => bootstrap.Modal.getOrCreateInstance($('#' + id))[show ? 'show' : 'hide']();

let busy = 0;
async function api(path, o = {}) {
  const h = { Accept: 'application/json' };
  if (S.token) h.Authorization = 'Bearer ' + S.token;
  let body;
  if (o.form) body = o.form;
  else if (o.body) { h['Content-Type'] = 'application/json'; body = JSON.stringify(o.body); }
  $('#busy').classList.remove('d-none'); busy++;
  try {
    const res = await fetch(API + path, { method: o.method || 'GET', headers: h, body });
    if (res.status === 204) return null;
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
      if (res.status === 401 && S.token) logout(false);
      throw new Error(data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Terjadi kesalahan (' + res.status + ')'));
    }
    return data;
  } finally { if (--busy === 0) $('#busy').classList.add('d-none'); }
}

function toast(msg, type = 'success') {
  const el = document.createElement('div');
  el.className = `toast align-items-center text-bg-${type} border-0`;
  el.innerHTML = `<div class="d-flex"><div class="toast-body">${esc(msg)}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
  $('#toasts').append(el);
  new bootstrap.Toast(el, { delay: 4500 }).show();
  el.addEventListener('hidden.bs.toast', () => el.remove());
}
const run = async fn => { try { return await fn(); } catch (e) { toast(e.message, 'danger'); } };
const ensureVeg = async () => { if (!S.veg.length) S.veg = (await api('/jenis-sayur')).data; };
const stopPoll = () => { clearInterval(S.poll); S.poll = null; };

// ---------- Sesi ----------
function saveSession(token, user) {
  S.token = token; S.user = user;
  localStorage.setItem('lp_token', token);
  localStorage.setItem('lp_user', JSON.stringify(user));
}
function logout(call = true) {
  if (call && S.token) api('/auth/logout', { method: 'POST' }).catch(() => {});
  S.token = null; S.user = null; S.veg = [];
  localStorage.removeItem('lp_token'); localStorage.removeItem('lp_user');
  stopPoll(); boot();
}

// ---------- Kerangka halaman ----------
const MENU = {
  petani: [['listings', 'Tawaran Saya', 'bi-basket'], ['orders', 'Pesanan Masuk', 'bi-inbox'], ['chat', 'Chat', 'bi-chat-dots'], ['ratings', 'Rating', 'bi-star']],
  pembeli: [['catalog', 'Katalog', 'bi-shop'], ['cart', 'Keranjang', 'bi-cart'], ['myorders', 'Pesanan Saya', 'bi-receipt'], ['chat', 'Chat', 'bi-chat-dots']],
  admin_mitra: [['verif', 'Verifikasi Akun', 'bi-person-check'], ['orgs', 'Organisasi', 'bi-building']],
};
MENU.agen_desa = MENU.petani;
MENU.admin = MENU.admin_mitra;

const DEMO = [['Petani', '081200000003'], ['Agen Desa', '081200000004'], ['Pembeli', '081200000005'], ['Admin Mitra', '081200000002'], ['Admin', '081200000001']];

function boot() {
  if (!S.token || !S.user) return renderAuth();
  renderShell();
  go(MENU[S.user.role][0][0]);
}

function renderAuth() {
  $('#app').innerHTML = `
  <div class="container" style="max-width:460px">
    <div class="text-center my-4"><i class="bi bi-basket3-fill text-success" style="font-size:3rem"></i>
      <h3>LumbungPintar</h3><p class="text-muted mb-0">Pasar pangan terencana: demo</p></div>
    <div class="card shadow-sm"><div class="card-body">
      <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#tLogin">Masuk</button></li>
        <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tReg">Daftar</button></li>
      </ul>
      <div class="tab-content">
        <div class="tab-pane fade show active" id="tLogin">
          <form data-form="login">
            <input class="form-control mb-2" name="login" id="loginInput" placeholder="Nomor HP atau email" required>
            <input class="form-control mb-3" type="password" name="password" id="passInput" placeholder="Kata sandi" required>
            <button class="btn btn-success w-100">Masuk</button>
          </form>
          <hr><div class="small text-muted mb-1">Akun demo (kata sandi: password)</div>
          <div class="d-flex flex-wrap gap-1">${DEMO.map(([n, p]) => `<button type="button" class="btn btn-sm btn-outline-success" data-act="fill" data-phone="${p}">${n}</button>`).join('')}</div>
        </div>
        <div class="tab-pane fade" id="tReg">
          <form data-form="register" class="row g-2">
            <div class="col-12"><input class="form-control" name="name" placeholder="Nama lengkap" required></div>
            <div class="col-12"><input class="form-control" name="phone" placeholder="Nomor HP" required></div>
            <div class="col-6"><input class="form-control" type="password" name="password" placeholder="Kata sandi (min. 8)" required></div>
            <div class="col-6"><input class="form-control" type="password" name="password_confirmation" placeholder="Ulangi kata sandi" required></div>
            <div class="col-12"><select class="form-select" name="role"><option value="petani">Petani</option><option value="agen_desa">Agen desa</option><option value="pembeli">Pembeli</option></select></div>
            <div class="col-6"><input class="form-control" name="desa" placeholder="Desa"></div>
            <div class="col-6"><input class="form-control" name="kecamatan" placeholder="Kecamatan"></div>
            <div class="col-12"><button class="btn btn-success w-100">Daftar</button>
              <div class="form-text">Agen desa dan pembeli perlu diverifikasi admin mitra sebelum bisa memesan.</div></div>
          </form>
        </div>
      </div>
    </div></div>
  </div>`;
}

function renderShell() {
  const menu = MENU[S.user.role] || [];
  $('#app').innerHTML = `
  <nav class="navbar navbar-dark bg-success mb-3"><div class="container">
    <span class="navbar-brand"><i class="bi bi-basket3-fill me-2"></i>LumbungPintar <small class="opacity-75">demo</small></span>
    <div class="d-flex align-items-center gap-2 text-white">
      <span class="small"><i class="bi bi-person-circle"></i> ${esc(S.user.name)} · ${ROLE[S.user.role]}</span>
      <button class="btn btn-sm btn-light" data-act="logout">Keluar</button>
    </div></div></nav>
  <div class="container pb-5">
    <ul class="nav nav-pills mb-3 flex-wrap gap-1" id="nav">
      ${menu.map(([k, t, i]) => `<li class="nav-item"><a href="#" class="nav-link" data-act="go" data-tab="${k}"><i class="bi ${i} me-1"></i>${t}</a></li>`).join('')}
    </ul>
    <div id="content"></div>
  </div>
  ${modals()}`;
}

function modals() {
  return `
  <div class="modal fade" id="listingModal" tabindex="-1"><div class="modal-dialog"><form class="modal-content" data-form="listing">
    <div class="modal-header"><h5 class="modal-title">Tawaran baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body row g-2">
      <div class="col-12"><label class="form-label">Jenis sayur</label><select class="form-select" name="vegetable_type_id" id="vegSelect" required></select></div>
      <div class="col-12"><label class="form-label">Judul tawaran</label><input class="form-control" name="title" placeholder="mis. Sawi hijau segar" required></div>
      <div class="col-6"><label class="form-label">Jumlah</label><input class="form-control" type="number" step="0.01" min="0.01" name="quantity_available" required></div>
      <div class="col-6"><label class="form-label">Satuan</label><input class="form-control" name="unit" value="kg" required></div>
      <div class="col-6"><label class="form-label">Harga / satuan (Rp)</label><input class="form-control" type="number" min="0" name="base_price" required></div>
      <div class="col-6"><label class="form-label">Kategori</label><select class="form-select" name="category"><option value="ecer">Ecer</option><option value="non_ecer">Non-ecer (grosir)</option></select></div>
      <div class="col-6"><label class="form-label">Tanggal panen</label><input class="form-control" type="date" name="harvest_date" max="${today()}" value="${today()}" required></div>
      <div class="col-6"><label class="form-label">Penyimpanan</label><select class="form-select" name="storage_method"><option value="suhu_ruang">Suhu ruang</option><option value="dingin">Dingin</option><option value="lainnya">Lainnya</option></select></div>
      <div class="col-12"><label class="form-label">Grade (opsional)</label><select class="form-select" name="grade"><option value="">Ditentukan AI dari foto</option><option>A</option><option>B</option><option>C</option></select></div>
    </div>
    <div class="modal-footer"><button class="btn btn-success">Simpan draf</button></div>
  </form></div></div>

  <div class="modal fade" id="orderModal" tabindex="-1"><div class="modal-dialog"><form class="modal-content" data-form="order">
    <div class="modal-header"><h5 class="modal-title">Data pengiriman</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <p class="text-muted small" id="orderSummary"></p>
      <label class="form-label">Alamat pengiriman</label><textarea class="form-control mb-2" name="delivery_address" rows="2" required></textarea>
      <label class="form-label">Dibutuhkan tanggal</label><input class="form-control mb-2" type="date" name="needed_date" min="${today()}">
      <label class="form-label">Catatan</label><input class="form-control" name="notes" maxlength="1000">
    </div>
    <div class="modal-footer"><button class="btn btn-success">Buat pesanan</button></div>
  </form></div></div>

  <div class="modal fade" id="rateModal" tabindex="-1"><div class="modal-dialog"><form class="modal-content" data-form="rate">
    <div class="modal-header"><h5 class="modal-title">Beri penilaian</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <label class="form-label">Penjual</label><select class="form-select mb-2" name="seller_id" id="rateSeller" required></select>
      <label class="form-label">Nilai</label>
      <select class="form-select mb-2" name="score"><option value="5">5 - Sangat baik</option><option value="4">4 - Baik</option><option value="3">3 - Cukup</option><option value="2">2 - Kurang</option><option value="1">1 - Buruk</option></select>
      <label class="form-label">Komentar</label><textarea class="form-control" name="comment" rows="2" maxlength="1000"></textarea>
    </div>
    <div class="modal-footer"><button class="btn btn-success">Kirim</button></div>
  </form></div></div>`;
}

const VIEWS = {};
async function go(tab) {
  stopPoll();
  if (tab !== 'chat') S.openThread = null;
  document.querySelectorAll('#nav .nav-link').forEach(a => a.classList.toggle('active', a.dataset.tab === tab));
  $('#content').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-success"></div></div>';
  await run(() => VIEWS[tab]());
}

// ---------- Petani / agen desa ----------
VIEWS.listings = async () => {
  const r = await api('/petani/listings?per_page=50');
  $('#content').innerHTML = `
    <div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0">Tawaran Saya</h5>
      <button class="btn btn-success" data-act="newListing"><i class="bi bi-plus-lg"></i> Tawaran baru</button></div>
    ${r.data.length ? `<div class="row g-3">${r.data.map(l => {
      const p = (l.photos || []).find(x => x.is_primary) || (l.photos || [])[0];
      return `<div class="col-md-6 col-lg-4"><div class="card h-100 shadow-sm">
        ${thumb(l.photos)}
        <div class="card-body">
          <div class="d-flex justify-content-between gap-2"><h6 class="card-title">${esc(l.title)}</h6>
            <span class="badge text-bg-${LS[l.status] || 'secondary'} align-self-start">${esc(l.status.replaceAll('_', ' '))}</span></div>
          <div class="mb-2">${gb(l.grade)} ${fb(l.freshness_status)} <span class="badge text-bg-light border">${esc(l.category)}</span></div>
          <div>${priceHtml(l.base_price, l.effective_price, l.discount_percent)} <span class="text-muted small">/ ${esc(l.unit)}</span></div>
          <div class="small text-muted mt-1">Stok ${l.quantity_available} ${esc(l.unit)} · Panen ${tgl(l.harvest_date)} · Segar s.d. ${tgl(l.fresh_until)}</div>
          ${p && p.predicted_grade ? `<div class="small mt-1"><i class="bi bi-cpu"></i> AI: Grade ${p.predicted_grade} (${Math.round((p.grade_confidence || 0) * 100)}%)</div>` : ''}
        </div>
        <div class="card-footer bg-white d-flex flex-wrap gap-1">
          <button class="btn btn-sm btn-outline-secondary" data-act="photo" data-id="${l.id}"><i class="bi bi-camera"></i> Foto + AI</button>
          ${['draft', 'menunggu_konfirmasi_petani'].includes(l.status) ? `<button class="btn btn-sm btn-success" data-act="confirm" data-id="${l.id}">Konfirmasi tayang</button>` : ''}
          ${l.status === 'tayang' ? `<button class="btn btn-sm btn-outline-danger" data-act="withdraw" data-id="${l.id}">Tarik</button>` : ''}
        </div></div></div>`;
    }).join('')}</div>` : empty('Belum ada tawaran. Klik "Tawaran baru".')}`;
};

VIEWS.orders = async () => {
  const r = await api('/petani/orders?per_page=50');
  $('#content').innerHTML = `<h5 class="mb-3">Pesanan Masuk</h5>` + (r.data.length ? r.data.map(o => `
    <div class="card shadow-sm mb-3"><div class="card-body">
      <div class="d-flex justify-content-between flex-wrap gap-2">
        <div><strong>${esc(o.order_number)}</strong>
          <div class="small text-muted">Pembeli: ${esc(o.buyer && o.buyer.name)} · ${esc(o.delivery_address)}</div></div>
        ${stepper(o.status, o.cancelled)}
      </div>
      <ul class="list-unstyled small mt-2 mb-2">${o.items.map(i => `<li>${esc(i.title)}: ${i.quantity} ${esc(i.unit)} × ${rp(i.unit_price)}${i.discount_percent ? ` (diskon ${i.discount_percent}%)` : ''} = ${rp(i.subtotal)}</li>`).join('')}</ul>
      <div class="d-flex justify-content-between align-items-center"><strong>Bagian Anda: ${rp(o.my_subtotal)}</strong>
        ${!o.cancelled && NEXT[o.status] ? `<button class="btn btn-sm btn-success" data-act="advance" data-id="${o.id}" data-to="${NEXT[o.status][0]}">${NEXT[o.status][1]}</button>` : ''}
      </div></div></div>`).join('') : empty('Belum ada pesanan masuk.'));
};

VIEWS.ratings = async () => {
  const r = await api('/petani/ratings?per_page=50');
  const s = (r.meta && r.meta.summary) || { average: 0, count: 0 };
  $('#content').innerHTML = `<h5 class="mb-3">Rating Saya</h5>
    <div class="card shadow-sm mb-3"><div class="card-body"><span class="fs-2 fw-bold">${s.average}</span> / 5 <i class="bi bi-star-fill text-warning"></i>
      <span class="text-muted ms-2">${s.count} penilaian</span></div></div>
    ${r.data.length ? r.data.map(x => `<div class="card mb-2"><div class="card-body py-2">
      <strong>${'★'.repeat(x.score)}${'☆'.repeat(5 - x.score)}</strong> <span class="small text-muted">${esc(x.rater && x.rater.name)}</span>
      <div>${esc(x.comment || '')}</div></div></div>`).join('') : empty('Belum ada penilaian.')}`;
};

// ---------- Pembeli ----------
VIEWS.catalog = async () => {
  await ensureVeg();
  const qs = new URLSearchParams({ ...S.filter, per_page: 24 });
  const r = await api('/pembeli/listings?' + qs);
  const f = S.filter;
  $('#content').innerHTML = `
    <form class="row g-2 mb-3" data-form="filter">
      <div class="col-md-3"><select class="form-select" name="vegetable_type_id"><option value="">Semua jenis</option>
        ${S.veg.map(v => `<option value="${v.id}" ${String(f.vegetable_type_id) === String(v.id) ? 'selected' : ''}>${esc(v.name)}</option>`).join('')}</select></div>
      <div class="col-md-3"><select class="form-select" name="category"><option value="">Ecer &amp; non-ecer</option>
        <option value="ecer" ${f.category === 'ecer' ? 'selected' : ''}>Ecer</option><option value="non_ecer" ${f.category === 'non_ecer' ? 'selected' : ''}>Non-ecer</option></select></div>
      <div class="col-md-4"><input class="form-control" name="q" placeholder="Cari tawaran…" value="${esc(f.q || '')}"></div>
      <div class="col-md-2"><button class="btn btn-success w-100"><i class="bi bi-search"></i> Cari</button></div>
    </form>
    ${r.data.length ? `<div class="row g-3">${r.data.map(l => `
      <div class="col-md-6 col-lg-4"><div class="card h-100 shadow-sm">
        ${thumb(l.photos)}
        <div class="card-body">
          <h6 class="card-title">${esc(l.title)}</h6>
          <div class="mb-2">${gb(l.grade)} ${fb(l.freshness_status)} <span class="badge text-bg-light border">${esc(l.category)}</span></div>
          <div>${priceHtml(l.base_price, l.price, l.discount_percent)} <span class="text-muted small">/ ${esc(l.unit)}</span></div>
          <div class="small text-muted mt-1">Stok ${l.quantity_available} ${esc(l.unit)} · min. ${l.min_order_qty} · Segar s.d. ${tgl(l.fresh_until)}</div>
          <div class="small mt-1"><i class="bi bi-geo-alt"></i> ${esc(l.seller && l.seller.name)}${l.seller && l.seller.desa ? ', ' + esc(l.seller.desa) : ''}</div>
        </div>
        <div class="card-footer bg-white">
          <div class="input-group input-group-sm mb-2"><span class="input-group-text">Jumlah</span>
            <input type="number" class="form-control" id="q-${l.id}" min="${l.min_order_qty}" step="0.5" value="${l.min_order_qty}"><span class="input-group-text">${esc(l.unit)}</span></div>
          <div class="d-flex gap-1">
            <button class="btn btn-sm btn-success flex-fill" data-act="buy" data-id="${l.id}">Beli langsung</button>
            <button class="btn btn-sm btn-outline-success" data-act="addcart" data-id="${l.id}" title="Tambah ke keranjang"><i class="bi bi-cart-plus"></i></button>
            <button class="btn btn-sm btn-outline-secondary" data-act="chatseller" data-id="${l.id}" data-seller="${l.seller && l.seller.id}" title="Chat penjual"><i class="bi bi-chat-dots"></i></button>
          </div></div></div></div>`).join('')}</div>` : empty('Belum ada tawaran yang tayang.')}`;
};

VIEWS.cart = async () => {
  const r = await api('/pembeli/cart');
  $('#content').innerHTML = `<h5 class="mb-3">Keranjang</h5>` + (r.data.length ? `
    <div class="card shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0">
      <thead><tr><th>Barang</th><th style="width:130px">Jumlah</th><th>Harga</th><th class="text-end">Total</th><th></th></tr></thead>
      <tbody>${r.data.map(i => `<tr>
        <td>${esc(i.title)}<div class="small text-muted">${esc(i.seller && i.seller.name)} ${fb(i.freshness_status)}
          ${i.is_available ? '' : '<span class="badge text-bg-danger">Tidak tersedia</span>'}</div></td>
        <td><div class="input-group input-group-sm"><input type="number" class="form-control" min="0.01" step="0.5" value="${i.quantity}" data-cartqty="${i.listing_id}"><span class="input-group-text">${esc(i.unit)}</span></div></td>
        <td>${priceHtml(i.base_price, i.price, i.discount_percent)}</td>
        <td class="text-end">${rp(i.line_total)}</td>
        <td><button class="btn btn-sm btn-outline-danger" data-act="rmcart" data-id="${i.listing_id}"><i class="bi bi-trash"></i></button></td></tr>`).join('')}</tbody>
    </table></div>
    <div class="card-body d-flex justify-content-between align-items-center">
      <div><div class="text-muted small">Subtotal (belum termasuk biaya layanan)</div><div class="fs-5 fw-bold">${rp(r.meta.subtotal)}</div></div>
      <button class="btn btn-success" data-act="checkout"><i class="bi bi-bag-check"></i> Checkout</button></div></div>` : empty('Keranjang masih kosong.'));
};

VIEWS.myorders = async () => {
  const r = await api('/pembeli/orders?per_page=50');
  S.orders = Object.fromEntries(r.data.map(o => [o.id, o]));
  $('#content').innerHTML = `<h5 class="mb-3">Pesanan Saya</h5>` + (r.data.length ? r.data.map(o => {
    const pay = o.payment;
    return `<div class="card shadow-sm mb-3"><div class="card-body">
      <div class="d-flex justify-content-between flex-wrap gap-2"><div><strong>${esc(o.order_number)}</strong>
        <div class="small text-muted">${esc(o.delivery_address)}</div></div>${stepper(o.status, o.cancelled)}</div>
      <ul class="list-unstyled small mt-2 mb-2">${(o.items || []).map(i => `<li>${esc(i.title)}: ${i.quantity} ${esc(i.unit)} × ${rp(i.unit_price)}${i.discount_percent ? ` (diskon ${i.discount_percent}%)` : ''} = ${rp(i.subtotal)}${i.seller ? ` <span class="text-muted">· ${esc(i.seller.name)}</span>` : ''}</li>`).join('')}</ul>
      <div class="small text-muted">Subtotal ${rp(o.subtotal)} + biaya layanan ${rp(o.service_fee_amount)}</div>
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-1">
        <div><strong class="fs-5">${rp(o.total)}</strong>
          ${pay ? `<span class="badge text-bg-${pay.status === 'dibayar' ? 'success' : pay.status === 'gagal' ? 'danger' : 'warning'} ms-1">${esc(pay.status)}${pay.is_simulated ? ' (simulasi)' : ''}</span>` : ''}</div>
        <div class="d-flex flex-wrap gap-1 align-items-center">
          ${!o.cancelled && pay && pay.status === 'menunggu' ? `
            <select class="form-select form-select-sm w-auto" id="pm-${o.id}"><option value="simulasi">Simulasi</option><option value="transfer">Transfer</option><option value="qris">QRIS</option><option value="virtual_account">Virtual account</option><option value="cod">COD</option></select>
            <button class="btn btn-sm btn-success" data-act="pay" data-id="${o.id}">Bayar</button>` : ''}
          ${!o.cancelled && o.status === 'menunggu_konfirmasi' ? `<button class="btn btn-sm btn-outline-danger" data-act="cancel" data-id="${o.id}">Batalkan</button>` : ''}
          ${!o.cancelled && o.status === 'dikirim' ? `<button class="btn btn-sm btn-success" data-act="complete" data-id="${o.id}">Pesanan diterima</button>` : ''}
          ${!o.cancelled && o.status === 'selesai' ? `<button class="btn btn-sm btn-outline-warning" data-act="rate" data-id="${o.id}"><i class="bi bi-star"></i> Beri rating</button>` : ''}
        </div></div></div></div>`;
  }).join('') : empty('Belum ada pesanan.'));
};

// ---------- Chat (petani dan pembeli) ----------
VIEWS.chat = async () => {
  const t = await api('/chat/threads?per_page=50');
  $('#content').innerHTML = `<div class="row g-3">
    <div class="col-md-4"><div class="list-group shadow-sm">${t.data.length ? t.data.map(x => `
      <a href="#" class="list-group-item list-group-item-action ${S.openThread === x.id ? 'active' : ''}" data-act="thread" data-id="${x.id}">
        <strong>${esc(x.counterpart && x.counterpart.name)}</strong><div class="small">${esc((x.listing && x.listing.title) || 'Percakapan umum')}</div></a>`).join('')
      : '<div class="list-group-item text-muted">Belum ada percakapan. Pembeli bisa memulainya dari katalog.</div>'}</div></div>
    <div class="col-md-8"><div class="card shadow-sm" id="chatPane"><div class="card-body text-muted">Pilih percakapan di sebelah kiri.</div></div></div></div>`;
  if (S.openThread) await openThread(S.openThread);
};

async function openThread(id) {
  S.openThread = id; stopPoll();
  $('#chatPane').innerHTML = `<div class="card-header" id="chatHead">&nbsp;</div>
    <div class="card-body"><div id="msgs"></div></div>
    <div class="card-footer"><form class="d-flex gap-2" data-form="chat">
      <input class="form-control" name="body" placeholder="Tulis pesan…" autocomplete="off" maxlength="2000" required>
      <button class="btn btn-success"><i class="bi bi-send"></i></button></form></div>`;
  await loadMsgs();
  S.poll = setInterval(() => loadMsgs().catch(() => {}), 5000);
}

async function loadMsgs() {
  if (!S.openThread || !$('#msgs')) return stopPoll();
  const r = await api(`/chat/threads/${S.openThread}/messages?per_page=100`);
  const box = $('#msgs');
  box.innerHTML = r.data.slice().reverse().map(m => `<div class="bubble ${m.is_mine ? 'me' : 'them'}">${esc(m.body)}</div>`).join('')
    || '<div class="text-muted small">Belum ada pesan.</div>';
  box.scrollTop = box.scrollHeight;
  const th = r.meta && r.meta.thread;
  if (th && $('#chatHead')) $('#chatHead').innerHTML = `<strong>${esc(th.counterpart && th.counterpart.name)}</strong> <span class="text-muted small">${esc((th.listing && th.listing.title) || '')}</span>`;
}

// ---------- Admin mitra / admin ----------
VIEWS.verif = async () => {
  const r = await api('/admin-mitra/verifikasi?per_page=50');
  $('#content').innerHTML = `<h5 class="mb-3">Akun menunggu verifikasi</h5>` + (r.data.length ? `
    <div class="card shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0">
      <thead><tr><th>Nama</th><th>Peran</th><th>Wilayah</th><th></th></tr></thead>
      <tbody>${r.data.map(u => `<tr><td>${esc(u.name)}</td><td><span class="badge text-bg-secondary">${ROLE[u.role] || esc(u.role)}</span></td>
        <td>${esc([u.desa, u.kecamatan].filter(Boolean).join(', ') || '-')}</td>
        <td class="text-end"><button class="btn btn-sm btn-success" data-act="approve" data-id="${u.id}">Verifikasi</button></td></tr>`).join('')}</tbody>
    </table></div></div>` : empty('Tidak ada akun yang menunggu verifikasi.'));
};

VIEWS.orgs = async () => {
  const r = await api('/admin-mitra/organisasi?per_page=50');
  $('#content').innerHTML = `
    <div class="card shadow-sm mb-3"><div class="card-body"><h6>Daftarkan organisasi</h6>
      <form class="row g-2" data-form="org">
        <div class="col-md-4"><input class="form-control" name="name" placeholder="Nama organisasi" required></div>
        <div class="col-md-3"><select class="form-select" name="type"><option value="sppg">SPPG</option><option value="katering">Katering</option><option value="koperasi_kdmp">Koperasi/KDMP</option><option value="umkm_olahan">UMKM olahan</option><option value="kelompok_tani">Kelompok tani</option></select></div>
        <div class="col-md-2"><input class="form-control" name="desa" placeholder="Desa"></div>
        <div class="col-md-2"><input class="form-control" name="kecamatan" placeholder="Kecamatan"></div>
        <div class="col-md-1"><button class="btn btn-success w-100"><i class="bi bi-plus-lg"></i></button></div>
      </form></div></div>
    ${r.data.length ? `<div class="card shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0">
      <thead><tr><th>ID</th><th>Nama</th><th>Jenis</th><th>Status</th><th></th></tr></thead>
      <tbody>${r.data.map(o => `<tr><td>${o.id}</td><td>${esc(o.name)}</td><td>${esc(o.type)}</td>
        <td>${o.is_verified ? '<span class="badge text-bg-success">Terverifikasi</span>' : '<span class="badge text-bg-warning">Belum</span>'}</td>
        <td class="text-end">${o.is_verified ? '' : `<button class="btn btn-sm btn-success" data-act="verifyorg" data-id="${o.id}">Verifikasi</button>`}
          <button class="btn btn-sm btn-outline-secondary" data-act="attach" data-id="${o.id}">Tautkan akun</button></td></tr>`).join('')}</tbody>
    </table></div></div>` : empty('Belum ada organisasi.')}`;
};

// ---------- Aksi (klik) ----------
const ACT = {
  logout: () => logout(true),
  go: b => go(b.dataset.tab),
  fill: b => { $('#loginInput').value = b.dataset.phone; $('#passInput').value = 'password'; },

  async newListing() {
    await ensureVeg();
    $('#vegSelect').innerHTML = S.veg.map(v => `<option value="${v.id}">${esc(v.name)} (segar ${v.fresh_days} hari)</option>`).join('');
    modal('listingModal');
  },
  photo: b => { S.photoTarget = b.dataset.id; $('#photoInput').click(); },
  async confirm(b) { await api(`/petani/listings/${b.dataset.id}/confirm`, { method: 'POST' }); toast('Tawaran sekarang tayang di katalog.'); go('listings'); },
  async withdraw(b) { if (!confirm('Tarik tawaran ini dari katalog?')) return; await api(`/petani/listings/${b.dataset.id}/withdraw`, { method: 'POST' }); go('listings'); },
  async advance(b) { await api(`/petani/orders/${b.dataset.id}/status`, { method: 'PATCH', body: { status: b.dataset.to } }); toast('Status pesanan diperbarui.'); go('orders'); },

  buy(b) {
    const qty = parseFloat($('#q-' + b.dataset.id).value);
    if (!(qty > 0)) return toast('Isi jumlah yang valid.', 'warning');
    S.pending = { mode: 'direct', listing_id: Number(b.dataset.id), quantity: qty };
    $('#orderSummary').textContent = `Beli langsung ${qty} unit dari tawaran #${b.dataset.id}.`;
    modal('orderModal');
  },
  async addcart(b) {
    const qty = parseFloat($('#q-' + b.dataset.id).value);
    if (!(qty > 0)) return toast('Isi jumlah yang valid.', 'warning');
    await api('/pembeli/cart', { method: 'POST', body: { listing_id: Number(b.dataset.id), quantity: qty } });
    toast('Ditambahkan ke keranjang.');
  },
  async chatseller(b) {
    const r = await api('/chat/threads', { method: 'POST', body: { seller_id: Number(b.dataset.seller), listing_id: Number(b.dataset.id) } });
    S.openThread = r.data.id; go('chat');
  },
  async rmcart(b) { await api(`/pembeli/cart/${b.dataset.id}`, { method: 'DELETE' }); go('cart'); },
  checkout() { S.pending = { mode: 'cart' }; $('#orderSummary').textContent = 'Semua isi keranjang akan dijadikan satu pesanan.'; modal('orderModal'); },
  async pay(b) {
    await api(`/pembeli/orders/${b.dataset.id}/pay`, { method: 'POST', body: { method: $('#pm-' + b.dataset.id).value } });
    toast('Pembayaran tercatat (simulasi, tidak ada dana yang berpindah).'); go('myorders');
  },
  async cancel(b) {
    if (!confirm('Batalkan pesanan ini? Stok akan dikembalikan.')) return;
    await api(`/pembeli/orders/${b.dataset.id}/cancel`, { method: 'POST', body: { cancel_reason: 'Dibatalkan pembeli' } });
    go('myorders');
  },
  async complete(b) { await api(`/pembeli/orders/${b.dataset.id}/complete`, { method: 'POST' }); toast('Pesanan selesai.'); go('myorders'); },
  rate(b) {
    S.pending = { orderId: b.dataset.id };
    const sellers = new Map();
    ((S.orders[b.dataset.id] || {}).items || []).forEach(i => i.seller && sellers.set(i.seller.id, i.seller.name));
    $('#rateSeller').innerHTML = [...sellers].map(([id, n]) => `<option value="${id}">${esc(n)}</option>`).join('');
    modal('rateModal');
  },

  thread: b => openThread(Number(b.dataset.id)),
  async approve(b) { await api(`/admin-mitra/verifikasi/${b.dataset.id}/approve`, { method: 'POST' }); toast('Akun diverifikasi.'); go('verif'); },
  async verifyorg(b) { await api(`/admin-mitra/organisasi/${b.dataset.id}/verify`, { method: 'POST' }); go('orgs'); },
  async attach(b) {
    const uid = prompt('ID akun pengguna yang ditautkan ke organisasi ini:');
    if (!uid) return;
    await api(`/admin-mitra/organisasi/${b.dataset.id}/anggota`, { method: 'POST', body: { user_id: Number(uid) } });
    toast('Akun ditautkan.');
  },
};

// ---------- Form ----------
const FORMS = {
  async login(f) {
    const r = await api('/auth/login', { method: 'POST', body: Object.fromEntries(new FormData(f)) });
    saveSession(r.token, r.user); boot();
  },
  async register(f) {
    const r = await api('/auth/register', { method: 'POST', body: clean(Object.fromEntries(new FormData(f))) });
    saveSession(r.token, r.user); boot();
    if (r.user.role !== 'petani') toast('Akun dibuat. Menunggu verifikasi admin mitra sebelum bisa memesan.', 'warning');
  },
  async listing(f) {
    await api('/petani/listings', { method: 'POST', body: clean(Object.fromEntries(new FormData(f))) });
    modal('listingModal', false); f.reset();
    toast('Draf dibuat. Unggah foto untuk penilaian AI, lalu konfirmasi agar tayang.');
    go('listings');
  },
  filter(f) { S.filter = clean(Object.fromEntries(new FormData(f))); go('catalog'); },
  async order(f) {
    const meta = clean(Object.fromEntries(new FormData(f)));
    const p = S.pending;
    if (p.mode === 'direct') await api('/pembeli/orders/direct', { method: 'POST', body: { listing_id: p.listing_id, quantity: p.quantity, ...meta } });
    else await api('/pembeli/orders/checkout', { method: 'POST', body: meta });
    modal('orderModal', false); f.reset();
    toast('Pesanan dibuat. Lanjutkan ke pembayaran.');
    go('myorders');
  },
  async rate(f) {
    await api(`/pembeli/orders/${S.pending.orderId}/rating`, { method: 'POST', body: clean(Object.fromEntries(new FormData(f))) });
    modal('rateModal', false); f.reset(); toast('Terima kasih atas penilaiannya.');
  },
  async chat(f) {
    const body = new FormData(f).get('body');
    await api(`/chat/threads/${S.openThread}/messages`, { method: 'POST', body: { body } });
    f.reset(); await loadMsgs();
  },
  async org(f) {
    await api('/admin-mitra/organisasi', { method: 'POST', body: clean(Object.fromEntries(new FormData(f))) });
    toast('Organisasi ditambahkan.'); go('orgs');
  },
};

// ---------- Pendengar peristiwa ----------
document.addEventListener('click', e => {
  const b = e.target.closest('[data-act]');
  if (!b) return;
  e.preventDefault();
  if (ACT[b.dataset.act]) run(() => ACT[b.dataset.act](b));
});
document.addEventListener('submit', e => {
  const f = e.target.closest('[data-form]');
  if (!f) return;
  e.preventDefault();
  if (FORMS[f.dataset.form]) run(() => FORMS[f.dataset.form](f));
});
document.addEventListener('change', e => {
  const t = e.target;
  if (t.id === 'photoInput' && t.files[0]) {
    const fd = new FormData(); fd.append('photo', t.files[0]); t.value = '';
    toast('Mengunggah foto dan menilai dengan AI…', 'info');
    run(async () => {
      const r = await api(`/petani/listings/${S.photoTarget}/photos`, { method: 'POST', form: fd });
      const a = r.data.ai;
      if (a.grade) toast(`Penilaian AI (${a.status}): Grade ${a.grade}, keyakinan ${Math.round((a.grade_confidence || 0) * 100)}%${a.is_sellable ? '' : ', TIDAK layak jual'}.`, a.is_sellable ? 'success' : 'warning');
      else toast('Foto tersimpan, tetapi AI tidak tersedia. Tentukan grade secara manual.', 'warning');
      go('listings');
    });
  }
  if (t.dataset && t.dataset.cartqty) {
    run(async () => { await api(`/pembeli/cart/${t.dataset.cartqty}`, { method: 'PATCH', body: { quantity: parseFloat(t.value) } }); go('cart'); });
  }
});

boot();
})();
</script>
</body>
</html>