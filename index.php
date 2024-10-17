<?php
session_start(); // Démarrer la session

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Rediriger vers la page de connexion si non connecté
    exit();
}

$userId = $_SESSION['user_id']; // Récupérer l'ID de l'utilisateur depuis la session

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

// Traitement du formulaire d'envoi de message
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $messageContent = $_POST['message'];

    // Remplacer par l'ID de l'utilisateur connecté (à définir)
    $userId = $_SESSION['user_id']; // Récupérer l'ID de l'utilisateur depuis la session

    // Insérer le message dans la base de données
    $stmt = $conn->prepare("INSERT INTO messages (user_id, content, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $userId, $messageContent);

    if ($stmt->execute()) {
        echo "<p>Message envoyé avec succès !</p>";
    } else {
        echo "<p>Erreur lors de l'envoi du message : " . $stmt->error . "</p>";
    }

    $stmt->close();
}


// Récupérer les messages depuis la base de données
$sql = "SELECT messages.id, messages.content, users.username, messages.created_at 
        FROM messages 
        JOIN users ON messages.user_id = users.id 
        ORDER BY messages.created_at DESC";

$result = $conn->query($sql);

$sql = "SELECT messages.id, messages.content, users.username, users.trust_score, messages.created_at 
        FROM messages 
        JOIN users ON messages.user_id = users.id 
        ORDER BY messages.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css"> <!-- Lien vers le fichier CSS -->
    <title>CLEANWAVE - Réseau Social</title>
</head>
<body>

    <!-- Ajout de l'image en haut à droite -->
    <img src="Clean.png" alt="Logo" class="logo">

    <div id="sidebar">
        <h2>CLEANWAVE</h2>
        <a href="#">Mes tweets</a>
        <a href="#">Actualités</a>
        <a href="#">MP</a>
        <a href="#">Mon compte</a>
    </div>



    <div id="content">^
        
        <h1>Messages</h1>

        <!-- Formulaire pour envoyer un message -->
        <form action="index.php" method="POST">
            <textarea name="message" placeholder="Écrire un message..." required></textarea>
            <button type="submit">Envoyer</button>
        </form>


        <?php
        if ($result->num_rows > 0) {
            // Afficher chaque message
            while ($row = $result->fetch_assoc()) {
                echo "<div class='message'>";
                echo "<div class='message-header'>";
                echo "<span class='message-user'>" . htmlspecialchars($row['username']) . " (Score de confiance: " . $row['trust_score'] . ")</span>";
                echo "<button class='report-btn' onclick='reportMessage(" . $row['id'] . ")'>Signaler</button>";
                echo "</div>";
                echo "<p class='message-content'>" . htmlspecialchars($row['content']) . "</p>";
                echo "<p class='message-date'>Posté le " . $row['created_at'] . "</p>";
                echo "<p class='report-message' id='report-" . $row['id'] . "' style='display:none;'>Message signalé</p>";
                echo "</div>";
            }
        } else {
            echo "<p>Aucun message trouvé.</p>";
        }
        ?>

    </div>

    <script>
        function reportMessage(messageId) {
            // Envoie une requête AJAX pour signaler un message
            fetch('report.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ message_id: messageId }),
            })
            .then(response => response.text())
            .then(data => {
                if (data === 'reported') {
                    document.getElementById('report-' + messageId).style.display = 'block';
                    const progressBar = document.getElementById('trust-score-' + userId);
                    const progressValue = document.getElementById('trust-score-value-' + userId);
                    
                    progressBar.value = data.new_trust_score;
                    progressValue.innerText = data.new_trust_score + "%";
                } else {
                    alert(data); // Afficher un message d'erreur
                }
            })
            .catch((error) => {
                console.error('Erreur:', error);
            });
        }
    </script>
</body>
</html>

<?php
// Fermer la connexion
$conn->close();
?>