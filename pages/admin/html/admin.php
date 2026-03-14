<?php
session_start();
require_once '../../../db.php';
require_once 'check_admin.php';

// ── Classement ─────────────────────────────────────────────────
$classement = [];
try {
    $toutes_equipes = $pdo->query("SELECT nom FROM equipes ORDER BY nom ASC")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($toutes_equipes as $eq) {
        $classement[$eq] = ['pts'=>0,'j'=>0,'v'=>0,'n'=>0,'d'=>0,'bp'=>0,'bc'=>0,'diff'=>0];
    }
    foreach ($pdo->query("SELECT * FROM resultats ORDER BY journee ASC")->fetchAll() as $r) {
        $dom = $r['equipe_domicile']; $ext = $r['equipe_exterieur'];
        $bd  = $r['buts_domicile'];  $be  = $r['buts_exterieur'];
        if (!isset($classement[$dom])) $classement[$dom] = ['pts'=>0,'j'=>0,'v'=>0,'n'=>0,'d'=>0,'bp'=>0,'bc'=>0,'diff'=>0];
        if (!isset($classement[$ext])) $classement[$ext] = ['pts'=>0,'j'=>0,'v'=>0,'n'=>0,'d'=>0,'bp'=>0,'bc'=>0,'diff'=>0];
        $classement[$dom]['j']++; $classement[$ext]['j']++;
        $classement[$dom]['bp'] += $bd; $classement[$dom]['bc'] += $be;
        $classement[$ext]['bp'] += $be; $classement[$ext]['bc'] += $bd;
        if ($bd > $be)       { $classement[$dom]['v']++; $classement[$dom]['pts'] += 3; $classement[$ext]['d']++; }
        elseif ($bd === $be) { $classement[$dom]['n']++; $classement[$dom]['pts']++; $classement[$ext]['n']++; $classement[$ext]['pts']++; }
        else                 { $classement[$ext]['v']++; $classement[$ext]['pts'] += 3; $classement[$dom]['d']++; }
    }
    foreach ($classement as &$s) { $s['diff'] = $s['bp'] - $s['bc']; } unset($s);
    uasort($classement, function($a,$b) {
        if ($b['pts'] !== $a['pts']) return $b['pts'] - $a['pts'];
        if ($b['diff'] !== $a['diff']) return $b['diff'] - $a['diff'];
        return $b['bp'] - $a['bp'];
    });
} catch (PDOException $e) { $classement = []; }

// Stats
$nb_equipes = count($toutes_equipes ?? []);
try { $nb_matchs   = $pdo->query("SELECT COUNT(*) FROM matches WHERE date_match >= NOW()")->fetchColumn(); } catch(PDOException $e) { $nb_matchs = 0; }
try { $nb_resultats = $pdo->query("SELECT COUNT(*) FROM resultats")->fetchColumn(); } catch(PDOException $e) { $nb_resultats = 0; }
try { $nb_users    = $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn(); } catch(PDOException $e) { $nb_users = 0; }

// Équipes pour selects
try { $eq_list = $pdo->query("SELECT nom FROM equipes ORDER BY nom ASC")->fetchAll(PDO::FETCH_COLUMN); }
catch (PDOException $e) { $eq_list = []; }

