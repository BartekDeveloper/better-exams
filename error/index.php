<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Błąd</title>
</head>
<body>
    <div class="container">
        <?php $code = isset($_GET['code']) ? $_GET['code'] : 404; ?>
        <img src="https://placehold.co/600x400/111/EEE?text=Error%20<?= $code ?>" alt="Błąd <?= $code ?>" style="width: 100%; height: auto; object-fit: cover;">
    </div>
</body>
</html>