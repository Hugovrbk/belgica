<?php
session_start();
require_once '../../../db.php';
require_once 'check_admin.php'; // Ce fichier bloque l'accès si on n'est pas admin



// ── Calcul du classement ────────────────────────────────────────────────────
$classement = [];
try {
    // Récupérer toutes les équipes
    $equipes_stmt = $pdo->query("SELECT nom FROM equipes ORDER BY nom ASC");
    $toutes_equipes = $equipes_stmt->fetchAll(PDO::FETCH_COLUMN);

    // Initialiser chaque équipe
    foreach ($toutes_equipes as $eq) {
        $classement[$eq] = [
            'pts' => 0, 'j' => 0, 'v' => 0, 'n' => 0, 'd' => 0,
            'bp' => 0, 'bc' => 0, 'diff' => 0
        ];
    }

    // Parcourir tous les résultats
    $resultats_stmt = $pdo->query("SELECT * FROM resultats ORDER BY journee ASC");
    $tous_resultats = $resultats_stmt->fetchAll();

    foreach ($tous_resultats as $r) {
        $dom = $r['equipe_domicile'];
        $ext = $r['equipe_exterieur'];
        $bd  = $r['buts_domicile'];
        $be  = $r['buts_exterieur'];

        // S'assurer que les équipes existent dans le tableau (sécurité)
        if (!isset($classement[$dom])) {
            $classement[$dom] = ['pts'=>0,'j'=>0,'v'=>0,'n'=>0,'d'=>0,'bp'=>0,'bc'=>0,'diff'=>0];
        }
        if (!isset($classement[$ext])) {
            $classement[$ext] = ['pts'=>0,'j'=>0,'v'=>0,'n'=>0,'d'=>0,'bp'=>0,'bc'=>0,'diff'=>0];
        }

        // Matchs joués
        $classement[$dom]['j']++;
        $classement[$ext]['j']++;

        // Buts
        $classement[$dom]['bp'] += $bd;
        $classement[$dom]['bc'] += $be;
        $classement[$ext]['bp'] += $be;
        $classement[$ext]['bc'] += $bd;

        // Résultat
        if ($bd > $be) {
            $classement[$dom]['v']++;   $classement[$dom]['pts'] += 3;
            $classement[$ext]['d']++;
        } elseif ($bd === $be) {
            $classement[$dom]['n']++;   $classement[$dom]['pts']++;
            $classement[$ext]['n']++;   $classement[$ext]['pts']++;
        } else {
            $classement[$ext]['v']++;   $classement[$ext]['pts'] += 3;
            $classement[$dom]['d']++;
        }
    }

    // Différence de buts
    foreach ($classement as $nom => &$stats) {
        $stats['diff'] = $stats['bp'] - $stats['bc'];
    }
    unset($stats);

    // Trier : points DESC, diff DESC, buts pour DESC
    uasort($classement, function($a, $b) {
        if ($b['pts'] !== $a['pts']) return $b['pts'] - $a['pts'];
        if ($b['diff'] !== $a['diff']) return $b['diff'] - $a['diff'];
        return $b['bp'] - $a['bp'];
    });

} catch (PDOException $e) {
    $classement = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Belgica FC 3</title>
    <link rel="stylesheet" href="../../../css/admin_styles.css">
</head>
<body>
    <h1>Bienvenue, <?= htmlspecialchars($_SESSION['username']) ?> !</h1>
    <p>Ceci est une zone sécurisée réservée aux administrateurs.</p>
    <!-- ══ HEADER ═══════════════════════════════════════════════════════════ -->
    <div class="header">
        <div class="logo"></div>
        <div class="navigation">
            <h1>Interface d'Administration</h1>
        </div>
        <div class="compte">
            <a href="../../../index.php" class="nav-btn">Retour au site</a>
        </div>
    </div>

    <!-- ══ CONTENU PRINCIPAL ════════════════════════════════════════════════ -->
    <div class="center">
        <div class="admin-container">

            <!-- Message flash -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="message <?= $_SESSION['message_type'] ?>">
                    <?= htmlspecialchars($_SESSION['message']) ?>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>

            <!-- ╔══════════════════════════════════════════╗ -->
            <!-- ║  SECTION 1 — PROCHAIN MATCH              ║ -->
            <!-- ╚══════════════════════════════════════════╝ -->
            <div class="section-block">
                <h2>Gestion du Prochain Match</h2>

                <form action="../php/save_match.php" method="POST" class="match-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="equipe_adversaire">Équipe Adversaire</label>
                            <input type="text" id="equipe_adversaire" name="equipe_adversaire" required
                                   placeholder="Ex: FC Bruxelles">
                        </div>
                        <div class="form-group">
                            <label for="stade">Stade</label>
                            <input type="text" id="stade" name="stade" required
                                   placeholder="Ex: Terrain 1">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_match">Date du Match</label>
                            <input type="datetime-local" id="date_match" name="date_match" required>
                        </div>
                        <div class="form-group">
                            <label for="competition">Compétition</label>
                            <input type="text" id="competition" name="competition"
                                   placeholder="Ex: Championnat, Coupe…">
                        </div>
                    </div>
                    <button type="submit" class="btn-submit">Enregistrer le Match</button>
                </form>

                <!-- Liste matchs programmés -->
                <div class="current-matches">
                    <h3>Matchs Programmés</h3>
                    <?php
                    try {
                        $sql   = "SELECT * FROM matches WHERE date_match >= NOW() ORDER BY date_match ASC";
                        $stmt  = $pdo->query($sql);
                        $matches = $stmt->fetchAll();
                        if (count($matches) > 0) {
                            foreach ($matches as $match) {
                                $date      = new DateTime($match['date_match']);
                                $formatter = new IntlDateFormatter('fr_FR',
                                    IntlDateFormatter::FULL, IntlDateFormatter::SHORT, 'Europe/Brussels');
                                $dateF = $formatter->format($date);
                                echo '<div class="match-item">';
                                echo '<div class="match-info">';
                                echo '<p><strong>Belgica FC vs ' . htmlspecialchars($match['equipe_adversaire']) . '</strong></p>';
                                echo '<p>📍 ' . htmlspecialchars($match['stade']) . ' &nbsp;|&nbsp; 📅 ' . $dateF . '</p>';
                                if (!empty($match['competition'])) {
                                    echo '<p>🏆 ' . htmlspecialchars($match['competition']) . '</p>';
                                }
                                echo '</div>';
                                echo '<form action="../php/delete_match.php" method="POST">';
                                echo '<input type="hidden" name="match_id" value="' . $match['id'] . '">';
                                echo '<button type="submit" class="btn-delete"
                                      onclick="return confirm(\'Supprimer ce match ?\')">🗑 Supprimer</button>';
                                echo '</form>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p class="empty-msg">Aucun match programmé pour le moment.</p>';
                        }
                    } catch (PDOException $e) {
                        echo '<p class="empty-msg">Erreur lors du chargement des matchs.</p>';
                    }
                    ?>
                </div>
            </div>

            <!-- ╔══════════════════════════════════════════╗ -->
            <!-- ║  SECTION 2 — GESTION DES ÉQUIPES         ║ -->
            <!-- ╚══════════════════════════════════════════╝ -->
            <div class="section-block">
                <h2>Gestion des Équipes</h2>

                <form action="../php/manage_teams.php" method="POST" class="match-form teams-add-form">
                    <input type="hidden" name="action" value="add">
                    <div class="form-row">
                        <div class="form-group" style="flex:1">
                            <label for="nom_equipe">Nom de la nouvelle équipe</label>
                            <input type="text" id="nom_equipe" name="nom_equipe" required
                                   placeholder="Ex: FC Anderlecht">
                        </div>
                        <div class="form-group form-group--btn">
                            <button type="submit" class="btn-submit btn-submit--inline">➕ Ajouter</button>
                        </div>
                    </div>
                </form>

                <!-- Liste des équipes -->
                <div class="current-matches">
                    <h3>Équipes inscrites</h3>
                    <?php
                    try {
                        $eq_stmt = $pdo->query("SELECT * FROM equipes ORDER BY nom ASC");
                        $equipes = $eq_stmt->fetchAll();
                        if (count($equipes) > 0) {
                            echo '<div class="teams-grid">';
                            foreach ($equipes as $eq) {
                                echo '<div class="team-chip">';
                                echo '<span>' . htmlspecialchars($eq['nom']) . '</span>';
                                echo '<form action="../php/manage_teams.php" method="POST">';
                                echo '<input type="hidden" name="action" value="delete">';
                                echo '<input type="hidden" name="equipe_id" value="' . $eq['id'] . '">';
                                echo '<button type="submit" class="chip-delete"
                                      onclick="return confirm(\'Supprimer ' . htmlspecialchars($eq['nom']) . ' ?\')">✕</button>';
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

            <!-- ╔══════════════════════════════════════════╗ -->
            <!-- ║  SECTION 3 — SAISIE RÉSULTAT             ║ -->
            <!-- ╚══════════════════════════════════════════╝ -->
            <div class="section-block">
                <h2>Saisir un Résultat de Championnat</h2>

                <?php
                // Charger la liste des équipes pour les selects
                try {
                    $eq_list_stmt = $pdo->query("SELECT nom FROM equipes ORDER BY nom ASC");
                    $eq_list = $eq_list_stmt->fetchAll(PDO::FETCH_COLUMN);
                } catch (PDOException $e) {
                    $eq_list = [];
                }
                ?>

                <form action="../php/save_result.php" method="POST" class="match-form result-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="journee">Journée</label>
                            <input type="number" id="journee" name="journee" min="1" max="50" required
                                   placeholder="Ex: 1">
                        </div>
                        <div class="form-group">
                            <label for="date_match_result">Date du match</label>
                            <input type="date" id="date_match_result" name="date_match">
                        </div>
                    </div>

                    <div class="score-row">
                        <!-- Équipe domicile -->
                        <div class="score-team">
                            <label for="equipe_domicile">Équipe Domicile</label>
                            <select id="equipe_domicile" name="equipe_domicile" required class="team-select">
                                <option value="" disabled selected>-- Choisir --</option>
                                <?php foreach ($eq_list as $nom): ?>
                                    <option value="<?= htmlspecialchars($nom) ?>"><?= htmlspecialchars($nom) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Score -->
                        <div class="score-inputs">
                            <input type="number" name="buts_domicile" min="0" max="99" value="0"
                                   class="score-input" required>
                            <span class="score-sep">–</span>
                            <input type="number" name="buts_exterieur" min="0" max="99" value="0"
                                   class="score-input" required>
                        </div>

                        <!-- Équipe extérieur -->
                        <div class="score-team">
                            <label for="equipe_exterieur">Équipe Extérieur</label>
                            <select id="equipe_exterieur" name="equipe_exterieur" required class="team-select">
                                <option value="" disabled selected>-- Choisir --</option>
                                <?php foreach ($eq_list as $nom): ?>
                                    <option value="<?= htmlspecialchars($nom) ?>"><?= htmlspecialchars($nom) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">Enregistrer le Résultat</button>
                </form>

                <!-- Historique des résultats -->
                <div class="current-matches">
                    <h3>Résultats enregistrés</h3>
                    <?php
                    try {
                        $res_stmt = $pdo->query("SELECT * FROM resultats ORDER BY journee ASC, id ASC");
                        $resultats = $res_stmt->fetchAll();
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
                                $bd  = $r['buts_domicile'];
                                $be  = $r['buts_exterieur'];
                                $winner_dom = $bd > $be ? 'winner' : ($bd < $be ? 'loser' : 'draw');
                                $winner_ext = $be > $bd ? 'winner' : ($be < $bd ? 'loser' : 'draw');

                                echo '<div class="result-item">';
                                echo '<span class="r-team r-dom ' . $winner_dom . '">' . $dom . '</span>';
                                echo '<span class="r-score">' . $bd . ' – ' . $be . '</span>';
                                echo '<span class="r-team r-ext ' . $winner_ext . '">' . $ext . '</span>';
                                echo '<form action="../php/delete_result.php" method="POST">';
                                echo '<input type="hidden" name="result_id" value="' . $r['id'] . '">';
                                echo '<button type="submit" class="btn-delete"
                                      onclick="return confirm(\'Supprimer ce résultat ?\')">🗑</button>';
                                echo '</form>';
                                echo '</div>';
                            }
                            echo '</div>'; // fermer dernier groupe
                        } else {
                            echo '<p class="empty-msg">Aucun résultat enregistré.</p>';
                        }
                    } catch (PDOException $e) {
                        echo '<p class="empty-msg">Erreur de chargement.</p>';
                    }
                    ?>
                </div>
            </div>

            <!-- ╔══════════════════════════════════════════╗ -->
            <!-- ║  SECTION 4 — CLASSEMENT                  ║ -->
            <!-- ╚══════════════════════════════════════════╝ -->
            <div class="section-block">
                <h2>Classement du Championnat</h2>

                <?php if (!empty($classement)): ?>
                <div class="classement-wrapper">
                    <table class="classement-table">
                        <thead>
                            <tr>
                                <th class="col-pos">#</th>
                                <th class="col-nom">Équipe</th>
                                <th class="col-stat">J</th>
                                <th class="col-stat">V</th>
                                <th class="col-stat">N</th>
                                <th class="col-stat">D</th>
                                <th class="col-stat">BP</th>
                                <th class="col-stat">BC</th>
                                <th class="col-stat">DIFF</th>
                                <th class="col-pts">PTS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $pos = 1;
                            foreach ($classement as $nom => $s):
                                $is_belgica = (stripos($nom, 'belgica') !== false);
                                $diff_str   = $s['diff'] > 0 ? '+' . $s['diff'] : $s['diff'];
                                $row_class  = $is_belgica ? 'row-belgica' : '';
                            ?>
                            <tr class="<?= $row_class ?>">
                                <td class="col-pos">
                                    <?php if ($pos === 1): ?>
                                        <span class="badge badge-gold">1</span>
                                    <?php elseif ($pos === 2): ?>
                                        <span class="badge badge-silver">2</span>
                                    <?php elseif ($pos === 3): ?>
                                        <span class="badge badge-bronze">3</span>
                                    <?php else: ?>
                                        <?= $pos ?>
                                    <?php endif; ?>
                                </td>
                                <td class="col-nom">
                                    <?php if ($is_belgica): ?>
                                        <span class="belgica-marker">⚽</span>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($nom) ?>
                                </td>
                                <td><?= $s['j'] ?></td>
                                <td><?= $s['v'] ?></td>
                                <td><?= $s['n'] ?></td>
                                <td><?= $s['d'] ?></td>
                                <td><?= $s['bp'] ?></td>
                                <td><?= $s['bc'] ?></td>
                                <td class="<?= $s['diff'] > 0 ? 'diff-pos' : ($s['diff'] < 0 ? 'diff-neg' : '') ?>">
                                    <?= $diff_str ?>
                                </td>
                                <td class="col-pts"><strong><?= $s['pts'] ?></strong></td>
                            </tr>
                            <?php $pos++; endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p class="empty-msg">Aucune donnée de classement disponible. Ajoutez des équipes et des résultats.</p>
                <?php endif; ?>
            </div>

        </div><!-- /.admin-container -->
    </div><!-- /.center -->

    <!-- ══ FOOTER ════════════════════════════════════════════════════════════ -->
    <div class="footer">
        <p>© 2026 Belgica FC — Interface d'Administration</p>
    </div>

</body>
</html>