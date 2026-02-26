<?php
require_once 'config.php'; // DB
require_once 'session.php'; // Sessies

if (!isset($_GET['id'])) { // ID check
    header('Location: index.php');
    exit();
}

// Product ophalen met mysqli prepared statement
$stmt = $mysqli->prepare("SELECT * FROM producten WHERE id = ?");
$stmt->bind_param("i", $_GET['id']);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) { // Bestaat niet
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['naam']) ?> - LEGO Shop</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>
    <header class="header">
        <div class="header-container">
            <a href="index.php" class="logo">LEGO Shop</a>
            <div class="user-info">
                <?php if (isLoggedIn()): ?>
                    <a href="winkelwagen.php">Winkelwagen (<?= getCartItemCount() ?>)</a>
                    <a href="logout.php">Uitloggen</a>
                <?php else: ?>
                    <a href="inlogen.php">Inloggen</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="product-detail">
            <div class="product-images">
                <div class="main-image">
                    <?php if ($product['afbeelding']): ?>
                        <img src="images/<?= htmlspecialchars($product['afbeelding']) ?>" alt="<?= htmlspecialchars($product['naam']) ?>">
                    <?php else: ?>
                        <div class="no-image">Geen afbeelding beschikbaar</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="product-info">
                <h1><?= htmlspecialchars($product['naam']) ?></h1>
                <div class="product-meta">
                    <span class="product-categorie"><?= htmlspecialchars($product['categorie']) ?></span>
                    <span class="product-leeftijd">Leeftijd: <?= htmlspecialchars($product['leeftijd']) ?></span>
                    <span class="product-stukjes"><?= $product['aantal_stukjes'] ?> stukjes</span>
                </div>
                
                <div class="product-pricing">
                    <?php if ($product['in_aanbieding']): ?>
                        <span class="original-price">€<?= number_format($product['prijs'], 2, ',', '.') ?></span>
                        <span class="sale-price">€<?= number_format($product['aanbieding_prijs'], 2, ',', '.') ?></span>
                        <span class="sale-badge">Aanbieding!</span>
                    <?php else: ?>
                        <span class="current-price">€<?= number_format($product['prijs'], 2, ',', '.') ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="product-voorraad">
                    <?php if ($product['voorraad'] > 0): ?>
                        <span class="in-stock">Op voorraad (<?= $product['voorraad'] ?> stuks)</span>
                    <?php else: ?>
                        <span class="out-of-stock">Tijdelijk uitverkocht</span>
                    <?php endif; ?>
                </div>
                
                <div class="product-actions">
                    <?php if ($product['voorraad'] > 0): ?>
                        <form method="POST" action="add_to_cart.php">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <button type="submit" class="btn btn-primary">Toevoegen aan winkelwagen</button>
                        </form>
                    <?php endif; ?>
                </div>
                
                <div class="product-description">
                    <h3>Productbeschrijving</h3>
                    <p><?= nl2br(htmlspecialchars($product['beschrijving'])) ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>