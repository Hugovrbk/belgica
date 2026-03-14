<?php
session_start();
require_once 'db.php';

// Prochain match
function getProchainMatch($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM matches WHERE date_match >= NOW() ORDER BY date_match ASC LIMIT 1");
        return $stmt->fetch();
    } catch (PDOException $e) { return null; }
}

// Classement (5 premiers)
function getClassement($pdo, $limit = 5) {
    $classement = [];
    try {
        $equipes = $pdo->query("SELECT nom FROM equipes ORDER BY nom ASC")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($equipes as $eq) {
            $classement[$eq] = ['pts'=>0,'j'=>0,'v'=>0,'n'=>0,'d'=>0,'bp'=>0,'bc'=>0,'diff'=>0];
        }
        $resultats = $pdo->query("SELECT * FROM resultats")->fetchAll();
        foreach ($resultats as $r) {
            $dom = $r['equipe_domicile']; $ext = $r['equipe_exterieur'];
            $bd = $r['buts_domicile'];   $be = $r['buts_exterieur'];
            if (!isset($classement[$dom])) $classement[$dom] = ['pts'=>0,'j'=>0,'v'=>0,'n'=>0,'d'=>0,'bp'=>0,'bc'=>0,'diff'=>0];
            if (!isset($classement[$ext])) $classement[$ext] = ['pts'=>0,'j'=>0,'v'=>0,'n'=>0,'d'=>0,'bp'=>0,'bc'=>0,'diff'=>0];
            $classement[$dom]['j']++; $classement[$ext]['j']++;
            $classement[$dom]['bp'] += $bd; $classement[$dom]['bc'] += $be;
            $classement[$ext]['bp'] += $be; $classement[$ext]['bc'] += $bd;
            if ($bd > $be)      { $classement[$dom]['v']++; $classement[$dom]['pts'] += 3; $classement[$ext]['d']++; }
            elseif ($bd == $be) { $classement[$dom]['n']++; $classement[$dom]['pts']++; $classement[$ext]['n']++; $classement[$ext]['pts']++; }
            else                { $classement[$ext]['v']++; $classement[$ext]['pts'] += 3; $classement[$dom]['d']++; }
        }
        foreach ($classement as &$s) { $s['diff'] = $s['bp'] - $s['bc']; } unset($s);
        uasort($classement, function($a,$b) {
            if ($b['pts'] !== $a['pts']) return $b['pts'] - $a['pts'];
            if ($b['diff'] !== $a['diff']) return $b['diff'] - $a['diff'];
            return $b['bp'] - $a['bp'];
        });
        return array_slice($classement, 0, $limit, true);
    } catch (PDOException $e) { return []; }
}

$prochainMatch = getProchainMatch($pdo);
$classement    = getClassement($pdo, 6);

// Format date FR
function formatDateFr($dateString) {
    try {
        $date = new DateTime($dateString);
        $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::SHORT, 'Europe/Brussels');
        return $formatter->format($date);
    } catch (Exception $e) { return $dateString; }
}

