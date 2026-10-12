<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<?php
// ====== LOGIKA PHP ======
$hasil = null;
$error = null;

// Tarif: [jam pertama, tiap jam berikutnya, ikon]
$tarif = [
    'mobil' => ['pertama' => 5000, 'lanjut' => 3000, 'ikon' => '🚗', 'nama' => 'Mobil'],
    'motor' => ['pertama' => 3000, 'lanjut' => 2000, 'ikon' => '🏍️', 'nama' => 'Motor'],
    'truck' => ['pertama' => 7000, 'lanjut' => 3000, 'ikon' => '🚚', 'nama' => 'Truck'],
];

if (isset($_POST['Submit'])) {
    $nomor           = trim($_POST['nomor'] ?? '');
    $nomor_kendaraan = strtoupper(trim($_POST['nomor_kendaraan'] ?? ''));
    $jenis_kendaraan = strtolower($_POST['jenis_kendaraan'] ?? '');
    $lama_parkir     = (int) ($_POST['lama_parkir'] ?? 0);
    $nama_petugas    = trim($_POST['nama_petugas'] ?? '');

    if (!isset($tarif[$jenis_kendaraan])) {
        $error = 'Jenis kendaraan tidak dikenali. Pilih mobil, motor, atau truck.';
    } elseif ($lama_parkir < 1) {
        $error = 'Lama parkir minimal 1 jam.';
    } else {
        $t     = $tarif[$jenis_kendaraan];
        $total = ($t['lanjut'] * ($lama_parkir - 1)) + $t['pertama'];

        $hasil = [
            'nomor'    => $nomor,
            'plat'     => $nomor_kendaraan,
            'jenis'    => $t['nama'],
            'ikon'     => $t['ikon'],
            'lama'     => $lama_parkir,
            'petugas'  => $nama_petugas,
            'pertama'  => $t['pertama'],
            'lanjut'   => $t['lanjut'],
            'tambahan' => $lama_parkir - 1,
            'total'    => $total,
        ];
    }
}

