@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="dash-wrapper">

    {{-- ======= HEADER ======= --}}
    <div class="dash-topbar mb-4">
        <div>
            <span class="dash-eyebrow" id="dashEyebrow">LumbungPintar</span>
            <h1 class="dash-headline">Dashboard</h1>
        </div>
        <div class="dash-meta">
            <div class="meta-chip">
                <span class="meta-dot pulse-dot"></span>
                <span id="currentDate" class="meta-text"></span>
            </div>
        </div>
    </div>

    <div id="alertBox"></div>

    {{-- ======= KARTU STATISTIK ======= --}}
    <div class="row mb-4" id="statCards">
        @for ($i = 0; $i < 4; $i++)
        <div class="col-12 col-sm-6 col-xl-3 mb-4">
            <div class="stat-card h-100 py-3">
                <div class="card-body">
                    <div class="sk sk-line sk-sm mb-3"></div>
                    <div class="sk sk-line sk-lg" style="height:26px"></div>
                </div>
            </div>
        </div>
        @endfor
    </div>

    {{-- ======= GRAFIK + PESANAN TERBARU ======= --}}
    <div class="row">

        <div class="col-12 col-md-8 mb-4">
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <p class="panel-eyebrow">Statistik</p>
                        <h2 class="panel-title">Pesanan 7 Hari Terakhir</h2>
                    </div>
                    <span class="panel-badge">Live</span>
                </div>
                <div class="panel-body">
                    <div class="chart-wrap"><canvas id="myAreaChart"></canvas></div>
                    <div class="status-chips" id="statusChips"></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4 mb-4">
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <p class="panel-eyebrow">Transaksi</p>
                        <h2 class="panel-title">Pesanan Terbaru</h2>
                    </div>
                    <span class="panel-badge panel-badge-green">Terbaru</span>
                </div>
                <div id="recentList">
                    @for ($i = 0; $i < 5; $i++)
                    <div class="ord-row">
                        <div class="sk sk-num"></div>
                        <div class="sk sk-icon"></div>
                        <div class="flex-grow-1">
                            <div class="sk sk-line sk-lg mb-1"></div>
                            <div class="sk sk-line sk-sm"></div>
                        </div>
                    </div>
                    @endfor
                </div>
            </div>
        </div>

    </div>
</div>

<style>
/* --- Base ---------------------------------------- */
:root {
    --c-green: #1cc88a; --c-amber: #f6c23e; --c-blue: #4e73df; --c-cyan: #36b9cc;
    --surface: #ffffff; --surface2: #f7f8fc; --line: #eef0f6;
    --text-1: #1a1d2e; --text-2: #6b7280; --text-3: #b0b5c8;
    --r-xl: 20px; --r-md: 10px;
}
body, .card, .card-body, h1,h2,h3,h4,h5,h6, p,span,div,a,li,td,th,button,input,select,textarea {
    font-family: 'Sora', sans-serif !important;
}
.dash-wrapper { padding: 6px 2px 40px; }

/* --- Header -------------------------------------- */
.dash-topbar { display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
.dash-eyebrow {
    display: block; font-size: .67rem; font-weight: 700; letter-spacing: 2px;
    text-transform: uppercase; color: var(--text-3); margin-bottom: 5px;
}
.dash-headline { font-size: 2rem; font-weight: 800; color: var(--text-1); letter-spacing: -.8px; margin: 0; line-height: 1; }
.meta-chip {
    display: inline-flex; align-items: center; gap: 8px; background: var(--surface);
    border: 1px solid var(--line); border-radius: 50px; padding: 8px 18px;
}
.meta-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--c-green); flex-shrink: 0; }
.pulse-dot { box-shadow: 0 0 0 0 rgba(28,200,138,.4); animation: kpulse 2s infinite; }
@keyframes kpulse {
    0%   { box-shadow: 0 0 0 0 rgba(28,200,138,.4); }
    70%  { box-shadow: 0 0 0 8px rgba(28,200,138,0); }
    100% { box-shadow: 0 0 0 0 rgba(28,200,138,0); }
}
.meta-text { font-size: .77rem; font-weight: 600; color: var(--text-2); white-space: nowrap; }

