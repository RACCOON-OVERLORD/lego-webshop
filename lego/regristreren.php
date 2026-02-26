<?php
require_once 'config.php';
require_once 'session.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naam = trim($_POST['naam'] ?? '');
    $achternaam = trim($_POST['achternaam'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $wachtwoord = $_POST['wachtwoord'] ?? '';
    $wachtwoord_bevestiging = $_POST['wachtwoord_bevestiging'] ?? '';
    
    // Valideer
    if (empty($naam)) $errors[] = 'Naam is verplicht';
    if (empty($achternaam)) $errors[] = 'Achternaam is verplicht';
    if (empty($email)) $errors[] = 'E-mail is verplicht';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Ongeldig e-mailadres';
    if (empty($wachtwoord)) $errors[] = 'Wachtwoord is verplicht';
    if (strlen($wachtwoord) < 8) $errors[] = 'Wachtwoord moet minimaal 8 tekens lang zijn';
    if ($wachtwoord !== $wachtwoord_bevestiging) $errors[] = 'Wachtwoorden komen niet overeen';
    
    // Email check
    $check_stmt = $mysqli->prepare("SELECT id FROM gebruikers WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    if ($result->fetch_assoc()) {
        $errors[] = 'E-mailadres is al in gebruik';
    }
    $check_stmt->close();
    
    if (empty($errors)) {
        $hashed_password = password_hash($wachtwoord, PASSWORD_DEFAULT);
        
        $insert_stmt = $mysqli->prepare("INSERT INTO gebruikers (naam, achternaam, email, wachtwoord) VALUES (?, ?, ?, ?)");
        $insert_stmt->bind_param("ssss", $naam, $achternaam, $email, $hashed_password);
        $insert_stmt->execute();
        
        // Login
        $user = [
            'id' => $mysqli->insert_id,
            'naam' => $naam,
            'achternaam' => $achternaam,
            'email' => $email
        ];
        loginUser($user);
        
        $insert_stmt->close();
        header('Location: index.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Registreren - LEGO Shop</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>
    <header class="header">
        <div class="header-container">
            <a href="index.php" class="logo">LEGO Shop</a>
            <div class="user-info">
                <a href="inlogen.php">Inloggen</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1>Account aanmaken</h1>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label" for="naam">Voornaam</label>
                        <input type="text" class="form-control" id="naam" name="naam" value="<?= htmlspecialchars($_POST['naam'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="achternaam">Achternaam</label>
                        <input type="text" class="form-control" id="achternaam" name="achternaam" value="<?= htmlspecialchars($_POST['achternaam'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="email">E-mailadres</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="wachtwoord">Wachtwoord</label>
                        <input type="password" class="form-control" id="wachtwoord" name="wachtwoord" required>
                        <small>Minimaal 8 tekens</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="wachtwoord_bevestiging">Wachtwoord bevestigen</label>
                        <input type="password" class="form-control" id="wachtwoord_bevestiging" name="wachtwoord_bevestiging" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Account aanmaken</button>
                </form>
                
                <div class="text-center mt-3">
                    <p>Al een account? <a href="inlogen.php">Inloggen</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>