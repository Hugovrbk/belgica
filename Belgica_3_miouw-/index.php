<?php
require_once 'db.php';

// Fonction pour récupérer le prochain match
function getProchainMatch($pdo) {
    try {
        // Récupérer le match dont la date est >= maintenant, trié par date
        $sql = "SELECT * FROM matches WHERE date_match >= NOW() ORDER BY date_match ASC LIMIT 1";
        $stmt = $pdo->query($sql);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

// Fonction pour formater la date en français
function formatDateFr($dateString) {
    setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'fr');
    $date = new DateTime($dateString);
    $formatter = new IntlDateFormatter(
        'fr_FR',
        IntlDateFormatter::FULL,
        IntlDateFormatter::SHORT,
        'Europe/Brussels'
    );
    return $formatter->format($date);
}

// Récupérer le prochain match
$prochainMatch = getProchainMatch($pdo);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Belgica FC 3</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="header">
        <div class="logo"></div>
        <div class="navigation">
            <div class="joueurs">
                <a href="#" class="nav-btn">Joueurs</a>
            </div>
            <div class="calendrier">
                <a href="#" class="nav-btn">Calendrier</a>
            </div>
            <div class="news">
                <a href="#" class="nav-btn">News</a>
            </div>
            <div class="histoire">
                <a href="#" class="nav-btn">Histoire</a>
            </div>
        </div>
        <div class="compte">
            <a href="pages/compte/html/login.html" class="nav-btn btn-compte">Compte</a>
        </div>
    </div>

    <div class="center">
        <div class="prochain_match">
            <h2>Prochain Match</h2>
            <?php if ($prochainMatch): ?>
                <p>bellinzola vs <?php echo htmlspecialchars($prochainMatch['equipe_adversaire']); ?></p>
                <p>Stade: <?php echo htmlspecialchars($prochainMatch['stade']); ?></p>
                <p>Date: <?php echo formatDateFr($prochainMatch['date_match']); ?></p>
                <?php if (!empty($prochainMatch['competition'])): ?>
                    <p>Compétition: <?php echo htmlspecialchars($prochainMatch['competition']); ?></p>
                <?php endif; ?>
            <?php else: ?>
                <p>Aucun match programmé pour le moment</p>
                <p>Consultez le calendrier pour plus d'informations</p>
            <?php endif; ?>
        </div>
        
        <div class="news_1">
            <h2>Actualités</h2>
            <p>Résumé du dernier entraînement...</p>
        </div>
        
        <div class="classement">
            <h2>Classement</h2>
            <p>1. Belgica FC - 35 pts</p>
            <p>2. Anderlecht - 32 pts</p>
        </div>

        <div class="sponsor">
            <div class="sponsors">
                <h2>Sponsors</h2>
                <img src="multimedia/img/sponsor/10'_dieci.png" alt="Description de l'image">
                <img src="multimedia/img/sponsor/acerbis.png" alt="Description de l'image">
                <img src="multimedia/img/sponsor/club_dei_100.png" alt="Description de l'image">
                <img src="multimedia/img/sponsor/Goal_service.png" alt="Description de l'image">
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="réseaux">
            <a href="https://www.instagram.com/acbellinzona_official/">
                <img src="multimedia/img/resaux/instagram.png" alt="instagram">
            </a>
            <a href="https://www.tiktok.com/@acbellinzona.official">
                <img src="multimedia/img/resaux/tiktok.png" alt="tiktok">
            </a>
            <a href="https://x.com/ACB1904">
                <img src="multimedia/img/resaux/twitter.png" alt="twitter">
            </a>
            <a href="https://www.facebook.com/ACBellinzona1904">
                <img src="multimedia/img/resaux/facebook.png" alt="facebook">
            </a>
        </div>
    </div>
</body>
</html>
