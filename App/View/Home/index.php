<!DOCTYPE html>
<html>

<head>
    <title>Home</title>
</head>

<body>
    <h1>Welcome</h1>
    <p>Hello, <?= htmlspecialchars($name); ?></p>

    <ul>
        <?php foreach ($colors as $color): ?>
            <li><?= $color; ?></li>
        <?php endforeach; ?>
    </ul>
</body>

</html>