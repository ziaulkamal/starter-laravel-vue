# K6 Load Testing Strategy

Dokumen ini merangkum strategi pengujian berbasis hasil spike test (500–800 concurrent users → 502 Bad Gateway karena PHP-FPM worker habis).

---

## Temuan dari Spike Test

| Fase     | Users | Req/s | Avg Latency | Error Rate |
|----------|-------|-------|-------------|------------|
| Normal   | 500   | 300   | 1460ms      | 88.8%      |
| Spike    | 800   | 205   | 3558ms      | 96.6%      |
| Recovery | 500   | 239   | 1799ms      | 90.8%      |

**Root cause:** PHP-FPM kehabisan worker → Nginx return 502 Bad Gateway.

---

## Kenapa K6?

Dibanding script Node.js custom:

| Fitur | Node.js custom | K6 |
|-------|---------------|-----|
| Threshold otomatis (pass/fail) | ✗ | ✓ |
| Grafik & report HTML | ✗ | ✓ |
| Skenario kompleks (ramp-up, spike, soak) | manual | built-in |
| Check per request | manual | built-in |
| Integrasi Grafana/InfluxDB | ✗ | ✓ |

---

## Instalasi K6

```bash
# Windows (winget)
winget install k6 --source winget

# Windows (Chocolatey)
choco install k6

# Mac
brew install k6

# Linux (Debian/Ubuntu)
sudo gpg -k
sudo gpg --no-default-keyring --keyring /usr/share/keyrings/k6-archive-keyring.gpg \
  --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys C5AD17C747E3415A3642D57D77C6C491D6AC1D69
echo "deb [signed-by=/usr/share/keyrings/k6-archive-keyring.gpg] https://dl.k6.io/deb stable main" \
  | sudo tee /etc/apt/sources.list.d/k6.list
sudo apt-get update && sudo apt-get install k6
```

---

## Skenario Testing

### 1. Smoke Test — Verifikasi Dasar

Jalankan sebelum semua test lain. Pastikan aplikasi tidak error di load minimal.

```javascript
// k6 run security-test/k6/smoke.js
import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  vus: 3,
  duration: '30s',
  thresholds: {
    http_req_failed:   ['rate<0.01'],   // error < 1%
    http_req_duration: ['p(95)<500'],   // p95 < 500ms
  },
};

export default function () {
  const r = http.get('http://localhost:8000/login');
  check(r, { 'status 200': r => r.status === 200 });
  sleep(1);
}
```

---

### 2. Load Test — Beban Normal

Simulasi traffic harian yang realistis.

```javascript
// k6 run security-test/k6/load.js
import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  stages: [
    { duration: '2m', target: 50  },   // ramp-up ke 50 users
    { duration: '5m', target: 50  },   // tahan 5 menit
    { duration: '2m', target: 0   },   // ramp-down
  ],
  thresholds: {
    http_req_failed:   ['rate<0.05'],   // error < 5%
    http_req_duration: ['p(95)<1000'],  // p95 < 1 detik
    http_req_duration: ['p(99)<3000'],  // p99 < 3 detik
  },
};

const BASE = 'http://localhost:8000';

export default function () {
  // Simulasi user browsing
  const pages = ['/login', '/register'];
  const page  = pages[Math.floor(Math.random() * pages.length)];

  const r = http.get(`${BASE}${page}`, {
    headers: { 'Accept': 'text/html' },
  });

  check(r, {
    'status ok':        r => r.status === 200,
    'no server error':  r => r.status < 500,
    'response < 2s':    r => r.timings.duration < 2000,
  });

  sleep(Math.random() * 2 + 1);   // jeda 1-3 detik (simulasi user nyata)
}
```

---

### 3. Stress Test — Cari Breaking Point

Naikkan users terus sampai server mulai error. Tujuan: tahu batas kapasitas.

```javascript
// k6 run security-test/k6/stress.js
import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  stages: [
    { duration: '2m', target: 50  },
    { duration: '2m', target: 100 },
    { duration: '2m', target: 150 },
    { duration: '2m', target: 200 },
    { duration: '2m', target: 300 },
    { duration: '2m', target: 400 },
    { duration: '2m', target: 0   },   // ramp-down
  ],
  thresholds: {
    http_req_failed:   ['rate<0.10'],   // alert jika error > 10%
    http_req_duration: ['p(95)<3000'],
  },
};

const BASE = 'http://localhost:8000';

export default function () {
  const r = http.get(`${BASE}/login`);
  check(r, {
    'not 502': r => r.status !== 502,
    'not 504': r => r.status !== 504,
  });
  sleep(0.5);
}
```