/* --- Stat cards ---------------------------------- */
.stat-card {
    background: var(--surface); border: none; border-radius: 16px;
    box-shadow: 0 5px 20px rgba(0,0,0,.04); transition: transform .2s ease, box-shadow .2s ease;
}
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,.07); }
.stat-card .card-body { padding: 1.25rem 1.5rem; }
.stat-label {
    font-size: .72rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .5px; color: var(--text-2); margin-bottom: 6px;
}
.stat-value { font-size: 1.6rem; font-weight: 700; color: var(--text-1); line-height: 1.1; min-height: 30px; }
.stat-icon-wrap {
    width: 52px; height: 52px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}

/* --- Panels -------------------------------------- */
.panel { background: var(--surface); border: 1px solid var(--line); border-radius: var(--r-xl); overflow: hidden; height: 100%; }
.panel-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 20px 14px; border-bottom: 1px solid var(--line);
}
.panel-eyebrow {
    font-size: .64rem; font-weight: 700; letter-spacing: 2px;
    text-transform: uppercase; color: var(--text-3); margin: 0 0 2px;
}
.panel-title { font-size: .95rem; font-weight: 700; color: var(--text-1); margin: 0; letter-spacing: -.3px; }
.panel-badge {
    font-size: .64rem; font-weight: 700; letter-spacing: .8px; text-transform: uppercase;
    padding: 5px 13px; border-radius: 50px; white-space: nowrap;
    background: rgba(78,115,223,.08); color: var(--c-blue); border: 1px solid rgba(78,115,223,.14);
}
.panel-badge-green { background: rgba(28,200,138,.08); color: var(--c-green); border-color: rgba(28,200,138,.14); }
.panel-body { padding: 18px 20px; }
.chart-wrap { position: relative; height: 250px; }

/* Status pesanan di bawah grafik */
.status-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
.chip {
    display: inline-flex; align-items: center; gap: 6px; font-size: .7rem; font-weight: 600;
    color: var(--text-2); background: var(--surface2); border: 1px solid var(--line);
    border-radius: 50px; padding: 5px 12px;
}
.chip i { font-size: .45rem; }
.chip b { color: var(--text-1); font-family: 'JetBrains Mono', monospace !important; }

/* --- Daftar pesanan terbaru ---------------------- */
.ord-row {
    display: flex; align-items: center; padding: 12px 20px;
    border-bottom: 1px solid var(--line); transition: background .18s;
}
.ord-row:last-child { border-bottom: none; }
.ord-row:hover { background: var(--surface2); }
.ord-num {
    font-family: 'JetBrains Mono', monospace !important; font-size: .63rem; font-weight: 600;
    color: var(--text-3); width: 22px; flex-shrink: 0; margin-right: 12px;
}
.ord-icon {
    width: 34px; height: 34px; border-radius: var(--r-md); background: rgba(28,200,138,.08);
    border: 1px solid rgba(28,200,138,.14); display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; margin-right: 12px; font-size: .8rem; color: var(--c-green);
}
.ord-title {
    font-size: .8rem; font-weight: 700; color: var(--text-1); white-space: nowrap;
    overflow: hidden; text-overflow: ellipsis; display: block; line-height: 1.3;
}
.ord-sub { font-size: .67rem; font-weight: 500; color: var(--text-3); display: block; margin-top: 2px; }
.ord-amount {
    font-family: 'JetBrains Mono', monospace !important; font-size: .68rem; font-weight: 600;
    color: var(--text-2); margin-left: 8px; white-space: nowrap;
}
.ord-empty { padding: 28px 20px; text-align: center; font-size: .8rem; color: var(--text-3); }