// Helper: escape output + format rupiah
function e($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function rp($n) { return 'Rp ' . number_format($n, 0, ',', '.'); }

// Pertahankan nilai input setelah submit
function old($k) { return e($_POST[$k] ?? ''); }
$pilih = strtolower($_POST['jenis_kendaraan'] ?? 'mobil');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hitung Biaya Parkir</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,500..900&family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --aspal: #1c2230;
            --aspal-gelap: #131823;
            --kertas: #f6f3ea;
            --marka: #ffc933;       /* kuning marka jalan */
            --marka-gelap: #e0a800;
            --tinta: #1c2230;
            --abu: #7c8394;
            --garis: #d9d4c4;
            --hijau: #2bb673;
            --radius: 14px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 32px 16px;
            font-family: 'Figtree', system-ui, sans-serif;
            color: var(--kertas);
            background:
                radial-gradient(1200px 500px at 50% -10%, #2a3248 0%, transparent 70%),
                var(--aspal-gelap);
            overflow-x: hidden;
        }

        /* Garis marka jalan yang berjalan di bagian bawah halaman */
        .jalan {
            position: fixed;
            left: 0; right: 0; bottom: 36px;
            height: 6px;
            background: repeating-linear-gradient(
                90deg,
                var(--marka) 0 56px,
                transparent 56px 112px
            );
            opacity: .35;
            animation: marka 2.4s linear infinite;
            pointer-events: none;
        }
        @keyframes marka { to { background-position-x: -112px; } }

        .wadah {
            width: 100%;
            max-width: 460px;
            display: grid;
            gap: 24px;
        }

        /* ===== Judul ===== */
        header h1 {
            font-family: 'Archivo', sans-serif;
            font-stretch: 110%;
            font-weight: 800;
            font-size: clamp(2rem, 7vw, 2.8rem);
            line-height: 1;
            letter-spacing: -0.02em;
            animation: masuk-judul .8s cubic-bezier(.2,.8,.2,1) both;
        }
        header p {
            margin-top: 10px;
            color: #aab1c2;
            animation: masuk-judul .8s .12s cubic-bezier(.2,.8,.2,1) both;
        }
        @keyframes masuk-judul {
            from { opacity: 0; transform: translateX(-24px); }
            to   { opacity: 1; transform: none; }
        }

        /* ===== Form ===== */
        form {
            background: var(--kertas);
            color: var(--tinta);
            border-radius: var(--radius);
            padding: 28px;
            display: grid;
            gap: 18px;
            box-shadow: 0 24px 60px -20px rgba(0,0,0,.6);
            animation: naik .7s .2s cubic-bezier(.2,.8,.2,1) both;
        }
        @keyframes naik {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: none; }
        }

        .baris { display: grid; gap: 18px; grid-template-columns: 1fr 1fr; }
        @media (max-width: 480px) { .baris { grid-template-columns: 1fr; } }

        label { display: grid; gap: 7px; font-weight: 600; font-size: .92rem; }

        input, select {
            width: 100%;
            font: inherit;
            font-weight: 500;
            color: var(--tinta);
            background: #fff;
            border: 2px solid var(--garis);
            border-radius: 10px;
            padding: 12px 14px;
            transition: border-color .2s, box-shadow .2s, transform .2s;
        }
        input::placeholder { color: #b0ab9a; font-weight: 400; }
        input:hover, select:hover { border-color: #bdb6a0; }
        input:focus-visible, select:focus-visible {
            outline: none;
            border-color: var(--tinta);
            box-shadow: 0 0 0 4px rgba(255, 201, 51, .55);
        }
        select {
            appearance: none;
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='9' fill='none' stroke='%231c2230' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M1 1.5l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 40px;
        }

        /* Tombol: lampu sinyal kuning dengan "kilau" yang lewat saat hover */
        button {
            position: relative;
            overflow: hidden;
            font: inherit;
            font-family: 'Archivo', sans-serif;
            font-stretch: 105%;
            font-weight: 800;
            font-size: 1.05rem;
            color: var(--tinta);
            background: var(--marka);
            border: none;
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            box-shadow: 0 5px 0 var(--marka-gelap);
            transition: transform .15s, box-shadow .15s;
        }
        button::after {
            content: "";
            position: absolute;
            top: 0; left: -80%;
            width: 50%; height: 100%;
            background: linear-gradient(100deg, transparent, rgba(255,255,255,.55), transparent);
            transform: skewX(-20deg);
        }
        button:hover::after { left: 130%; transition: left .7s ease; }
        button:hover { transform: translateY(-2px); box-shadow: 0 7px 0 var(--marka-gelap); }
        button:active { transform: translateY(4px); box-shadow: 0 1px 0 var(--marka-gelap); }
        button:focus-visible { outline: 3px solid var(--kertas); outline-offset: 3px; }

        /* ===== Pesan error ===== */
        .error {
            background: #3a1f26;
            border-left: 5px solid #ff5d73;
            color: #ffd3da;
            padding: 14px 16px;
            border-radius: 10px;
            animation: getar .5s both;
        }
        @keyframes getar {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-8px); }
            40% { transform: translateX(8px); }
            60% { transform: translateX(-5px); }
            80% { transform: translateX(5px); }
        }

        /* ===== Karcis hasil ===== */
        .karcis-wadah {
            position: relative;
            padding-top: 56px;           /* ruang untuk kendaraan yang masuk */
        }

        .kendaraan {
            position: absolute;
            top: 0; left: 0;
            font-size: 2.6rem;
            line-height: 1;
            transform: scaleX(-1);       /* menghadap ke kanan */
            animation: melaju 1.3s cubic-bezier(.3,.7,.2,1) both;
            filter: drop-shadow(0 6px 4px rgba(0,0,0,.4));
        }
        @keyframes melaju {
            0%   { left: -80px; opacity: 0; }
            15%  { opacity: 1; }
            100% { left: calc(50% - 22px); opacity: 1; }
        }

        .karcis {
            position: relative;
            background: var(--kertas);
            color: var(--tinta);
            border-radius: var(--radius);
            padding: 26px 28px 8px;
            transform-origin: top center;
            animation: cetak .9s .9s cubic-bezier(.2,.9,.2,1) both;
            box-shadow: 0 24px 60px -20px rgba(0,0,0,.6);
        }
        @keyframes cetak {
            from { opacity: 0; transform: translateY(-30px) scaleY(.7); }
            to   { opacity: 1; transform: none; }
        }

        /* lubang karcis di sisi kiri-kanan garis sobekan */
        .karcis::before, .karcis::after {
            content: "";
            position: absolute;
            top: 138px;
            width: 24px; height: 24px;
            background: var(--aspal-gelap);
            border-radius: 50%;
        }
        .karcis::before { left: -12px; }
        .karcis::after  { right: -12px; }

        .atas {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding-bottom: 18px;
        }
        .atas .label { font-size: .85rem; color: var(--abu); }
        .atas .nomor {
            font-family: 'Archivo', sans-serif;
            font-stretch: 110%;
            font-weight: 800;
            font-size: 1.4rem;
        }

        /* plat nomor */
        .plat {
            font-family: 'Archivo', sans-serif;
            font-stretch: 100%;
            font-weight: 800;
            font-size: 1.15rem;
            letter-spacing: .08em;
            background: #fff;
            border: 3px solid var(--tinta);
            border-radius: 8px;
            padding: 6px 14px;
            box-shadow: inset 0 0 0 2px #fff, inset 0 0 0 3px var(--garis);
        }

        .sobekan {
            border-top: 3px dashed var(--garis);
            margin: 0 -4px;
        }

        .rincian { padding: 18px 0 6px; display: grid; gap: 10px; }
        .rincian .r {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            font-size: .95rem;
        }
        .rincian .r span:first-child { color: var(--abu); }
        .rincian .r span:last-child { font-weight: 600; text-align: right; }

        .total {
            margin: 14px -28px 0;
            padding: 20px 28px 24px;
            background: var(--aspal);
            color: var(--kertas);
            border-radius: 0 0 var(--radius) var(--radius);
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 12px;
        }
        .total .judul { color: #aab1c2; font-weight: 500; }
        .total .angka {
            font-family: 'Archivo', sans-serif;
            font-stretch: 110%;
            font-weight: 800;
            font-size: clamp(1.8rem, 8vw, 2.4rem);
            color: var(--marka);
            font-variant-numeric: tabular-nums;
        }

        .kalimat {
            margin-top: 14px;
            font-size: .88rem;
            line-height: 1.55;
            color: #aab1c2;
            text-align: center;
            animation: masuk-judul .8s 1.6s both;
        }

        /* Hormati pengguna yang mematikan animasi */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: .01ms !important;
                animation-delay: 0s !important;
                transition-duration: .01ms !important;
            }
        }
    </style>
