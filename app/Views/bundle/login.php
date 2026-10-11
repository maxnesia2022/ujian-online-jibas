<?= view('bundle/_head', ['pageTitle' => 'Masuk', 'assetVersion' => $assetVersion, 'baseUrl' => $baseUrl, 'school' => $school]) ?>
<body>
<?= view('bundle/_appbar', ['school' => $school]) ?>
<div class="k-wrap" style="padding-top:5vh">
    <div class="k-pagehead">
        <h1>Masuk Ujian</h1>
        <p>Gunakan akun yang diberikan sekolah.</p>
    </div>

    <div class="k-card">
        <div id="err" class="k-note k-error" style="display:none;margin-bottom:16px"></div>

        <form id="loginForm" class="k-stack" novalidate>
            <div>
                <label class="k-label" for="username">Username</label>
                <input class="k-input" id="username" name="username"
                       autocomplete="username" autocapitalize="none" autocorrect="off"
                       spellcheck="false" inputmode="text" required>
            </div>
            <div>
                <label class="k-label" for="password">Password</label>
                <input class="k-input" id="password" name="password" type="password"
                       autocomplete="current-password" required>
            </div>
            <button class="k-btn" id="btn" type="submit">Masuk</button>
        </form>
    </div>

    <p class="k-muted" style="text-align:center;margin-top:20px">
        Pastikan Anda masuk dengan akun sendiri.
    </p>
    <div style="text-align:center;margin-top:8px;font-size:13px;color:var(--kiosk-muted)">
        <div>Server: <strong id="serverInfo">&nbsp;</strong></div>
        <div style="margin-top:6px;display:flex;align-items:center;justify-content:center;gap:8px;flex-wrap:wrap">
            <span id="connBadge" style="padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;background:#e2e8f0;color:#475569">Memeriksa koneksi…</span>
            <button type="button" id="btnTestConn" style="background:none;border:none;color:var(--kiosk-primary);cursor:pointer;font-size:12px;text-decoration:underline;padding:2px 4px">Tes Ulang</button>
        </div>
        <div id="connDetail" style="display:none;margin:8px auto 0;max-width:440px;font-size:12px;text-align:left;color:var(--kiosk-danger);background:var(--kiosk-danger-bg);border:1px solid #fecaca;padding:8px 12px;border-radius:8px;word-break:break-word"></div>
    </div>
</div>