/* --- Skeleton ------------------------------------ */
@keyframes skshim { from { background-position: -600px 0; } to { background-position: 600px 0; } }
.sk {
    border-radius: 6px; background: linear-gradient(90deg,#eef0f7 25%,#f5f6fc 50%,#eef0f7 75%);
    background-size: 600px 100%; animation: skshim 1.4s infinite linear;
}
.sk-num { width: 18px; height: 10px; flex-shrink: 0; margin-right: 12px; }
.sk-icon { width: 34px !important; height: 34px !important; border-radius: 10px; flex-shrink: 0; margin-right: 12px; }
.sk-line { height: 9px; }
.sk-lg { width: 70%; }
.sk-sm { width: 42%; }

/* --- Animasi ------------------------------------- */
@keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
.dash-topbar { animation: fadeUp .4s ease both; }
.panel { animation: fadeUp .4s .32s ease both; }

/* --- Responsif: tablet --------------------------- */
@media (min-width: 768px) and (max-width: 991.98px) {
    .dash-headline { font-size: 1.6rem; }
    .stat-value { font-size: 1.4rem; }
    .stat-card .card-body { padding: 1rem 1.25rem; }
    .stat-icon-wrap { width: 42px; height: 42px; }
    .chart-wrap { height: 220px; }
    .panel-head { padding: 14px 16px 12px; }
    .panel-body { padding: 14px 16px; }
    .ord-row { padding: 10px 14px; }
}

/* --- Responsif: mobile (layout kolom sama, ukuran dikecilkan) --- */
@media (max-width: 767.98px) {
    .dash-wrapper { padding: 4px 0 24px; }
    .dash-topbar { gap: 8px; margin-bottom: 0.75rem !important; }
    .dash-eyebrow { font-size: .58rem; letter-spacing: 1.5px; }
    .dash-headline { font-size: 1.25rem; letter-spacing: -.5px; }
    .meta-chip { padding: 5px 10px; gap: 6px; }
    .meta-text { font-size: .65rem; }
    .stat-card .card-body { padding: 1rem 1.1rem; }
    .stat-label { font-size: .62rem; }
    .stat-value { font-size: 1.3rem; }
    .stat-icon-wrap { width: 42px; height: 42px; border-radius: 12px; }
    .panel { border-radius: 12px; }
    .panel-head { padding: 12px 14px 10px; flex-wrap: wrap; gap: 6px; }
    .panel-title { font-size: .82rem; }
    .panel-body { padding: 12px 14px; }
    .chart-wrap { height: 200px; }
    .ord-row { padding: 10px 14px; }
}

@media (max-width: 419.98px) {
    .dash-headline { font-size: 1.05rem; }
    .stat-value { font-size: 1.15rem; }
    .stat-icon-wrap { width: 36px; height: 36px; }
    .chart-wrap { height: 170px; }
    .ord-icon, .ord-num { display: none; }
}
</style>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(async function () {
    const ROLE_LABEL = { petani: 'Petani', agen_desa: 'Agen Desa', pembeli: 'Pembeli', admin_mitra: 'Admin Mitra', admin: 'Admin' };
    const COLORS = { success: '#1cc88a', warning: '#f6c23e', primary: '#4e73df', info: '#36b9cc' };
    // [label, warna]
    const ST = {
        menunggu_konfirmasi: ['Menunggu', '#f6c23e'],
        dikonfirmasi: ['Dikonfirmasi', '#36b9cc'],
        diproses: ['Diproses', '#4e73df'],
        dikirim: ['Dikirim', '#858796'],
        selesai: ['Selesai', '#1cc88a'],
    };

    document.getElementById('currentDate').textContent = new Date().toLocaleDateString('id-ID', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
    });
    document.getElementById('dashEyebrow').textContent = 'LumbungPintar · ' + (ROLE_LABEL[LP.user.role] || '');

    const cardValue = c => c.type === 'currency'
        ? LP.rp(c.value)
        : Number(c.value).toLocaleString('id-ID') + (c.suffix || '');

    try {
        const { data } = await LP.api('/dashboard');

        if (!data.verified) {
            $('#alertBox').html(
                '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle mr-1"></i>' +
                'Akun Anda belum diverifikasi admin mitra. Beberapa fitur (misalnya memesan) baru aktif setelah diverifikasi.</div>'
            );
        }

        // 1. Kartu statistik
        $('#statCards').html(data.cards.map(c => {
            const col = COLORS[c.color] || '#4e73df';
            return `
            <div class="col-12 col-sm-6 col-xl-3 mb-4">
                <div class="stat-card h-100 py-3" style="border-left:4px solid ${col};">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="stat-label">${LP.esc(c.label)}</div>
                                <div class="stat-value">${LP.esc(cardValue(c))}</div>
                            </div>
                            <div class="col-auto">
                                <div class="stat-icon-wrap" style="background:${col}1a;">
                                    <i class="fas ${LP.esc(c.icon)} fa-2x" style="color:${col};"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
        }).join(''));

        // 2. Grafik area: pesanan per hari
        const ctx = document.getElementById('myAreaChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.charts.per_day.labels,
                datasets: [{
                    label: 'Pesanan',
                    tension: 0.45,
                    fill: true,
                    backgroundColor: function (c) {
                        const g = c.chart.ctx.createLinearGradient(0, 0, 0, 270);
                        g.addColorStop(0, 'rgba(28,200,138,0.18)');
                        g.addColorStop(1, 'rgba(28,200,138,0)');
                        return g;
                    },
                    borderColor: 'rgba(28,200,138,1)',
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: 'rgba(28,200,138,1)',
                    pointBorderWidth: 2.5,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: 'rgba(28,200,138,1)',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                    pointHitRadius: 14,
                    data: data.charts.per_day.values,
                }],
            },
            options: {
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0, color: '#b0b5c8', font: { family: 'Sora', size: 10, weight: '600' }, padding: 6, maxTicksLimit: 6 },
                        grid: { color: '#f1f3f9' },
                        border: { display: false },
                    },
                    x: {
                        ticks: { color: '#b0b5c8', font: { family: 'Sora', size: 10, weight: '600' }, padding: 4, maxRotation: 0 },
                        grid: { display: false },
                        border: { display: false },
                    },
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1a1d2e', titleColor: '#ffffff', bodyColor: 'rgba(255,255,255,0.6)',
                        padding: { x: 14, y: 10 }, cornerRadius: 10, displayColors: false,
                        titleFont: { family: 'Sora', weight: '700', size: 11 },
                        bodyFont: { family: 'JetBrains Mono', size: 11 },
                        callbacks: { label: c => '  ' + c.parsed.y + ' pesanan' },
                    },
                },
            },
        });

        // 3. Ringkasan status
        $('#statusChips').html(data.charts.status.keys.map((k, i) =>
            `<span class="chip"><i class="fas fa-circle" style="color:${ST[k][1]}"></i> ${ST[k][0]} <b>${data.charts.status.values[i]}</b></span>`
        ).join(''));

        // 4. Pesanan terbaru
        $('#recentList').html(data.recent.length ? data.recent.map((o, i) => `
            <div class="ord-row">
                <span class="ord-num">${String(i + 1).padStart(2, '0')}</span>
                <div class="ord-icon"><i class="fas fa-receipt"></i></div>
                <div class="overflow-hidden flex-grow-1">
                    <span class="ord-title">${LP.esc(o.order_number)}</span>
                    <span class="ord-sub">${LP.esc(o.party)} · ${o.cancelled ? 'Dibatalkan' : ST[o.status][0]}</span>
                </div>
                <span class="ord-amount">${LP.rp(o.amount)}</span>
            </div>`).join('')
            : '<div class="ord-empty">Belum ada pesanan.</div>');

    } catch (e) {
        $('#statCards').empty();
        $('#recentList').html('<div class="ord-empty">Gagal memuat data.</div>');
        $('#alertBox').html('<div class="alert alert-danger">' + LP.esc(e.message) + '</div>');
    }
})();
</script>
@endsection