function formatTime($dateString) {
    try {
        $date = new DateTime($dateString);
        return $date->format('H:i');
    } catch (Exception $e) { return ''; }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFC Liège — Site Officiel</title>
    <!-- Fonts non-bloquantes (fix chargement infini) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:ital,wght@0,400;0,600;0,700;0,900;1,400&family=Barlow+Condensed:wght@400;600;700;900&display=swap" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:ital,wght@0,400;0,600;0,700;0,900;1,400&family=Barlow+Condensed:wght@400;600;700;900&display=swap"></noscript>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- ══ HEADER ═══════════════════════════════════════════════════ -->
<header class="header">
    <a href="index.php" class="logo-wrap">
        <div class="logo-img">
            <img src="multimedia/img/logo/rfcl-logo.png" alt="RFC Liège" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
            <div style="display:none;width:48px;height:48px;background:var(--rouge);border-radius:50%;align-items:center;justify-content:center;font-family:'Bebas Neue',sans-serif;font-size:1.2rem;color:#fff;font-weight:900;">RFCL</div>
        </div>
        <div class="logo-text">
            <span class="club-name">RFC Liège</span>
            <span class="club-sub">Matricule 4 · 1892</span>
        </div>
    </a>

    <nav class="nav-desktop">
        <a href="#" class="nav-btn active">Accueil</a>
        <a href="#" class="nav-btn">Joueurs</a>
        <a href="#" class="nav-btn">Calendrier</a>
        <a href="#" class="nav-btn">Résultats</a>
        <a href="#" class="nav-btn">News</a>
        <a href="#" class="nav-btn">Histoire</a>
    </nav>

    <div style="display:flex;align-items:center;gap:10px;">
        <?php if (isset($_SESSION['user_id'])): ?>
            <span style="font-size:0.85rem;color:rgba(255,255,255,0.6);">👋 <?= htmlspecialchars($_SESSION['username']) ?></span>
            <?php if (isset($_SESSION['user_admin']) && $_SESSION['user_admin'] == 1): ?>
                <a href="pages/admin/html/admin.php" class="btn-compte" style="background:var(--or);color:var(--navy);">Admin</a>
            <?php endif; ?>
            <a href="pages/compte/php/logout.php" class="btn-compte" style="background:transparent;border:1px solid rgba(255,255,255,0.3);">Déconnexion</a>
        <?php else: ?>
            <a href="pages/compte/html/login.html" class="btn-compte">Connexion</a>
        <?php endif; ?>
        <button class="hamburger" id="hamburger" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>

<!-- Mobile nav -->
<nav class="nav-mobile" id="navMobile">
    <a href="#" class="nav-btn">Accueil</a>
    <a href="#" class="nav-btn">Joueurs</a>
    <a href="#" class="nav-btn">Calendrier</a>
    <a href="#" class="nav-btn">Résultats</a>
    <a href="#" class="nav-btn">News</a>
    <a href="#" class="nav-btn">Histoire</a>
    <a href="pages/compte/html/login.html" class="btn-compte">Connexion</a>
</nav>

<!-- ══ HERO ════════════════════════════════════════════════════ -->
<section class="hero">
    <div class="hero-content">
        <div class="hero-badge">Challenger Pro League · Saison 2025–26</div>
        <h1>RFC<br><span>Liège</span></h1>
        <p>Sang &amp; Marine depuis 1892. Le Royal Football Club de Liège, Matricule 4, est le doyen du football belge.</p>
        <div class="hero-cta">
            <a href="#prochain-match" class="btn-primary">Prochain match</a>
            <a href="#classement" class="btn-secondary">Classement</a>
        </div>
    </div>

    <!-- Ticker -->
    <div class="score-ticker">
        <div class="ticker-label">En direct</div>
        <div class="ticker-scroll">
            <div class="ticker-track">
                <?php
                // Afficher les derniers résultats dans le ticker
                try {
                    $derniers = $pdo->query("SELECT * FROM resultats ORDER BY id DESC LIMIT 5")->fetchAll();
                    $ticker_items = '';
                    foreach ($derniers as $r) {
                        $ticker_items .= '<div class="ticker-item">'
                            . htmlspecialchars($r['equipe_domicile'])
                            . ' <span class="score-badge">' . $r['buts_domicile'] . ' – ' . $r['buts_exterieur'] . '</span> '
                            . htmlspecialchars($r['equipe_exterieur'])
                            . '</div>';
                    }
                    if (empty($ticker_items)) {
                        $ticker_items = '<div class="ticker-item">Bienvenue au RFC Liège — Saison 2025–2026</div><div class="ticker-item">Suivez tous les résultats ici</div>';
                    }
                    // Doubler pour l'animation infinie
                    echo $ticker_items . $ticker_items;
                } catch (PDOException $e) {
                    echo '<div class="ticker-item">RFC Liège — Saison 2025–2026</div><div class="ticker-item">RFC Liège — Saison 2025–2026</div>';
                }
                ?>
            </div>
        </div>
    </div>
</section>

<!-- ══ PROCHAIN MATCH ══════════════════════════════════════════ -->
<section class="section-match" id="prochain-match">
    <div class="section-header" style="max-width:860px;margin:0 auto 28px;">
        <div>
            <div class="section-label" style="color:rgba(255,255,255,0.5);">Agenda</div>
            <div class="section-title" style="color:var(--blanc);">Prochain Match</div>
        </div>
        <a href="#" class="section-link" style="color:rgba(255,255,255,0.5);">Voir tout le calendrier →</a>
    </div>

    <?php if ($prochainMatch): ?>
    <div class="match-card">
        <div class="match-header">
            <span class="match-competition">
                <?= !empty($prochainMatch['competition']) ? '🏆 ' . htmlspecialchars($prochainMatch['competition']) : '⚽ Match officiel' ?>
            </span>
            <span class="match-date-pill">📅 <?= formatDateFr($prochainMatch['date_match']) ?></span>
        </div>
        <div class="match-body">
            <div class="match-team">
                <div class="match-team-logo" style="background:rgba(200,16,46,0.2);border-color:rgba(200,16,46,0.4);">
                    <span style="font-family:'Bebas Neue',sans-serif;font-size:1.5rem;color:var(--rouge);">BFC</span>
                </div>
                <div>
                    <div class="match-team-name">RFC Liège</div>
                    <div class="match-team-type">Domicile</div>
                </div>
            </div>
            <div class="match-vs">
                <div class="vs-text">VS</div>
                <div class="match-time"><?= formatTime($prochainMatch['date_match']) ?></div>
            </div>
            <div class="match-team">
                <div class="match-team-logo">
                    <span style="font-size:1.5rem;">⚽</span>
                </div>
                <div>
                    <div class="match-team-name"><?= htmlspecialchars($prochainMatch['equipe_adversaire']) ?></div>
                    <div class="match-team-type">Visiteur</div>
                </div>
            </div>
        </div>
        <div class="match-footer">
            <div class="match-venue">
                <span>📍</span>
                <span><?= htmlspecialchars($prochainMatch['stade']) ?></span>
            </div>
            <a href="#" class="btn-primary" style="font-size:0.82rem;padding:9px 20px;">🎫 Billetterie</a>
        </div>
    </div>
    <?php else: ?>
    <div class="match-card">
        <div class="no-match">
            <p>Aucun match programmé pour le moment.</p>
            <p style="margin-top:8px;">Revenez bientôt pour suivre l'agenda de l'équipe.</p>
        </div>
    </div>
    <?php endif; ?>
</section>

<!-- ══ ACTUALITÉS ══════════════════════════════════════════════ -->
<section class="section-news main">
    <div class="section-header">
        <div>
            <div class="section-label">À la une</div>
            <div class="section-title">Actualités</div>
        </div>
        <a href="#" class="section-link">Toutes les news →</a>
    </div>

    <div class="news-grid">
        <article class="news-card">
            <div class="news-card-img">🏆</div>
            <div class="news-card-body">
                <div class="news-tag">Équipe première</div>
                <div class="news-card-title">Victoire convaincante lors de la dernière journée</div>
                <div class="news-card-desc">L'équipe réalise un match solide et s'impose face à son adversaire grâce à une performance collective de haut niveau.</div>
                <div class="news-meta">
                    <span class="news-date">7 mars 2026</span>
                    <span class="news-read">Lire →</span>
                </div>
            </div>
        </article>
        <article class="news-card">
            <div class="news-card-img">⚽</div>
            <div class="news-card-body">
                <div class="news-tag">Transferts</div>
                <div class="news-card-title">Nouveau renfort pour renforcer l'effectif</div>
                <div class="news-card-desc">Le club annonce l'arrivée d'un joueur expérimenté pour la deuxième moitié de saison.</div>
                <div class="news-meta">
                    <span class="news-date">5 mars 2026</span>
                    <span class="news-read">Lire →</span>
                </div>
            </div>
        </article>
        <article class="news-card">
            <div class="news-card-img">🎽</div>
            <div class="news-card-body">
                <div class="news-tag">Club</div>
                <div class="news-card-title">Nouveau maillot domicile dévoilé pour la saison prochaine</div>
                <div class="news-card-desc">Le club présente le futur maillot dans les couleurs traditionnelles du RFC Liège.</div>
                <div class="news-meta">
                    <span class="news-date">2 mars 2026</span>
                    <span class="news-read">Lire →</span>
                </div>
            </div>
        </article>
    </div>
</section>

<!-- ══ CLASSEMENT ══════════════════════════════════════════════ -->
<section class="section-classement main" id="classement">
    <div class="section-header">
        <div>
            <div class="section-label">Championnat</div>
            <div class="section-title">Classement</div>
        </div>
        <a href="#" class="section-link">Classement complet →</a>
    </div>

    <?php if (!empty($classement)): ?>
    <div class="classement-table-wrap">
        <table class="classement-table">
            <thead>
                <tr>
                    <th class="col-pos">#</th>
                    <th class="col-nom">Équipe</th>
                    <th>J</th>
                    <th>V</th>
                    <th>N</th>
                    <th>D</th>
                    <th>DIFF</th>
                    <th class="col-pts">PTS</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $pos = 1;
                foreach ($classement as $nom => $s):
                    $is_b = stripos($nom, 'RFC Liège') !== false;
                    $diff_str = $s['diff'] > 0 ? '+' . $s['diff'] : $s['diff'];
                    $badge_class = $pos === 1 ? 'gold' : ($pos === 2 ? 'silver' : ($pos === 3 ? 'bronze' : 'other'));
                ?>
                <tr <?= $is_b ? 'class="row-rfcliege"' : '' ?>>
                    <td><span class="badge-pos <?= $badge_class ?>"><?= $pos ?></span></td>
                    <td class="col-nom">
                        <?= $is_b ? '<span style="color:var(--rouge);margin-right:4px;">⚽</span>' : '' ?>
                        <?= htmlspecialchars($nom) ?>
                    </td>
                    <td><?= $s['j'] ?></td>
                    <td><?= $s['v'] ?></td>
                    <td><?= $s['n'] ?></td>
                    <td><?= $s['d'] ?></td>
                    <td class="<?= $s['diff'] > 0 ? 'diff-pos' : ($s['diff'] < 0 ? 'diff-neg' : '') ?>"><?= $diff_str ?></td>
                    <td class="pts-cell"><?= $s['pts'] ?></td>
                </tr>
                <?php $pos++; endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p style="text-align:center;color:var(--texte-light);padding:40px;font-style:italic;">Aucune donnée de classement disponible.</p>
    <?php endif; ?>
</section>

<!-- ══ SPONSORS ════════════════════════════════════════════════ -->
<section class="section-sponsors main">
    <div style="text-align:center;">
        <div class="section-label" style="text-align:center;">Partenaires officiels</div>
        <div class="section-title" style="text-align:center;">Nos Sponsors</div>
    </div>
    <div class="sponsors-grid">
        <div class="sponsor-item">
            <img src="multimedia/img/sponsor/10'_dieci.png" alt="Dieci" onerror="this.style.display='none'">
        </div>
        <div class="sponsor-item">
            <img src="multimedia/img/sponsor/acerbis.png" alt="Acerbis" onerror="this.style.display='none'">
        </div>
        <div class="sponsor-item">
            <img src="multimedia/img/sponsor/club_dei_100.png" alt="Club dei 100" onerror="this.style.display='none'">
        </div>
        <div class="sponsor-item">
            <img src="multimedia/img/sponsor/Goal_service.png" alt="Goal Service" onerror="this.style.display='none'">
        </div>
    </div>
</section>

<!-- ══ FOOTER ══════════════════════════════════════════════════ -->
<footer class="footer">
    <div class="footer-grid">
        <div class="footer-brand">
            <div class="club-name">RFC Liège</div>
            <p>Fondé en 1892, le Royal Football Club de Liège (Matricule 4) est le doyen du football belge. Sang et Marine depuis toujours.</p>
            <div class="footer-social">
                <a href="https://www.instagram.com" class="social-btn" aria-label="Instagram">IG</a>
                <a href="https://www.facebook.com" class="social-btn" aria-label="Facebook">f</a>
                <a href="https://www.tiktok.com" class="social-btn" aria-label="TikTok">🎵</a>
                <a href="https://x.com" class="social-btn" aria-label="Twitter">𝕏</a>
            </div>
        </div>
        <div class="footer-col">
            <h4>Club</h4>
            <ul>
                <li><a href="#">Histoire</a></li>
                <li><a href="#">Organigramme</a></li>
                <li><a href="#">Stade</a></li>
                <li><a href="#">Partenaires</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Équipe</h4>
            <ul>
                <li><a href="#">Joueurs</a></li>
                <li><a href="#">Staff</a></li>
                <li><a href="#">Calendrier</a></li>
                <li><a href="#">Résultats</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Supporters</h4>
            <ul>
                <li><a href="#">Fanshop</a></li>
                <li><a href="#">Billetterie</a></li>
                <li><a href="#">Newsletter</a></li>
                <li><a href="pages/compte/html/login.html">Espace membre</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© 2026 RFC Liège — Tous droits réservés</p>
        <div style="display:flex;gap:20px;">
            <a href="#">Mentions légales</a>
            <a href="#">Politique de confidentialité</a>
            <a href="pages/admin/html/admin.php">Admin</a>
        </div>
    </div>
</footer>

<script>
const hamburger = document.getElementById('hamburger');
const navMobile = document.getElementById('navMobile');
hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('open');
    navMobile.classList.toggle('open');
});
</script>
</body>
</html>