<script>
    (function () {
        var base = window.KIOSK_BASE_URL;
        localStorage.setItem('kiosk_base_url', base);

        // Tunjukkan server yang dituju: kalau perangkat salah diarahkan,
        // ini satu-satunya petunjuk yang siswa/pengawas punya sebelum login.
        try {
            document.getElementById('serverInfo').textContent = new URL(base).host;
        } catch (e) {
            document.getElementById('serverInfo').textContent = base || '';
        }

        var form = document.getElementById('loginForm');
        var btn = document.getElementById('btn');
        var err = document.getElementById('err');
        var connBadge = document.getElementById('connBadge');
        var btnTestConn = document.getElementById('btnTestConn');
        var connDetail = document.getElementById('connDetail');

        function checkServerConnection() {
            if (!connBadge) return;
            connBadge.textContent = 'Menghubungi…';
            connBadge.style.background = '#e2e8f0';
            connBadge.style.color = '#475569';
            if (connDetail) connDetail.style.display = 'none';

            var t0 = Date.now();
            var controller = (typeof AbortController !== 'undefined') ? new AbortController() : null;
            var timeoutId = controller ? setTimeout(function () { controller.abort(); }, 5000) : null;

            fetch(base + '/health', {
                method: 'GET',
                cache: 'no-store',
                signal: controller ? controller.signal : undefined
            }).then(function (res) {
                if (timeoutId) clearTimeout(timeoutId);
                var lat = Date.now() - t0;
                if (res.ok) {
                    connBadge.textContent = '● Terhubung (' + lat + 'ms)';
                    connBadge.style.background = '#dcfce7';
                    connBadge.style.color = '#15803d';
                } else {
                    connBadge.textContent = '● HTTP ' + res.status;
                    connBadge.style.background = '#fee2e2';
                    connBadge.style.color = '#b91c1c';
                    if (connDetail) {
                        connDetail.textContent = 'Server merespon kode: HTTP ' + res.status;
                        connDetail.style.display = 'block';
                    }
                }
            }).catch(function (e) {
                if (timeoutId) clearTimeout(timeoutId);
                connBadge.textContent = '● Gagal Terhubung';
                connBadge.style.background = '#fee2e2';
                connBadge.style.color = '#b91c1c';
                var msg = e.name === 'AbortError' ? 'Koneksi timeout (>5 detik).' : (e.message || 'Tidak dapat menjangkau server.');
                if (connDetail) {
                    connDetail.innerHTML = '<strong>Error Koneksi:</strong> ' + msg + '<br><small>Pastikan HP & PC di Wi-Fi yang sama, port 8080 diizinkan di firewall Windows, dan Mixed Content HTTP diizinkan jika menggunakan Android WebView.</small>';
                    connDetail.style.display = 'block';
                }
            });
        }

        if (btnTestConn) {
            btnTestConn.addEventListener('click', checkServerConnection);
        }
        checkServerConnection();

        function fail(msg) {
            err.textContent = msg;
            err.style.display = 'block';
            btn.disabled = false;
            btn.textContent = 'Masuk';
        }

        /* Diambil dari jembatan native. Di luar aplikasi kiosk jembatannya
           tidak ada, dan string kosong itu benar: server memperlakukan login
           tanpa device_id sebagai tidak boleh merebut sesi perangkat lain.

           Periksa `typeof ... === 'function'`, bukan sekadar keberadaan
           window.CommsBridge: bundle bisa lebih baru daripada APK-nya. Di APK
           lama jembatannya ada tapi getDeviceId belum, dan pemeriksaan longgar
           akan melempar TypeError di tengah handler submit — login gagal total,
           bukan sekadar tanpa ambil-alih. */
        function deviceId() {
            try {
                if (window.CommsBridge && typeof window.CommsBridge.getDeviceId === 'function') {
                    return window.CommsBridge.getDeviceId() || '';
                }
            } catch (e) { /* jaring pengaman; jalur nyata ditangani || '' di atas */ }
            return '';
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var u = document.getElementById('username').value.trim();
            var p = document.getElementById('password').value;
            if (!u || !p) { fail('Username dan password wajib diisi.'); return; }

            btn.disabled = true;
            btn.textContent = 'Memeriksa…';
            err.style.display = 'none';

            fetch(base + '/login', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'kiosk-bundle'
                },
                body: 'username=' + encodeURIComponent(u)
                    + '&password=' + encodeURIComponent(p)
                    + '&device_id=' + encodeURIComponent(deviceId())
            }).then(function (r) {
                return r.text().then(function (txt) {
                    try {
                        return { ok: r.ok, status: r.status, j: JSON.parse(txt) };
                    } catch (e) {
                        return { ok: r.ok, status: r.status, raw: txt };
                    }
                });
            }).then(function (res) {
                if (res.j && res.j.status === 'success') {
                    if (res.j.session_id) {
                        try { localStorage.setItem('kiosk_session_id', res.j.session_id); } catch (e) {}
                    }
                    location.href = 'dashboard.html' + window.location.search;
                } else if (res.j && res.j.message) {
                    fail(res.j.message);
                } else {
                    fail('Respon server (HTTP ' + res.status + '): ' + (res.raw ? res.raw.substring(0, 100) : 'Gagal memproses login.'));
                }
            }).catch(function (error) {
                fail('Gagal terhubung ke ' + base + ' (' + (error.message || 'NetworkError') + '). Periksa status server di bawah.');
                checkServerConnection();
            });
        });
    })();
</script>
</body></html>