// Format date
function formatDateAdmin($dateString) {
    try {
        $d = new DateTime($dateString);
        $f = new IntlDateFormatter('fr_FR', IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT, 'Europe/Brussels');
        return $f->format($d);
    } catch(Exception $e) { return $dateString; }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — RFC Liège</title>
    <link rel="stylesheet" href="../../../css/admin_styles.css">
</head>
<body>

<div class="admin-layout">

    <!-- ══ SIDEBAR ════════════════════════════════════════════ -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <div class="logo-symbol">B</div>
            <div class="logo-txt">
                <span>RFC Liège</span>
                <span>Administration</span>
            </div>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-title">Navigation</div>
            <a href="#section-match"   class="sidebar-link active"><span class="link-icon">📅</span> Matchs</a>
            <a href="#section-equipes" class="sidebar-link"><span class="link-icon">🏟</span> Équipes</a>
            <a href="#section-result"  class="sidebar-link"><span class="link-icon">⚽</span> Résultats</a>
            <a href="#section-rank"    class="sidebar-link"><span class="link-icon">🏆</span> Classement</a>

            <div class="sidebar-section-title">Site</div>
            <a href="../../../index.php" class="sidebar-link"><span class="link-icon">🌐</span> Voir le site</a>
        </div>

        <div class="sidebar-footer">
            <div class="user-pill">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)) ?></div>
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></div>
                    <div class="user-role">Administrateur</div>
                </div>
            </div>
            <a href="../../../pages/compte/php/logout.php" class="btn-logout">⏻ Déconnexion</a>
        </div>
    </aside>

    <!-- ══ MAIN ═══════════════════════════════════════════════ -->
    <div class="admin-main">

        <!-- Topbar -->
        <div class="admin-topbar">
            <div class="topbar-title">Tableau de bord</div>
            <div class="topbar-right">
                <a href="../../../index.php" class="topbar-site-link">🌐 Voir le site</a>
            </div>
        </div>

        <!-- Content -->
        <div class="admin-content">

            <!-- Flash message -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="flash-msg <?= htmlspecialchars($_SESSION['message_type']) ?>">
                    <?= $_SESSION['message_type'] === 'success' ? '✅' : '❌' ?>
                    <?= htmlspecialchars($_SESSION['message']) ?>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>

            <!-- Stats row -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon">🏟</div>
                    <div class="stat-value"><?= $nb_equipes ?></div>
                    <div class="stat-label">Équipes</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-icon">📅</div>
                    <div class="stat-value"><?= $nb_matchs ?></div>
                    <div class="stat-label">Matchs à venir</div>
                </div>
                <div class="stat-card gold">
                    <div class="stat-icon">⚽</div>
                    <div class="stat-value"><?= $nb_resultats ?></div>
                    <div class="stat-label">Résultats</div>
                </div>
                <div class="stat-card navy">
                    <div class="stat-icon">👤</div>
                    <div class="stat-value"><?= $nb_users ?></div>
                    <div class="stat-label">Membres</div>
                </div>
            </div>

            <!-- ╔══════════════════════════════════════════════╗ -->
            <!-- ║  SECTION 1 — MATCHS                          ║ -->
            <!-- ╚══════════════════════════════════════════════╝ -->
            <div class="section-block" id="section-match">
                <div class="section-block-header">
                    <h2><span class="icon">📅</span> Gestion des Matchs</h2>
                </div>
                <div class="section-block-body">
                    <form action="../php/save_match.php" method="POST" class="admin-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="equipe_adversaire">Équipe adversaire *</label>
                                <input type="text" id="equipe_adversaire" name="equipe_adversaire" required placeholder="Ex: FC Bruxelles">
                            </div>
                            <div class="form-group">
                                <label for="stade">Stade *</label>
                                <input type="text" id="stade" name="stade" required placeholder="Ex: Terrain Municipal 1">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="date_match">Date &amp; heure *</label>
                                <input type="datetime-local" id="date_match" name="date_match" required>
                            </div>
                            <div class="form-group">
                                <label for="competition">Compétition</label>
                                <input type="text" id="competition" name="competition" placeholder="Ex: Championnat D3, Coupe…">
                            </div>
                        </div>
                        <button type="submit" class="btn-submit">➕ Enregistrer le match</button>
                    </form>

                    <!-- Matchs programmés -->
                    <div>
                        <div style="font-size:0.72rem;font-weight:900;letter-spacing:0.2em;text-transform:uppercase;color:rgba(255,255,255,0.35);margin-bottom:12px;">
                            Matchs programmés
                        </div>
                        <div class="items-list">
                        <?php
                        try {
                            $matches = $pdo->query("SELECT * FROM matches WHERE date_match >= NOW() ORDER BY date_match ASC")->fetchAll();
                            if (count($matches) > 0) {
                                foreach ($matches as $m) {
                                    echo '<div class="match-item">';
                                    echo '<div class="match-item-info">';
                                    echo '<div class="match-item-title">RFC Liège vs ' . htmlspecialchars($m['equipe_adversaire']) . '</div>';
                                    echo '<div class="match-item-meta">';
                                    echo '<span class="meta-pill">📍 ' . htmlspecialchars($m['stade']) . '</span>';
                                    echo '<span class="meta-pill">📅 ' . formatDateAdmin($m['date_match']) . '</span>';
                                    if (!empty($m['competition'])) echo '<span class="meta-pill">🏆 ' . htmlspecialchars($m['competition']) . '</span>';
                                    echo '</div>';
                                    echo '</div>';
                                    echo '<form action="../php/delete_match.php" method="POST">';
                                    echo '<input type="hidden" name="match_id" value="' . $m['id'] . '">';
                                    echo '<button type="submit" class="btn-delete" onclick="return confirm(\'Supprimer ce match ?\')">🗑 Suppr.</button>';
                                    echo '</form>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<p class="empty-msg">Aucun match programmé.</p>';
                            }
                        } catch (PDOException $e) {
                            echo '<p class="empty-msg">Erreur de chargement.</p>';
                        }
                        ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ╔══════════════════════════════════════════════╗ -->
            <!-- ║  SECTION 2 — ÉQUIPES                         ║ -->
            <!-- ╚══════════════════════════════════════════════╝ -->
            <div class="section-block" id="section-equipes">
                <div class="section-block-header">
                    <h2><span class="icon">🏟</span> Gestion des Équipes</h2>
                </div>
                <div class="section-block-body">
                    <form action="../php/manage_teams.php" method="POST" class="admin-form">
                        <input type="hidden" name="action" value="add">
                        <div class="form-row" style="align-items:flex-end;">
                            <div class="form-group">
                                <label for="nom_equipe">Nom de la nouvelle équipe *</label>
                                <input type="text" id="nom_equipe" name="nom_equipe" required placeholder="Ex: FC Anderlecht">
                            </div>
                            <div style="padding-bottom:0;">
                                <button type="submit" class="btn-add-inline">➕ Ajouter l'équipe</button>
                            </div>
                        </div>
                    </form>

                    <div>
                        <div style="font-size:0.72rem;font-weight:900;letter-spacing:0.2em;text-transform:uppercase;color:rgba(255,255,255,0.35);margin-bottom:10px;">
                            Équipes inscrites
                        </div>
                        <?php
                        try {
                            $equipes = $pdo->query("SELECT * FROM equipes ORDER BY nom ASC")->fetchAll();
                            if (count($equipes) > 0) {
                                echo '<div class="teams-grid">';
                                foreach ($equipes as $eq) {
                                    echo '<div class="team-chip">';
                                    echo '<span class="team-chip-name">' . htmlspecialchars($eq['nom']) . '</span>';
                                    echo '<form action="../php/manage_teams.php" method="POST" style="height:100%;">';
                                    echo '<input type="hidden" name="action" value="delete">';
                                    echo '<input type="hidden" name="equipe_id" value="' . $eq['id'] . '">';
                                    echo '<button type="submit" class="chip-del" onclick="return confirm(\'Supprimer ' . htmlspecialchars($eq['nom'], ENT_QUOTES) . ' ?\')">✕</button>';
                                    echo '</form>';
                                    echo '</div>';
                                }
                                echo '</div>';
                            } else {
                                echo '<p class="empty-msg">Aucune équipe enregistrée.</p>';
                            }
                        } catch (PDOException $e) {
                            echo '<p class="empty-msg">Erreur de chargement.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- ╔══════════════════════════════════════════════╗ -->
            <!-- ║  SECTION 3 — RÉSULTATS                       ║ -->
            <!-- ╚══════════════════════════════════════════════╝ -->
            <div class="section-block" id="section-result">
                <div class="section-block-header">
                    <h2><span class="icon">⚽</span> Saisir un Résultat</h2>
                </div>
                <div class="section-block-body">
                    <form action="../php/save_result.php" method="POST" class="admin-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="journee">Journée *</label>
                                <input type="number" id="journee" name="journee" min="1" max="50" required placeholder="1">
                            </div>
                            <div class="form-group">
                                <label for="date_match_result">Date du match</label>
                                <input type="date" id="date_match_result" name="date_match">
                            </div>
                        </div>

                        <div class="score-row-form">
                            <div class="form-group">
                                <label for="equipe_domicile">Équipe Domicile *</label>
                                <select id="equipe_domicile" name="equipe_domicile" required>
                                    <option value="" disabled selected>-- Choisir --</option>
                                    <?php foreach ($eq_list as $n): ?>
                                        <option value="<?= htmlspecialchars($n) ?>"><?= htmlspecialchars($n) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="score-inputs-pair">
                                <label>Score</label>
                                <div class="score-pair">
                                    <input type="number" name="buts_domicile" min="0" max="99" value="0" class="score-input form-group input" required>
                                    <span class="score-sep">–</span>
                                    <input type="number" name="buts_exterieur" min="0" max="99" value="0" class="score-input form-group input" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="equipe_exterieur">Équipe Extérieur *</label>
                                <select id="equipe_exterieur" name="equipe_exterieur" required>
                                    <option value="" disabled selected>-- Choisir --</option>
                                    <?php foreach ($eq_list as $n): ?>
                                        <option value="<?= htmlspecialchars($n) ?>"><?= htmlspecialchars($n) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit full">💾 Enregistrer le résultat</button>
                    </form>

                    <!-- Historique -->
                    <div>
                        <div style="font-size:0.72rem;font-weight:900;letter-spacing:0.2em;text-transform:uppercase;color:rgba(255,255,255,0.35);margin-bottom:12px;">
                            Résultats enregistrés
                        </div>
                        <?php
                        try {
                            $resultats = $pdo->query("SELECT * FROM resultats ORDER BY journee ASC, id ASC")->fetchAll();
                            if (count($resultats) > 0) {
                                $current_j = null;
                                foreach ($resultats as $r) {
                                    if ($r['journee'] !== $current_j) {
                                        if ($current_j !== null) echo '</div>';
                                        echo '<div class="journee-group">';
                                        echo '<div class="journee-title">Journée ' . $r['journee'] . '</div>';
                                        $current_j = $r['journee'];
                                    }
                                    $dom = htmlspecialchars($r['equipe_domicile']);
                                    $ext = htmlspecialchars($r['equipe_exterieur']);
                                    $bd = $r['buts_domicile']; $be = $r['buts_exterieur'];
                                    $w_dom = $bd > $be ? 'winner' : ($bd < $be ? 'loser' : 'draw');
                                    $w_ext = $be > $bd ? 'winner' : ($be < $bd ? 'loser' : 'draw');
                                    echo '<div class="result-item">';
                                    echo '<span class="r-team r-dom ' . $w_dom . '">' . $dom . '</span>';
                                    echo '<span class="r-score">' . $bd . ' – ' . $be . '</span>';
                                    echo '<span class="r-team r-ext ' . $w_ext . '">' . $ext . '</span>';
                                    echo '<form action="../php/delete_result.php" method="POST">';
                                    echo '<input type="hidden" name="result_id" value="' . $r['id'] . '">';
                                    echo '<button type="submit" class="btn-delete" onclick="return confirm(\'Supprimer ?\')">🗑</button>';
                                    echo '</form>';
                                    echo '</div>';
                                }
                                echo '</div>';
                            } else {
                                echo '<p class="empty-msg">Aucun résultat enregistré.</p>';
                            }
                        } catch (PDOException $e) {
                            echo '<p class="empty-msg">Erreur de chargement.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- ╔══════════════════════════════════════════════╗ -->
            <!-- ║  SECTION 4 — CLASSEMENT                      ║ -->
            <!-- ╚══════════════════════════════════════════════╝ -->
            <div class="section-block" id="section-rank">
                <div class="section-block-header">
                    <h2><span class="icon">🏆</span> Classement du Championnat</h2>
                </div>
                <div class="section-block-body">
                    <?php if (!empty($classement)): ?>
                    <div class="classement-wrapper">
                        <table class="classement-table">
                            <thead>
                                <tr>
                                    <th class="col-pos">#</th>
                                    <th class="col-nom">Équipe</th>
                                    <th>J</th>
                                    <th>V</th>
                                    <th>N</th>
                                    <th>D</th>
                                    <th>BP</th>
                                    <th>BC</th>
                                    <th>DIFF</th>
                                    <th>PTS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $pos = 1;
                                foreach ($classement as $nom => $s):
                                    $is_b = stripos($nom, 'RFC Liège') !== false;
                                    $diff_str = $s['diff'] > 0 ? '+' . $s['diff'] : $s['diff'];
                                    $badge_c = $pos === 1 ? 'gold' : ($pos === 2 ? 'silver' : ($pos === 3 ? 'bronze' : 'other'));
                                ?>
                                <tr <?= $is_b ? 'class="row-rfcliege"' : '' ?>>
                                    <td><span class="badge-pos <?= $badge_c ?>"><?= $pos ?></span></td>
                                    <td class="col-nom">
                                        <?= $is_b ? '<span style="color:var(--rouge);margin-right:4px;">⚽</span>' : '' ?>
                                        <?= htmlspecialchars($nom) ?>
                                    </td>
                                    <td><?= $s['j'] ?></td>
                                    <td><?= $s['v'] ?></td>
                                    <td><?= $s['n'] ?></td>
                                    <td><?= $s['d'] ?></td>
                                    <td><?= $s['bp'] ?></td>
                                    <td><?= $s['bc'] ?></td>
                                    <td class="<?= $s['diff'] > 0 ? 'diff-pos' : ($s['diff'] < 0 ? 'diff-neg' : '') ?>"><?= $diff_str ?></td>
                                    <td class="pts-cell"><strong><?= $s['pts'] ?></strong></td>
                                </tr>
                                <?php $pos++; endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <p class="empty-msg">Aucune donnée disponible. Ajoutez des équipes et des résultats.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- /.admin-content -->
    </div><!-- /.admin-main -->
</div><!-- /.admin-layout -->

<!-- Mobile sidebar toggle -->
<button class="sidebar-toggle" id="sidebarToggle" aria-label="Menu">☰</button>

<script>
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    sidebarToggle.textContent = sidebar.classList.contains('open') ? '✕' : '☰';
});

// Smooth scroll sidebar links
document.querySelectorAll('.sidebar-link[href^="#"]').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) { target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
        document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
        if (window.innerWidth <= 900) sidebar.classList.remove('open');
    });
});
</script>
</body>
</html>