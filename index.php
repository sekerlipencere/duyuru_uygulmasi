<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Duyuru Formu</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
<style>
    /* Fontları yükle */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap');

    body {
        font-family: 'Inter', sans-serif;
    }

    .custom-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background-color: #f3f4f6;
    }

    .form-container {
        max-width: 500px;
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .form-header {
        background-color: #2563eb;
        color: #ffffff;
        padding: 20px;
        text-align: center;
    }

    .form-body {
        padding: 30px;
    }

    .form-input {
        width: 100%;
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 5px;
        border: 1px solid #e5e7eb;
    }

    .form-button {
        width: 100%;
        padding: 10px;
        border-radius: 5px;
        border: none;
        background-color: #2563eb;
        color: #ffffff;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .form-button:hover {
        background-color: #1e429f;
    }

    /* Animasyonlar için stil */
    @keyframes rubberBand {
        0% {
            transform: scale(1);
        }
        30% {
            transform: scale(1.25);
        }
        100% {
            transform: scale(1);
        }
    }

    .animate-rubberBand {
        animation: rubberBand 1s;
    }

    /* Mobil cihazlar için ek stil */
    @media (max-width: 768px) {
        .custom-container {
            padding: 20px;
        }

        .form-container {
            max-width: 100%;
        }

        .form-body {
            padding: 20px;
        }
    }
</style>
</head>
<body>
    <div class="custom-container">
        <div class="form-container animate__animated animate__fadeInDown">
            <div class="form-header">
                <h1 class="text-3xl font-bold">Duyuru Formu</h1>
            </div>
            <div class="form-body">
                <form id="duyuruFormu" class="space-y-4" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div>
                        <label for="duyuruMetni" class="block text-sm font-medium text-gray-700">Duyuru Metni:</label>
                        <textarea id="duyuruMetni" name="duyuruMetni" rows="4" required
                                  class="form-input"></textarea>
                    </div>
                    <div>
                        <label for="duyuruSuresi" class="block text-sm font-medium text-gray-700">Duyuru Süresi (saniye):</label>
                        <input type="number" id="duyuruSuresi" name="duyuruSuresi" min="1" required
                               class="form-input">
                    </div>
                    <div>
                        <button type="submit" class="form-button">Duyur</button>
                    </div>
                </form>
                <div id="onayTiki" style="display: none;">
                    <img id="onayTikiImg" src="https://cdn.jsdelivr.net/gh/mailtoharshit/SVGs@5fa6a9604519e7f2f05e913bf147e65a0302e7ed/checked.svg"
                         alt="Onay Tiki" class="w-8 h-8 animate__animated animate__rubberBand">
                </div>
                <?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $veri = $_POST['duyuruMetni'] . '$' . $_POST['duyuruSuresi'] . "\n";

    // Dosyanın içeriğini temizle
    file_put_contents('duyurular.txt', '');

    // Dosyaya yeni veriyi ekle
    if (file_put_contents('duyurular.txt', $veri, FILE_APPEND) !== false) {
        echo '<p class="text-green-500">Duyuru başarıyla kaydedildi!</p>';
        echo '<script>
                document.getElementById("onayTiki").style.display = "block"; // Onay tiki göster
                document.getElementById("onayTikiImg").classList.add("animate__rubberBand"); // Animasyonu ekle
              </script>';
    } else {
        echo '<p class="text-red-500">Dosyaya yazma hatası!</p>';
    }
}
?>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('duyuruFormu').addEventListener('submit', function (e) {
            e.preventDefault(); // Formun varsayılan gönderme işlemini durdur

            var duyuruMetni = document.getElementById('duyuruMetni').value;
            var duyuruSuresi = document.getElementById('duyuruSuresi').value;

            var veri = duyuruMetni + '$' + duyuruSuresi; // Veriyi istenen formatta birleştir

            // AJAX ile PHP dosyasına veriyi gönder
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById('duyuruFormu').reset(); // Formu sıfırla
                    document.getElementById('formSonuc').innerHTML = xhr.responseText; // Sonucu ekrana yazdır
                    document.getElementById("onayTiki").style.display = "block"; // Onay tikiyi göster
                    document.getElementById("onayTikiImg").classList.add("animate-rubberBand"); // Animasyonu ekle
                }
            };
            xhr.send('duyuruMetni=' + encodeURIComponent(duyuruMetni) + '&duyuruSuresi=' + encodeURIComponent(duyuruSuresi));
        });
    </script>
</body>
</html>