</head>
<body>
    <div class="jalan" aria-hidden="true"></div>

    <main class="wadah">
        <header>
            <h1>Hitung Biaya Parkir</h1>
            <p>Isi data kendaraan, biaya langsung dihitung.</p>
        </header>

        <form method="POST">
            <div class="baris">
                <label>
                    Nomor parkir
                    <input type="number" name="nomor" placeholder="mis. 12" min="1" value="<?= old('nomor') ?>">
                </label>
                <label>
                    Nomor kendaraan
                    <input type="text" name="nomor_kendaraan" placeholder="H 1234 AB" value="<?= old('nomor_kendaraan') ?>">
                </label>
            </div>

            <div class="baris">
                <label>
                    Jenis kendaraan
                    <select name="jenis_kendaraan" required>
                        <option value="mobil" <?= $pilih === 'mobil' ? 'selected' : '' ?>>Mobil</option>
                        <option value="motor" <?= $pilih === 'motor' ? 'selected' : '' ?>>Motor</option>
                        <option value="truck" <?= $pilih === 'truck' ? 'selected' : '' ?>>Truck</option>
                    </select>
                </label>
                <label>
                    Lama parkir (jam)
                    <input type="number" name="lama_parkir" min="1" placeholder="mis. 3" required value="<?= old('lama_parkir') ?>">
                </label>
            </div>

            <label>
                Nama petugas
                <input type="text" name="nama_petugas" placeholder="Nama petugas jaga" value="<?= old('nama_petugas') ?>">
            </label>

            <button type="submit" name="Submit">Hitung biaya</button>
        </form>

        <?php if ($error): ?>
            <div class="error" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($hasil): ?>
            <section class="karcis-wadah" aria-live="polite">
                <div class="kendaraan" aria-hidden="true"><?= $hasil['ikon'] ?></div>

                <article class="karcis">
                    <div class="atas">
                        <div>
                            <div class="label">Karcis parkir</div>
                            <div class="nomor">No. <?= e($hasil['nomor'] !== '' ? $hasil['nomor'] : '-') ?></div>
                        </div>
                        <div class="plat"><?= e($hasil['plat'] !== '' ? $hasil['plat'] : '-') ?></div>
                    </div>

                    <div class="sobekan"></div>

                    <div class="rincian">
                        <div class="r"><span>Jenis kendaraan</span><span><?= e($hasil['jenis']) ?></span></div>
                        <div class="r"><span>Lama parkir</span><span><?= e($hasil['lama']) ?> jam</span></div>
                        <div class="r"><span>Jam pertama</span><span><?= rp($hasil['pertama']) ?></span></div>
                        <div class="r">
                            <span>Jam berikutnya (<?= e($hasil['tambahan']) ?> × <?= rp($hasil['lanjut']) ?>)</span>
                            <span><?= rp($hasil['tambahan'] * $hasil['lanjut']) ?></span>
                        </div>
                        <div class="r"><span>Petugas</span><span><?= e($hasil['petugas'] !== '' ? $hasil['petugas'] : '-') ?></span></div>
                    </div>

                    <div class="total">
                        <span class="judul">Total bayar</span>
                        <span class="angka" data-total="<?= (int) $hasil['total'] ?>"><?= rp($hasil['total']) ?></span>
                    </div>
                </article>

                <p class="kalimat">
                    Kendaraan Anda <?= e(strtolower($hasil['jenis'])) ?>
                    dengan nomor kendaraan <?= e($hasil['plat']) ?>
                    telah parkir selama <?= e($hasil['lama']) ?> jam,
                    dan total biaya parkir adalah <?= rp($hasil['total']) ?>.
                </p>
            </section>

            <script>
                // Angka total naik dari 0 setelah karcis tercetak
                (function () {
                    var el = document.querySelector('.angka');
                    if (!el || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

                    var target = parseInt(el.dataset.total, 10);
                    var mulai = null, tunda = 1500, durasi = 1100;

                    function format(n) {
                        return 'Rp ' + n.toLocaleString('id-ID');
                    }

                    el.textContent = format(0);

                    function langkah(t) {
                        if (mulai === null) mulai = t;
                        var p = Math.min(Math.max((t - mulai - tunda) / durasi, 0), 1);
                        var ease = 1 - Math.pow(1 - p, 3);
                        el.textContent = format(Math.round(target * ease));
                        if (p < 1) requestAnimationFrame(langkah);
                    }
                    requestAnimationFrame(langkah);
                })();
            </script>
        <?php endif; ?>
    </main>
</body>
</html>