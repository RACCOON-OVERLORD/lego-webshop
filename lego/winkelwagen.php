<?php
require_once 'config.php'; // DB
require_once 'session.php'; // Sessie

if (!isLoggedIn()) { // Login check
    header('Location: inlogen.php');
    exit();
}

$cart = getCart();
$totaal = 0;

// Prepare statement voor hergebruik
$stmt = $mysqli->prepare("SELECT * FROM producten WHERE id = ?");
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Winkelwagen</title>
    <link rel="stylesheet" href="css.css">  
</head>
<body>
    <header class="header">
        <div class="header-container">
            <a href="index.php" class="logo">LEGO Shop</a>
            <div class="user-info">
                <a href="winkelwagen.php">Winkelwagen (<?= getCartItemCount() ?>)</a>
                <a href="logout.php">Uitloggen</a>
            </div>
        </div>
    </header>

    <div class="container">
        <h1>Winkelwagen</h1>
        
        <?php if (empty($cart)): ?>
            <p>Je winkelwagen is leeg</p>
            <a href="index.php" class="btn btn-primary">Verder winkelen</a>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Prijs</th>
                        <th>Aantal</th>
                        <th>Subtotaal</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart as $productId => $aantal): 
                        $stmt->bind_param("i", $productId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $product = $result->fetch_assoc();
                        
                        $prijs = $product['in_aanbieding'] ? $product['aanbieding_prijs'] : $product['prijs'];
                        $subtotaal = $prijs * $aantal;
                        $totaal += $subtotaal;
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($product['naam']) ?></td>
                            <td>€<?= number_format($prijs, 2, ',', '.') ?></td>
                            <td>
                                <div class="quantity-controls">
                                    <!-- Verlaag aantal knop -->
                                    <form method="POST" action="remove_from_cart.php" style="display: inline;">
                                        <input type="hidden" name="product_id" value="<?= $productId ?>">
                                        <input type="hidden" name="action" value="remove_one">
                                        <button type="submit" class="btn btn-sm btn-outline">-</button>
                                    </form>
                                    
                                    <span style="margin: 0 10px;"><?= $aantal ?>x</span>
                                    
                                    <!-- Verhoog aantal knop -->
                                    <form method="POST" action="add_to_cart.php" style="display: inline;">
                                        <input type="hidden" name="product_id" value="<?= $productId ?>">
                                        <button type="submit" class="btn btn-sm btn-outline">+</button>
                                    </form>
                                </div>
                            </td>
                            <td>€<?= number_format($subtotaal, 2, ',', '.') ?></td>
                            <td>
                                <!-- Verwijder alle knop -->
                                <form method="POST" action="remove_from_cart.php" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?= $productId ?>">
                                    <input type="hidden" name="action" value="remove_all">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Weet je zeker dat je dit product wilt verwijderen?')">
                                        Verwijderen
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4">Totaal</th>
                        <th>€<?= number_format($totaal, 2, ',', '.') ?></th>
                    </tr>
                </tfoot>
            </table>
            
            <div class="cart-actions">
                <div class="left-actions">
                    <form method="POST" action="remove_from_cart.php" style="display: inline;">
                        <input type="hidden" name="clear_cart" value="1">
                        <button type="submit" class="btn btn-outline" onclick="return confirm('Weet je zeker dat je de hele winkelwagen wilt legen?')">
                            Winkelwagen legen
                        </button>
                    </form>
                </div>
                <div class="right-actions">
                    <a href="index.php" class="btn btn-secondary">Verder winkelen</a>
                    <a href="afrekenen.php" class="btn btn-primary">Afrekenen</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>