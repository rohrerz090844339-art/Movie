<?php
session_start();
require_once __DIR__ . '/db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: index.php?admin_login=1');
    exit;
}

if (($_SESSION['user_role'] ?? 'user') !== 'admin') {
    header('Location: index.php?admin_access=denied');
    exit;
}

$movieCount = (int) $pdo->query('SELECT COUNT(*) FROM movies')->fetchColumn();
$userCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$questionCount = (int) $pdo->query('SELECT COUNT(*) FROM questions')->fetchColumn();
$adminCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
$watchlistCount = (int) $pdo->query('SELECT COUNT(*) FROM watchlist')->fetchColumn();
$favoriteCount = (int) $pdo->query('SELECT COUNT(*) FROM favorites')->fetchColumn();
$recentUsers = $pdo->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 6')->fetchAll();
$recentQuestions = $pdo->query('SELECT id, name, category, status, created_at FROM questions ORDER BY created_at DESC LIMIT 6')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RENEtflix Admin Dashboard</title>
    <link rel="icon" href="favicon.svg?v=2" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary-red: #e50914;
            --bg-black: #141414;
            --bg-card: #1d1d1d;
            --bg-soft: #242424;
            --text: #ffffff;
            --muted: #aaa;
            --border: rgba(255,255,255,0.08);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: var(--bg-black);
            color: var(--text);
        }
        .admin-shell {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px 80px;
        }
        .admin-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 28px;
            padding-bottom: 18px;
            border-bottom: 1px solid var(--border);
        }
        .brand {
            font-family: 'Bebas Neue', sans-serif;
            letter-spacing: 2px;
            font-size: 2.4rem;
            color: var(--primary-red);
        }
        .top-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 0;
            border-radius: 8px;
            background: var(--primary-red);
            color: #fff;
            padding: 10px 18px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 700;
        }
        .button.secondary {
            background: #2d2d2d;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01));
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 22px 18px;
        }
        .stat-label {
            color: var(--muted);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-bottom: 12px;
        }
        .stat-value {
            font-size: 2.2rem;
            font-weight: 800;
        }
        .admin-grid {
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            gap: 20px;
        }
        .panel {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        th, td {
            text-align: left;
            padding: 10px 8px;
            border-bottom: 1px solid var(--border);
            color: var(--text);
        }
        th {
            color: var(--muted);
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .07em;
        }
        .tag {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 700;
            background: rgba(229, 9, 20, 0.15);
            color: #ff9ea4;
        }
        @media (max-width: 768px) {
            .admin-grid { grid-template-columns: 1fr; }
            .admin-topbar { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
    <div class="admin-shell">
        <div class="admin-topbar">
            <div class="brand">RENE<span style="color:#fff;">TFLIX</span></div>
            <div class="top-actions">
                <a href="index.php" class="button secondary"><i class="fas fa-home"></i> Home</a>
                <a href="index.php?logout=1" class="button"><i class="fas fa-sign-out-alt"></i> Sign out</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Movies</div>
                <div class="stat-value"><?= $movieCount ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Users</div>
                <div class="stat-value"><?= $userCount ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Questions</div>
                <div class="stat-value"><?= $questionCount ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Admins</div>
                <div class="stat-value"><?= $adminCount ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Saved to My List</div>
                <div class="stat-value"><?= $watchlistCount ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Favorites</div>
                <div class="stat-value"><?= $favoriteCount ?></div>
            </div>
        </div>

        <div class="admin-grid">
            <div class="panel">
                <h3>Recent Users</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><span class="tag"><?= htmlspecialchars($user['role'] ?? 'user') ?></span></td>
                                <td><?= htmlspecialchars(date('M d, Y', strtotime($user['created_at']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="panel">
                <h3>Recent Support Questions</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Person</th>
                            <th>Category</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentQuestions as $question): ?>
                            <tr>
                                <td><?= htmlspecialchars($question['name']) ?></td>
                                <td><?= htmlspecialchars($question['category']) ?></td>
                                <td><span class="tag"><?= htmlspecialchars($question['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
