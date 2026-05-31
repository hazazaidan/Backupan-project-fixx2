function startScanner() {

    let isProcessing = false;

    function onScanSuccess(decodedText) {

        if (isProcessing) return;
        isProcessing = true;

        fetch('?url=scan/api', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'qr_code=' + encodeURIComponent(decodedText)
        })
        .then(res => res.json())
        .then(data => {

            const resultEl = document.getElementById('result');

            if (data.success) {
                resultEl.innerHTML = `
                    <div class="p-4 bg-green-50 border border-green-200 rounded-xl">
                        <p class="font-semibold text-green-700">✅ ${data.message}</p>
                        <p class="text-sm text-green-600">${data.siswa?.nama_kelas ?? ''} · ${data.waktu}</p>
                    </div>`;
            } else {
                resultEl.innerHTML = `
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl">
                        <p class="font-semibold text-red-700">❌ ${data.message}</p>
                        <p class="text-sm text-red-500">Scan gagal</p>
                    </div>`;
            }

            // Reset setelah 3 detik biar bisa scan lagi
            setTimeout(() => { isProcessing = false; }, 3000);
        })
        .catch(() => {
            isProcessing = false;
        });
    }

    new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 })
        .render(onScanSuccess);
}