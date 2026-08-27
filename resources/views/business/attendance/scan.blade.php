<x-app-layout heading="QR scanner">
    <div class="page-head">
        <div>
            <h1>Scan member QR</h1>
            <p class="muted">First scan checks in. A second scan the same day checks out.</p>
        </div>
    </div>
    <div class="grid grid-2">
        <div class="card"><div class="card-b">
            <div id="reader" class="scan-frame"></div>
            <p id="scan-result" class="muted" style="margin-top:12px">Waiting for a scan…</p>
        </div></div>
        <div class="card"><div class="card-b">
            <form id="token-form" class="form">
                @csrf
                <label>Or enter token <input name="token" id="token" required></label>
                <button class="btn btn-primary" type="submit">Record attendance</button>
            </form>
        </div></div>
    </div>
    @push('scripts')
        <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
        <script>
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const endpoint = @json(route('business.attendance.scan.store'));
            const resultEl = document.getElementById('scan-result');

            async function submitToken(token) {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ token })
                });
                const data = await response.json();
                resultEl.textContent = response.ok
                    ? `${data.message}: ${data.attendance.member} (${data.attendance.code})`
                    : (data.message || data.errors?.token?.[0] || data.errors?.member?.[0] || 'Scan failed');
            }

            document.getElementById('token-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                await submitToken(document.getElementById('token').value);
            });

            if (window.Html5Qrcode) {
                const scanner = new Html5Qrcode('reader');
                Html5Qrcode.getCameras().then((cameras) => {
                    if (!cameras.length) return;
                    scanner.start(cameras[0].id, { fps: 8, qrbox: 220 }, (decoded) => submitToken(decoded));
                }).catch(() => {
                    resultEl.textContent = 'Camera not available. Enter the token instead.';
                });
            }
        </script>
    @endpush
</x-app-layout>