---

### 4. Spike Test — Lonjakan Tiba-tiba

Replika skenario yang sudah terjadi: traffic normal → lonjakan mendadak.

```javascript
// k6 run security-test/k6/spike.js
import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  stages: [
    { duration: '2m',  target: 50  },  // normal
    { duration: '30s', target: 500 },  // spike mendadak
    { duration: '3m',  target: 500 },  // tahan spike
    { duration: '30s', target: 50  },  // turun tiba-tiba
    { duration: '3m',  target: 50  },  // observasi recovery
    { duration: '1m',  target: 0   },
  ],
  thresholds: {
    http_req_failed:   ['rate<0.15'],
    http_req_duration: ['p(95)<5000'],
  },
};

const BASE = 'http://localhost:8000';

export default function () {
  const r = http.get(`${BASE}/login`);

  check(r, {
    'bukan 502 (PHP-FPM habis)': r => r.status !== 502,
    'bukan 504 (timeout)':       r => r.status !== 504,
    'response < 5s':             r => r.timings.duration < 5000,
  });

  sleep(0.3);
}
```

---

### 5. Soak Test — Ketahanan Jangka Panjang

Deteksi memory leak, koneksi DB tidak ditutup, file descriptor bocor.

```javascript
// k6 run security-test/k6/soak.js
import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  stages: [
    { duration: '5m',  target: 100 },  // ramp-up
    { duration: '2h',  target: 100 },  // tahan 2 jam
    { duration: '5m',  target: 0   },  // ramp-down
  ],
  thresholds: {
    http_req_failed:   ['rate<0.05'],
    http_req_duration: ['p(95)<2000'],
  },
};

export default function () {
  http.get('http://localhost:8000/login');
  sleep(1);
}
```

---

### 6. Breakpoint Test — Maksimal Absolut

Push sampai server benar-benar tidak bisa handle. Gunakan hanya di staging, bukan production.

```javascript
// k6 run security-test/k6/breakpoint.js
import http from 'k6/http';
import { check } from 'k6';

export const options = {
  executor:  'ramping-arrival-rate',
  startRate: 50,
  timeUnit:  '1s',
  preAllocatedVUs: 500,
  maxVUs:    2000,
  stages: [
    { duration: '5m', target: 100 },
    { duration: '5m', target: 300 },
    { duration: '5m', target: 600 },
    { duration: '5m', target: 1000 },
  ],
  thresholds: {
    // Test dianggap selesai jika error sudah > 50%
    http_req_failed: [{ threshold: 'rate<0.5', abortOnFail: true }],
  },
};

export default function () {
  const r = http.get('http://localhost:8000/login');
  check(r, { 'not 5xx': r => r.status < 500 });
}
```

---

## Urutan Eksekusi yang Disarankan

```
Sebelum deploy:
  1. smoke.js     → pastikan tidak ada error dasar
  2. load.js      → validasi performa di beban normal

Sebelum release besar:
  3. stress.js    → cari breaking point terbaru
  4. spike.js     → validasi recovery setelah lonjakan

Setelah optimasi PHP-FPM/Nginx/Redis:
  5. spike.js     → konfirmasi 502 sudah tidak muncul
  6. soak.js      → cek memory leak (jalankan semalam)

Staging only:
  7. breakpoint.js → tahu batas absolut server
```

---

## Target Threshold Setelah Optimasi

Berdasarkan temuan spike test, ini target yang harus dicapai setelah tuning:

| Metric | Sekarang | Target |
|--------|----------|--------|
| Error rate (normal load) | 88.8% | < 1% |
| Error rate (spike 800 users) | 96.6% | < 15% |
| Avg latency (normal) | 1460ms | < 300ms |
| P95 latency (normal) | 4226ms | < 1000ms |
| 502 Bad Gateway | sering | tidak ada |

---

## Jalankan dengan Output HTML Report

```bash
k6 run --out json=results.json security-test/k6/spike.js

# Atau dengan summary HTML (butuh k6 reporter)
K6_WEB_DASHBOARD=true k6 run security-test/k6/spike.js
```

---

## Checklist Sebelum Test

- [ ] `php artisan config:cache` sudah dijalankan
- [ ] `php artisan route:cache` sudah dijalankan
- [ ] OPcache aktif (`opcache.enable=1`)
- [ ] `pm.max_children` PHP-FPM sudah dinaikkan (min. 20)
- [ ] `SESSION_DRIVER=redis` jika Redis tersedia
- [ ] Server tidak sedang dipakai user lain (staging/local)
- [ ] Monitor RAM & CPU selama test: `htop` atau Task Manager
