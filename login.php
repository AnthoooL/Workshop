<?php
//error_reporting(E_ALL); // Activer l'affichage des erreurs
//ini_set('display_errors', 1); // Afficher les erreurs

session_start(); // Démarrer la session

// Informations de connexion à la base de données
$servername = "localhost";
$username = "root"; // Nom d'utilisateur par défaut pour XAMPP
$password = ""; // Mot de passe par défaut
$dbname = "social_network"; // Nom de la base de données

// Créer une connexion à MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion: " . $conn->connect_error);
}

// Initialiser les messages d'erreur
$errorMessage = "";

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer le nom d'utilisateur et le mot de passe
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Valider les informations d'identification
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    // Vérifier si l'utilisateur existe
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($userId, $hashedPassword);
        $stmt->fetch();

        // Vérifier le mot de passe (dans un scénario réel, tu devrais hacher les mots de passe lors de l'inscription)
        if ($password === $hashedPassword) { // Remplace par password_verify($password, $hashedPassword) si tu utilises un hash
            // Authentifier l'utilisateur
            $_SESSION['user_id'] = $userId; // Stocker l'ID de l'utilisateur dans la session
            header("Location: index.php"); // Rediriger vers index.php après connexion
            exit();
        } else {
            $errorMessage = "Mot de passe incorrect.";
        }
    } else {
        $errorMessage = "Nom d'utilisateur incorrect.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css"> <!-- Lien vers le fichier CSS -->
    <title>Connexion - EPSI</title>
    <style>
        /* Styles simples pour le formulaire */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        h2 {
            text-align: center;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .error {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Connexion</h2>
        <?php if ($errorMessage): ?>
            <div class="error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <input type="text" name="username" placeholder="Nom d'utilisateur" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit">Se connecter</button>
        </form>
    </div>
</body>
</html>
