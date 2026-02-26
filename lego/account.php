<?php
session_start();

// DB
$host = 'localhost';
$dbname = 'lego_webshop';
$username = 'root'; 
$password = 'root';     

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database verbinding mislukt: " . $e->getMessage());
}

// Login check
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Gebruiker ophalen
$stmt = $pdo->prepare("SELECT naam, achternaam, email FROM gebruikers WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: login.php');
    exit();
}

// Formulier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Naam
    if (isset($_POST['update_name'])) {
        $nieuwe_naam = trim($_POST['naam']);
        $nieuwe_achternaam = trim($_POST['achternaam']);
        
        if (empty($nieuwe_naam) || empty($nieuwe_achternaam)) {
            $error_message = "Naam en achternaam mogen niet leeg zijn.";
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE gebruikers SET naam = ?, achternaam = ? WHERE id = ?");
                $stmt->execute([$nieuwe_naam, $nieuwe_achternaam, $user_id]);
                $success_message = "Naam succesvol bijgewerkt!";
                $user['naam'] = $nieuwe_naam;
                $user['achternaam'] = $nieuwe_achternaam;
            } catch(PDOException $e) {
                $error_message = "Fout bij het bijwerken van de naam.";
            }
        }
    }
    
    // Wachtwoord
    if (isset($_POST['update_password'])) {
        $huidig_wachtwoord = $_POST['huidig_wachtwoord'];
        $nieuw_wachtwoord = $_POST['nieuw_wachtwoord'];
        $bevestig_wachtwoord = $_POST['bevestig_wachtwoord'];
        
        if (empty($huidig_wachtwoord) || empty($nieuw_wachtwoord) || empty($bevestig_wachtwoord)) {
            $error_message = "Alle wachtwoord velden zijn verplicht.";
        } elseif ($nieuw_wachtwoord !== $bevestig_wachtwoord) {
            $error_message = "Nieuwe wachtwoorden komen niet overeen.";
        } elseif (strlen($nieuw_wachtwoord) < 6) {
            $error_message = "Nieuw wachtwoord moet minimaal 6 karakters bevatten.";
        } else {
            // Controleer huidig wachtwoord
            $stmt = $pdo->prepare("SELECT wachtwoord FROM gebruikers WHERE id = ?");
            $stmt->execute([$user_id]);
            $stored_password = $stmt->fetchColumn();
            
            if (password_verify($huidig_wachtwoord, $stored_password)) {
                $hashed_password = password_hash($nieuw_wachtwoord, PASSWORD_DEFAULT);
                
                try {
                    $stmt = $pdo->prepare("UPDATE gebruikers SET wachtwoord = ? WHERE id = ?");
                    $stmt->execute([$hashed_password, $user_id]);
                    $success_message = "Wachtwoord succesvol bijgewerkt!";
                } catch(PDOException $e) {
                    $error_message = "Fout bij het bijwerken van het wachtwoord.";
                }
            } else {
                $error_message = "Huidig wachtwoord is incorrect.";
            }
        }
    }
    
    // Verwijder
    if (isset($_POST['delete_account'])) {
        $bevestig_wachtwoord = $_POST['bevestig_delete_wachtwoord'];
        
        if (empty($bevestig_wachtwoord)) {
            $error_message = "Wachtwoord is verplicht om account te verwijderen.";
        } else {
            // Controleer wachtwoord
            $stmt = $pdo->prepare("SELECT wachtwoord FROM gebruikers WHERE id = ?");
            $stmt->execute([$user_id]);
            $stored_password = $stmt->fetchColumn();
            
            if (password_verify($bevestig_wachtwoord, $stored_password)) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM gebruikers WHERE id = ?");
                    $stmt->execute([$user_id]);
                    
                    // Vernietig sessie en redirect naar homepage
                    session_destroy();
                    header('Location: index.php?message=account_deleted');
                    exit();
                } catch(PDOException $e) {
                    $error_message = "Fout bij het verwijderen van het account.";
                }
            } else {
                $error_message = "Wachtwoord is incorrect.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mijn Account - LEGO Webshop</title>
    <link rel="stylesheet" href="css.css">  
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Mijn Account</h1>
            <p>Beheer je accountgegevens</p>
        </div>

        <div class="content">
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    ✅ <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger">
                    ❌ <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="user-info">
                <h3>👤 Huidige accountgegevens</h3>
                <p><strong>Naam:</strong> <?php echo htmlspecialchars($user['naam'] . ' ' . $user['achternaam']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            </div>

            <!-- Update naam sectie -->
            <div class="section">
                <h3>✏️ Naam bijwerken</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="naam">Voornaam:</label>
                        <input type="text" id="naam" name="naam" value="<?php echo htmlspecialchars($user['naam']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="achternaam">Achternaam:</label>
                        <input type="text" id="achternaam" name="achternaam" value="<?php echo htmlspecialchars($user['achternaam']); ?>" required>
                    </div>
                    <button type="submit" name="update_name" class="btn btn-primary">Naam bijwerken</button>
                </form>
            </div>

            <!-- Update wachtwoord sectie -->
            <div class="section">
                <h3>🔐 Wachtwoord wijzigen</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="huidig_wachtwoord">Huidig wachtwoord:</label>
                        <input type="password" id="huidig_wachtwoord" name="huidig_wachtwoord" required>
                    </div>
                    <div class="form-group">
                        <label for="nieuw_wachtwoord">Nieuw wachtwoord:</label>
                        <input type="password" id="nieuw_wachtwoord" name="nieuw_wachtwoord" minlength="6" required>
                    </div>
                    <div class="form-group">
                        <label for="bevestig_wachtwoord">Bevestig nieuw wachtwoord:</label>
                        <input type="password" id="bevestig_wachtwoord" name="bevestig_wachtwoord" minlength="6" required>
                    </div>
                    <button type="submit" name="update_password" class="btn btn-primary">Wachtwoord wijzigen</button>
                </form>
            </div>

            <!-- Account verwijderen sectie -->
            <div class="section delete-section">
                <h3>🗑️   Account verwijderen</h3>
                <div class="warning-text">
                    ⚠️ Let op: Deze actie kan niet ongedaan gemaakt worden!
                </div>
                <form method="POST" onsubmit="return confirm('Weet je zeker dat je je account wilt verwijderen? Deze actie kan niet ongedaan gemaakt worden.');">
                    <div class="form-group">
                        <label for="bevestig_delete_wachtwoord">Bevestig met je wachtwoord:</label>
                        <input type="password" id="bevestig_delete_wachtwoord" name="bevestig_delete_wachtwoord" required>
                    </div>
                    <button type="submit" name="delete_account" class="btn btn-danger">Account permanent verwijderen</button>
                </form>
            </div>

            <a href="index.php" class="btn btn-secondary">← Terug naar shop</a>
        </div>
    </div>

    <script>
        // Wachtwoord bevestiging validatie
        document.getElementById('bevestig_wachtwoord').addEventListener('input', function() {
            const nieuwWachtwoord = document.getElementById('nieuw_wachtwoord').value;
            const bevestigWachtwoord = this.value;
            
            if (nieuwWachtwoord !== bevestigWachtwoord) {
                this.setCustomValidity('Wachtwoorden komen niet overeen');
            } else {
                this.setCustomValidity('');
            }
        });

        // Extra bevestiging voor account verwijderen
        document.querySelector('form[onsubmit]').addEventListener('submit', function(e) {
            const confirmation = prompt('Type "VERWIJDER" om te bevestigen dat je je account wilt verwijderen:');
            if (confirmation !== 'VERWIJDER') {
                e.preventDefault();
                alert('Account verwijdering geannuleerd.');
            }
        });
    </script>
</body>
</html>