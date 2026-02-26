<?php
// DB & sessie
require_once 'config.php';
require_once 'session.php';

// Haal producten op met mysqli prepared statement
$sql = "SELECT * FROM producten ORDER BY aangemaakt_op";
$stmt = $mysqli->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$producten = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>LEGO Shop - Home</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>
    <header class="header">
        <div class="header-container">
            <a href="index.php" class="logo">LEGO Shop</a>  
            <div class="user-info">
                <?php // Login links ?>
                <?php if (isLoggedIn()): ?>
                    <a href="logout.php">Uitloggen</a>
                    <a href="account.php">Mijn Account</a>
                    <a href="./winkelwagen.php">Winkelwagen (<?= getCartItemCount() ?>)</a>
                <?php else: ?>
                    <a href="inlogen.php">Inloggen</a>
                    <a href="regristreren.php">Registreren</a>
                    <a href="winkelwagen.php">Winkelwagen (<?= getCartItemCount() ?>)</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
                    
    <div class="container">
        <h1>Nieuwste LEGO sets</h1>
        
        <div class="products-grid">
            <?php // Producten ?>
            <?php foreach ($producten as $product): ?>
                <div class="product-card">
                    <?php // Aanbieding ?>
                    <?php if ($product['in_aanbieding']): ?>
                        <div class="sale-badge">Aanbieding!</div>
                    <?php endif; ?>
                    
                    <div class="product-image">
                        <?php // Afbeelding?>
                        <?php if ($product['afbeelding']): ?>
                            <img src="images/<?= htmlspecialchars($product['afbeelding']) ?>" alt="<?= htmlspecialchars($product['naam']) ?>">
                        <?php else: ?>
                            <div class="no-image">🧱</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-content">
                        <h3><?= htmlspecialchars($product['naam']) ?></h3>
                        <div class="product-price">
                            <?php // Prijs ?>
                            <?php if ($product['in_aanbieding']): ?>
                                <span class="original-price">€<?= number_format($product['prijs'], 2, ',', '.') ?></span>
                                <span class="sale-price">€<?= number_format($product['aanbieding_prijs'], 2, ',', '.') ?></span>
                            <?php else: ?>
                                <span class="current-price">€<?= number_format($product['prijs'], 2, ',', '.') ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="product-categorie"><?= htmlspecialchars($product['categorie']) ?></p>
                        
                        <div class="product-actions">
                          <?php // toevoegen winkelwagen ?>
                            <form method="POST" action="add_to_cart.php">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <button type="submit" class="btn btn-primary">Toevoegen</button>
                            </form>
                              <?php // Winkelwagen  bekijken ?>
                            <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-secondary">Bekijken</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>