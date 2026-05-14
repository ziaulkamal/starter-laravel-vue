<template>
    <AppLayout>

        <!-- ── Row 0: Stat Cards ── -->
        <div class="row g-3 mb-4">
            <div v-for="stat in stats" :key="stat.label" class="col-6 col-lg">
                <div class="card card-hover h-100" :class="`bg-${stat.color}-subtle shadow-none border-0`">
                    <div class="card-body text-center py-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                            :class="`bg-${stat.color}-subtle`" style="width:52px;height:52px;">
                            <i :class="`ti ${stat.icon} fs-6 text-${stat.color}`"></i>
                        </div>
                        <p class="fw-semibold fs-3 mb-1" :class="`text-${stat.color}`">{{ stat.label }}</p>
                        <h5 class="fw-semibold mb-1" :class="`text-${stat.color}`">{{ stat.value }}</h5>
                        <div class="d-flex align-items-center justify-content-center gap-1">
                            <span class="round-20 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                :class="stat.up ? 'bg-success-subtle' : 'bg-danger-subtle'">
                                <i :class="`ti ${stat.up ? 'ti-arrow-up-left text-success' : 'ti-arrow-down-right text-danger'} fs-2`"></i>
                            </span>
                            <span class="fs-2 text-dark">{{ stat.trend }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Row 1: Main Chart + Breakup + Mini Earning ── -->
        <div class="row g-3 mb-4">

            <!-- Pertumbuhan Pelanggan -->
            <div class="col-lg-8 d-flex align-items-stretch">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="d-sm-flex d-block align-items-center justify-content-between mb-9">
                            <div class="mb-3 mb-sm-0">
                                <h4 class="card-title fw-semibold">Pertumbuhan Pelanggan</h4>
                                <p class="card-subtitle mb-0">Ringkasan Jan – Jun 2026</p>
                            </div>
                            <select v-model="chartYear" class="form-select w-auto">
                                <option>2026</option>
                                <option>2025</option>
                            </select>
                        </div>
                        <div class="row align-items-center">
                            <!-- Bar Chart SVG -->
                            <div class="col-md-8">
                                <div style="height:200px;">
                                    <svg viewBox="0 0 560 180" preserveAspectRatio="xMidYMid meet" class="w-100 h-100">
                                        <line v-for="(y,i) in [20,60,100,140]" :key="i" x1="0" :y1="y" x2="560" :y2="y" stroke="var(--bs-border-color)" stroke-width="1"/>
                                        <g v-for="b in chartBars" :key="b.label">
                                            <rect :x="b.x" :y="b.y" :width="b.w" :height="b.h" fill="var(--bs-primary)" rx="5"/>
                                            <rect :x="b.x+b.w+4" :y="b.y2" :width="b.w" :height="b.h2" fill="var(--bs-secondary)" rx="5" opacity="0.5"/>
                                            <text :x="b.x+b.w+2" y="175" text-anchor="middle" font-size="11" fill="var(--bs-secondary-color)">{{ b.label }}</text>
                                        </g>
                                    </svg>
                                </div>
                            </div>
                            <!-- Side Summary -->
                            <div class="col-md-4">
                                <div class="hstack mb-4 pb-1">
                                    <div class="p-8 bg-primary-subtle rounded me-3 d-flex align-items-center justify-content-center flex-shrink-0">
                                        <i class="ti ti-users text-primary fs-6"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-0 fs-7 fw-semibold">3,248</h4>
                                        <p class="fs-3 mb-0">Total Pelanggan</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-baseline mb-4">
                                    <span class="round-8 text-bg-primary rounded-circle me-2 flex-shrink-0"></span>
                                    <div>
                                        <p class="fs-3 mb-1">Pelanggan baru bulan ini</p>
                                        <h6 class="fs-5 fw-semibold mb-0">148</h6>
                                    </div>
                                </div>
                                <div class="d-flex align-items-baseline mb-4 pb-1">
                                    <span class="round-8 bg-secondary rounded-circle me-2 flex-shrink-0"></span>
                                    <div>
                                        <p class="fs-3 mb-1">Pelanggan churn bulan ini</p>
                                        <h6 class="fs-5 fw-semibold mb-0">31</h6>
                                    </div>
                                </div>
                                <button class="btn btn-primary w-100">Lihat Laporan Lengkap</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Segmen + Mini Card -->
            <div class="col-lg-4 d-flex align-items-stretch flex-column gap-3">

                <!-- Segmen Tahunan -->
                <div class="card w-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h4 class="card-title mb-9 fw-semibold">Segmen Pelanggan</h4>
                                <h4 class="fw-semibold mb-3">2,890 Aktif</h4>
                                <div class="d-flex align-items-center mb-3">
                                    <span class="me-2 rounded-circle bg-success-subtle round-20 d-flex align-items-center justify-content-center flex-shrink-0">
                                        <i class="ti ti-arrow-up-left text-success"></i>
                                    </span>
                                    <p class="text-dark me-1 fs-3 mb-0">+8.5%</p>
                                    <p class="fs-3 mb-0">dari tahun lalu</p>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="round-8 text-bg-primary rounded-circle"></span>
                                        <span class="fs-2">Premium</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="round-8 bg-secondary rounded-circle"></span>
                                        <span class="fs-2">Standard</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4 d-flex justify-content-center">
                                <svg viewBox="0 0 100 100" style="width:90px;height:90px;">
                                    <circle cx="50" cy="50" r="38" fill="none" stroke="var(--bs-border-color)" stroke-width="14"/>
                                    <circle cx="50" cy="50" r="38" fill="none" stroke="var(--bs-primary)" stroke-width="14"
                                        stroke-dasharray="95 144" stroke-dashoffset="0" transform="rotate(-90 50 50)"/>
                                    <circle cx="50" cy="50" r="38" fill="none" stroke="var(--bs-secondary)" stroke-width="14"
                                        stroke-dasharray="84 155" stroke-dashoffset="-95" transform="rotate(-90 50 50)"/>
                                    <text x="50" y="54" text-anchor="middle" font-size="13" font-weight="700" fill="var(--bs-dark)">89%</text>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pelanggan Baru Mini -->
                <div class="card w-100 flex-grow-1">
                    <div class="card-body">
                        <div class="row align-items-start">
                            <div class="col-8">
                                <h4 class="card-title mb-9 fw-semibold">Pelanggan Baru</h4>
                                <h4 class="fw-semibold mb-3">148 bulan ini</h4>
                                <div class="d-flex align-items-center pb-1">
                                    <span class="me-2 rounded-circle bg-success-subtle round-20 d-flex align-items-center justify-content-center flex-shrink-0">
                                        <i class="ti ti-arrow-up-left text-success"></i>
                                    </span>
                                    <p class="text-dark me-1 fs-3 mb-0">+8.3%</p>
                                    <p class="fs-3 mb-0">dari bulan lalu</p>
                                </div>
                            </div>
                            <div class="col-4 d-flex justify-content-end">
                                <div class="text-white text-bg-primary rounded-circle p-6 d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                                    <i class="ti ti-user-plus fs-6"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Sparkline mini bar -->
                    <div class="px-3 pb-2">
                        <svg viewBox="0 0 300 40" class="w-100" style="height:40px;">
                            <rect v-for="(b,i) in sparkBars" :key="i"
                                :x="i*44" y="0" width="36" :height="b"
                                fill="var(--bs-primary-bg-subtle)" rx="3"/>
                            <rect :x="5*44" y="0" width="36" :height="sparkBars[5]"
                                fill="var(--bs-primary)" rx="3"/>
                        </svg>
                    </div>
                </div>

            </div>
        </div>

        <!-- ── Row 2: Aktivitas + 2 Mini Cards + VIP Card ── -->
        <div class="row g-3 mb-4">

            <!-- Aktivitas Mingguan -->
            <div class="col-lg-4 d-flex align-items-stretch">
                <div class="card w-100">
                    <div class="card-body">
                        <h4 class="card-title fw-semibold">Aktivitas Mingguan</h4>
                        <p class="card-subtitle">Rata-rata interaksi</p>
                        <div class="my-4 d-flex align-items-end gap-2 justify-content-between" style="height:80px;">
                            <div v-for="(b,i) in weekBars" :key="i"
                                class="d-flex flex-column align-items-center gap-1 flex-grow-1">
                                <div class="rounded-2 w-100"
                                    :style="`height:${b.h}px;background:${i===6?'var(--bs-primary)':'var(--bs-primary-bg-subtle)'};`"></div>
                                <span class="fs-2 text-muted">{{ b.d }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-7">
                            <div class="d-flex">
                                <div class="p-6 bg-primary-subtle rounded me-6 d-flex align-items-center justify-content-center">
                                    <i class="ti ti-user-check text-primary fs-6"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fs-4 fw-semibold">Pelanggan Teraktif</h6>
                                    <p class="fs-3 mb-0">Andi Pratama</p>
                                </div>
                            </div>
                            <div class="bg-primary-subtle badge">
                                <p class="fs-3 text-primary fw-semibold mb-0">+24</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-7">
                            <div class="d-flex">
                                <div class="p-6 bg-success-subtle rounded me-6 d-flex align-items-center justify-content-center">
                                    <i class="ti ti-star text-success fs-6"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fs-4 fw-semibold">Segmen Terbesar</h6>
                                    <p class="fs-3 mb-0">Premium (40%)</p>
                                </div>
                            </div>
                            <div class="bg-success-subtle badge">
                                <p class="fs-3 text-success fw-semibold mb-0">+12</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex">
                                <div class="p-6 bg-danger-subtle rounded me-6 d-flex align-items-center justify-content-center">
                                    <i class="ti ti-alert-triangle text-danger fs-6"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fs-4 fw-semibold">Risiko Churn</h6>
                                    <p class="fs-3 mb-0">Perlu tindak lanjut</p>
                                </div>
                            </div>
                            <div class="bg-danger-subtle badge">
                                <p class="fs-3 text-danger fw-semibold mb-0">7</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2 Mini Stats -->
            <div class="col-lg-4 d-flex align-items-stretch">
                <div class="row g-3 w-100">
                    <div class="col-sm-6 col-lg-12 d-flex align-items-stretch">
                        <div class="card w-100">
                            <div class="card-body pb-0 mb-3">
                                <p class="mb-1 fs-3">Total Aktif</p>
                                <h4 class="fw-semibold fs-7">2,890</h4>
                                <div class="d-flex align-items-center mb-3">
                                    <span class="me-2 rounded-circle bg-success-subtle round-20 d-flex align-items-center justify-content-center flex-shrink-0">
                                        <i class="ti ti-arrow-up-left text-success"></i>
                                    </span>
                                    <p class="text-dark fs-3 mb-0">+5.2%</p>
                                </div>
                                <div class="progress mb-0" style="height:4px;">
                                    <div class="progress-bar bg-success" style="width:89%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-12 d-flex align-items-stretch">
                        <div class="card w-100">
                            <div class="card-body">
                                <p class="mb-1 fs-3">Tidak Aktif</p>
                                <h4 class="fw-semibold fs-7">358</h4>
                                <div class="d-flex align-items-center mb-3">
                                    <span class="me-2 rounded-circle bg-danger-subtle round-20 d-flex align-items-center justify-content-center flex-shrink-0">
                                        <i class="ti ti-arrow-down-right text-danger"></i>
                                    </span>
                                    <p class="text-dark fs-3 mb-0">-2.4%</p>
                                </div>
                                <div class="progress mb-0" style="height:4px;">
                                    <div class="progress-bar bg-danger" style="width:11%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pelanggan VIP Feature Card -->
            <div class="col-lg-4 d-flex align-items-stretch">
                <div class="card text-bg-primary border-0 w-100">
                    <div class="card-body pb-0">
                        <h4 class="fw-semibold mb-1 text-white card-title">Pelanggan Premium</h4>
                        <p class="fs-3 mb-3 text-white opacity-75">40% dari total pelanggan</p>
                        <div class="text-center mt-2">
                            <svg viewBox="0 0 200 100" class="w-75 opacity-75">
                                <ellipse cx="100" cy="80" rx="90" ry="20" fill="rgba(255,255,255,0.1)"/>
                                <polygon points="100,10 160,80 40,80" fill="rgba(255,255,255,0.3)" stroke="rgba(255,255,255,0.5)" stroke-width="2"/>
                                <circle cx="100" cy="10" r="8" fill="white" opacity="0.9"/>
                                <text x="100" y="65" text-anchor="middle" font-size="14" fill="white" font-weight="700">VIP</text>
                            </svg>
                        </div>
                    </div>
                    <div class="card mx-2 mb-2 mt-n2">
                        <div class="card-body">
                            <div class="mb-7 pb-1">
                                <div class="d-flex justify-content-between align-items-center mb-6">
                                    <div>
                                        <h6 class="mb-1 fs-4 fw-semibold">Premium</h6>
                                        <p class="fs-3 mb-0">1,299 pelanggan</p>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary fw-semibold fs-3">40%</span>
                                </div>
                                <div class="progress bg-primary-subtle" style="height:4px;">
                                    <div class="progress-bar" style="width:40%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-6">
                                    <div>
                                        <h6 class="mb-1 fs-4 fw-semibold">Standard</h6>
                                        <p class="fs-3 mb-0">1,137 pelanggan</p>
                                    </div>
                                    <span class="badge bg-secondary-subtle text-secondary fw-bold fs-3">35%</span>
                                </div>
                                <div class="progress bg-secondary-subtle" style="height:4px;">
                                    <div class="progress-bar text-bg-secondary" style="width:35%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Row 3: Top Pelanggan + Tabel Pelanggan Terbaru ── -->
        <div class="row g-3">

            <!-- Top Pelanggan Aktif -->
            <div class="col-lg-4 d-flex align-items-stretch">
                <div class="card w-100">
                    <div class="card-body">
                        <h4 class="card-title fw-semibold">Top Pelanggan</h4>
                        <p class="card-subtitle">Berdasarkan aktivitas</p>

                        <div class="d-flex flex-column gap-0 mt-4">
                            <div v-for="(c, i) in topCustomers" :key="i"
                                class="d-flex align-items-center justify-content-between py-3"
                                :class="i < topCustomers.length-1 ? 'border-bottom' : ''">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                        :style="`width:38px;height:38px;font-size:13px;background:${c.color};`">
                                        {{ c.initials }}
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fs-4 fw-semibold">{{ c.name }}</h6>
                                        <p class="fs-2 mb-0 text-muted">{{ c.type }}</p>
                                    </div>
                                </div>
                                <div :class="`bg-${c.badge}-subtle badge`">
                                    <p :class="`fs-3 text-${c.badge} fw-semibold mb-0`">{{ c.score }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Pelanggan Terbaru -->
            <div class="col-lg-8 d-flex align-items-stretch">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="d-sm-flex d-block align-items-center justify-content-between mb-7">
                            <div class="mb-3 mb-sm-0">
                                <h4 class="card-title fw-semibold">Pelanggan Terbaru</h4>
                                <p class="card-subtitle">Pendaftaran terakhir</p>
                            </div>
                            <div class="d-flex gap-2">
                                <div class="input-group input-group-sm" style="width:200px;">
                                    <span class="input-group-text bg-transparent border-end-0">
                                        <i class="ti ti-search text-muted fs-4"></i>
                                    </span>
                                    <input v-model="search" type="text" class="form-control border-start-0" placeholder="Cari..."/>
                                </div>
                                <select v-model="filterStatus" class="form-select form-select-sm" style="width:120px;">
                                    <option value="">Semua</option>
                                    <option value="Aktif">Aktif</option>
                                    <option value="Tidak Aktif">Tidak Aktif</option>
                                    <option value="Pending">Pending</option>
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle text-nowrap mb-0">
                                <thead>
                                    <tr class="text-muted fw-semibold">
                                        <th class="ps-0">Pelanggan</th>
                                        <th>Kontak</th>
                                        <th>Bergabung</th>
                                        <th>Segmen</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody class="border-top">
                                    <tr v-for="c in filteredCustomers" :key="c.id">
                                        <td class="ps-0">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                                    :style="`width:40px;height:40px;font-size:13px;background:${c.avatarColor};`">
                                                    {{ c.initials }}
                                                </div>
                                                <div>
                                                    <h6 class="fw-semibold mb-1">{{ c.name }}</h6>
                                                    <p class="fs-2 mb-0 text-muted">{{ c.company }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="mb-0 fs-3">{{ c.email }}</p>
                                            <p class="fs-2 mb-0 text-muted">{{ c.phone }}</p>
                                        </td>
                                        <td><p class="mb-0 fs-3">{{ c.joined }}</p></td>
                                        <td>
                                            <span class="badge fw-semibold py-1" :class="segmentBadge(c.segment)">{{ c.segment }}</span>
                                        </td>
                                        <td>
                                            <span class="badge fw-semibold py-1" :class="statusBadge(c.status)">{{ c.status }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <button class="btn btn-sm btn-outline-secondary p-1 lh-1" title="Detail">
                                                    <i class="ti ti-eye fs-4"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-secondary p-1 lh-1" title="Edit">
                                                    <i class="ti ti-pencil fs-4"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredCustomers.length === 0">
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <i class="ti ti-search-off fs-7 d-block mb-2"></i>
                                            <p class="fs-3 mb-0">Tidak ada hasil ditemukan</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

// ── Stat Cards ──
const stats = [
    { label: 'Total Pelanggan', value: '3,248', trend: '+12.5% bulan ini', up: true,  color: 'primary', icon: 'ti-users'      },
    { label: 'Pelanggan Aktif', value: '2,890', trend: '89% dari total',   up: true,  color: 'success', icon: 'ti-user-check' },
    { label: 'Pelanggan Baru',  value: '148',   trend: '+8.3% bulan lalu', up: true,  color: 'warning', icon: 'ti-user-plus'  },
    { label: 'Tidak Aktif',     value: '358',   trend: '-2.4% bulan lalu', up: false, color: 'danger',  icon: 'ti-user-minus' },
    { label: 'Churn Rate',      value: '2.4%',  trend: '-0.8% membaik',    up: true,  color: 'info',    icon: 'ti-chart-line' },
];

// ── Chart ──
const chartYear = ref('2026');
const chartBars = [
    { label: 'Jan', x: 10,  y: 80,  w: 28, h: 100, y2: 90,  h2: 90  },
    { label: 'Feb', x: 95,  y: 70,  w: 28, h: 110, y2: 80,  h2: 100 },
    { label: 'Mar', x: 180, y: 50,  w: 28, h: 130, y2: 60,  h2: 120 },
    { label: 'Apr', x: 265, y: 60,  w: 28, h: 120, y2: 70,  h2: 110 },
    { label: 'Mei', x: 350, y: 30,  w: 28, h: 150, y2: 40,  h2: 140 },
    { label: 'Jun', x: 435, y: 40,  w: 28, h: 140, y2: 50,  h2: 130 },
];

// ── Sparkline ──
const sparkBars = [20, 28, 22, 32, 26, 40];

// ── Weekly activity bars ──
const weekBars = [
    { d: 'Sen', h: 30 }, { d: 'Sel', h: 50 }, { d: 'Rab', h: 40 },
    { d: 'Kam', h: 65 }, { d: 'Jum', h: 45 }, { d: 'Sab', h: 25 },
    { d: 'Min', h: 70 },
];

// ── Top Customers ──
const topCustomers = [
    { name: 'Andi Pratama',     type: 'Premium',  initials: 'AP', color: '#5d87ff', badge: 'primary', score: '+68' },
    { name: 'Dewi Lestari',     type: 'Premium',  initials: 'DL', color: '#7c3aed', badge: 'primary', score: '+52' },
    { name: 'Siti Rahayu',      type: 'Standard', initials: 'SR', color: '#13deb9', badge: 'success', score: '+47' },
    { name: 'Hendra Gunawan',   type: 'Premium',  initials: 'HG', color: '#539bff', badge: 'info',    score: '+41' },
    { name: 'Nurul Hidayah',    type: 'Basic',    initials: 'NH', color: '#ffae1f', badge: 'warning', score: '+33' },
];

// ── Customers Table ──
const search       = ref('');
const filterStatus = ref('');

const customers = ref([
    { id: 1, name: 'Andi Pratama',     company: 'PT Maju Jaya',      email: 'andi@majujaya.co.id',    phone: '0812-3456-7890', joined: '10 Jan 2026', segment: 'Premium',  status: 'Aktif',       initials: 'AP', avatarColor: '#5d87ff' },
    { id: 2, name: 'Siti Rahayu',      company: 'CV Sejahtera',       email: 'siti@sejahtera.com',     phone: '0856-2345-6789', joined: '14 Jan 2026', segment: 'Standard', status: 'Aktif',       initials: 'SR', avatarColor: '#13deb9' },
    { id: 3, name: 'Budi Santoso',     company: 'Toko Online Budi',   email: 'budi@tokobudi.id',       phone: '0821-9876-5432', joined: '20 Jan 2026', segment: 'Basic',    status: 'Pending',     initials: 'BS', avatarColor: '#ffae1f' },
    { id: 4, name: 'Dewi Lestari',     company: 'PT Lestari Abadi',   email: 'dewi@lestariabadi.com',  phone: '0878-5432-1098', joined: '3 Feb 2026',  segment: 'Premium',  status: 'Aktif',       initials: 'DL', avatarColor: '#7c3aed' },
    { id: 5, name: 'Rizky Firmansyah', company: 'CV Tekno Muda',      email: 'rizky@teknomu.com',      phone: '0813-6543-2109', joined: '15 Feb 2026', segment: 'Standard', status: 'Tidak Aktif', initials: 'RF', avatarColor: '#fa896b' },
    { id: 6, name: 'Nurul Hidayah',    company: 'Butik Nurul',        email: 'nurul@butiknurul.id',    phone: '0852-7654-3210', joined: '22 Feb 2026', segment: 'Basic',    status: 'Aktif',       initials: 'NH', avatarColor: '#ffae1f' },
    { id: 7, name: 'Hendra Gunawan',   company: 'PT Guna Karya',      email: 'hendra@gunakarya.co.id', phone: '0819-8765-4321', joined: '5 Mar 2026',  segment: 'Premium',  status: 'Aktif',       initials: 'HG', avatarColor: '#539bff' },
    { id: 8, name: 'Fitri Wahyuni',    company: 'Klinik Sehat Fitri', email: 'fitri@klinikfitri.id',   phone: '0877-0987-6543', joined: '18 Mar 2026', segment: 'Standard', status: 'Pending',     initials: 'FW', avatarColor: '#13deb9' },
]);

const filteredCustomers = computed(() =>
    customers.value.filter(c => {
        const q = search.value.toLowerCase();
        return (!q || c.name.toLowerCase().includes(q) || c.email.toLowerCase().includes(q) || c.company.toLowerCase().includes(q))
            && (!filterStatus.value || c.status === filterStatus.value);
    })
);

const segmentBadge = (s: string) => ({
    'bg-primary-subtle text-primary': s === 'Premium',
    'bg-secondary-subtle text-secondary': s === 'Standard',
    'bg-warning-subtle text-warning': s === 'Basic',
});

const statusBadge = (s: string) => ({
    'bg-success-subtle text-success': s === 'Aktif',
    'bg-danger-subtle text-danger':   s === 'Tidak Aktif',
    'bg-warning-subtle text-warning': s === 'Pending',
});
</script>
