<?php
// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "social_network");

if ($conn->connect_error) {
    die("Erreur de connexion: " . $conn->connect_error);
}

// Lire les données envoyées via fetch()
$data = json_decode(file_get_contents('php://input'), true);
$message_id = $data['message_id'];

// Vérifier si le message est déjà signalé par l'utilisateur actuel (pseudo ou user_id doit être géré avec des sessions)
$user_id = 1; // ID utilisateur à remplacer par celui de la session
$sql_check = "SELECT * FROM reports WHERE message_id = $message_id AND user_id = $user_id";
$result_check = $conn->query($sql_check);

if ($result_check->num_rows == 0) {
    // Insérer le signalement dans la base de données
    $sql = "INSERT INTO reports (message_id, user_id, report_reason, created_at) VALUES ($message_id, $user_id, 'Contenu inapproprié', NOW())";
    if ($conn->query($sql)) {
        echo "reported";
    } else {
        echo "Erreur: " . $conn->error;
    }
} else {
    echo "Ce message a déjà été signalé.";
}

// Fermer la connexion
$conn->close();
?>
