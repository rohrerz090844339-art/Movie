<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$profile = $_SESSION['active_profile'] ?? $_GET['profile'] ?? $_POST['profile'] ?? 'Rene';

try {
    switch ($action) {
        
        
        
        case 'get_auth_state':
            $isLoggedIn = !empty($_SESSION['user_id']);
            $userData = null;
            $profiles = [];

            if ($isLoggedIn) {
                $uStmt = $pdo->prepare("SELECT id, name, email, avatar, active_profile, role FROM users WHERE id = ?");
                $uStmt->execute([$_SESSION['user_id']]);
                $userData = $uStmt->fetch();

                if ($userData) {
                    $_SESSION['user_role'] = $userData['role'] ?? 'user';
                    $pStmt = $pdo->prepare("SELECT id, profile_name, avatar_url, is_kids FROM user_profiles WHERE user_id = ?");
                    $pStmt->execute([$userData['id']]);
                    $profiles = $pStmt->fetchAll();
                } else {
                    session_destroy();
                    $isLoggedIn = false;
                }
            }

            echo json_encode([
                'status' => 'success',
                'logged_in' => $isLoggedIn,
                'user' => $userData,
                'current_profile' => $_SESSION['active_profile'] ?? 'Rene',
                'profiles' => $profiles
            ]);
            break;

        case 'update_profile':
            if (empty($_SESSION['user_id'])) {
                echo json_encode(['status' => 'error', 'message' => 'Please sign in to customize your profile.']);
                exit;
            }

            $name = trim($_POST['name'] ?? '');
            $avatar = $_POST['avatar'] ?? '';
            $allowedAvatars = [
                'assets/avatar_rene.svg',
                'assets/avatar_sherlyn.svg',
                'assets/avatar_kids.svg',
                'assets/avatar_guest.svg'
            ];

            if ($name === '' || strlen($name) > 100) {
                echo json_encode(['status' => 'error', 'message' => 'Enter a profile name of 1 to 100 characters.']);
                exit;
            }
            if (!in_array($avatar, $allowedAvatars, true)) {
                echo json_encode(['status' => 'error', 'message' => 'Choose one of the available profile avatars.']);
                exit;
            }

            $userId = (int) $_SESSION['user_id'];
            $activeProfile = $_SESSION['active_profile'] ?? $name;
            $pdo->beginTransaction();
            try {
                $updateUser = $pdo->prepare("UPDATE users SET name = ?, avatar = ? WHERE id = ?");
                $updateUser->execute([$name, $avatar, $userId]);

                $updateProfile = $pdo->prepare("UPDATE user_profiles SET avatar_url = ? WHERE user_id = ? AND profile_name = ?");
                $updateProfile->execute([$avatar, $userId, $activeProfile]);

                if ($updateProfile->rowCount() === 0) {
                    $createProfile = $pdo->prepare("INSERT INTO user_profiles (user_id, profile_name, avatar_url, is_kids) VALUES (?, ?, ?, 0) ON DUPLICATE KEY UPDATE avatar_url = VALUES(avatar_url)");
                    $createProfile->execute([$userId, $activeProfile, $avatar]);
                }

                $pdo->commit();
            } catch (Throwable $error) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw $error;
            }

            $_SESSION['user_name'] = $name;
            $_SESSION['user_avatar'] = $avatar;

            echo json_encode([
                'status' => 'success',
                'message' => 'Your profile has been saved.',
                'user' => ['name' => $name, 'avatar' => $avatar]
            ]);
            break;

        case 'login':
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                echo json_encode(['status' => 'error', 'message' => 'Please provide both email and password.']);
                exit;
            }

            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid email or password. Please try again.']);
                exit;
            }

            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_avatar'] = $user['avatar'];
            $_SESSION['user_role'] = $user['role'] ?? 'user';
            $_SESSION['active_profile'] = $user['active_profile'] ?: $user['name'];

            $pStmt = $pdo->prepare("SELECT id, profile_name, avatar_url, is_kids FROM user_profiles WHERE user_id = ?");
            $pStmt->execute([$user['id']]);
            $profiles = $pStmt->fetchAll();

            echo json_encode([
                'status' => 'success',
                'message' => 'Welcome back, ' . $user['name'] . '!',
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'avatar' => $user['avatar'],
                    'active_profile' => $_SESSION['active_profile'],
                    'role' => $_SESSION['user_role']
                ],
                'profiles' => $profiles
            ]);
            break;

        case 'register':
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $avatar = trim($_POST['avatar'] ?? 'assets/avatar_rene.svg');

            if (empty($name) || empty($email) || empty($password)) {
                echo json_encode(['status' => 'error', 'message' => 'All fields are required to create an account.']);
                exit;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address.']);
                exit;
            }

            if (strlen($name) > 100 || strlen($email) > 150 || strlen($password) < 8) {
                echo json_encode(['status' => 'error', 'message' => 'Use a name and email within the allowed length and a password of at least 8 characters.']);
                exit;
            }

            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetch()) {
                echo json_encode(['status' => 'error', 'message' => 'An account with this email already exists. Please log in instead.']);
                exit;
            }

            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO users (name, email, password, avatar, active_profile, role) VALUES (?, ?, ?, ?, ?, 'user')");
            $ins->execute([$name, $email, $hashed, $avatar, $name]);
            $newUserId = $pdo->lastInsertId();

            $pIns = $pdo->prepare("INSERT INTO user_profiles (user_id, profile_name, avatar_url, is_kids) VALUES (?, ?, ?, ?)");
            $pIns->execute([$newUserId, $name, $avatar, 0]);
            $pIns->execute([$newUserId, 'Kids', 'assets/avatar_kids.svg', 1]);
            $pIns->execute([$newUserId, 'Guest', 'assets/avatar_guest.svg', 0]);

            session_regenerate_id(true);
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_avatar'] = $avatar;
            $_SESSION['user_role'] = 'user';
            $_SESSION['active_profile'] = $name;

            echo json_encode([
                'status' => 'success',
                'message' => 'Account created successfully! Welcome to RENEtflix, ' . $name . '.',
                'user' => [
                    'id' => $newUserId,
                    'name' => $name,
                    'email' => $email,
                    'avatar' => $avatar,
                    'active_profile' => $name,
                    'role' => 'user'
                ]
            ]);
            break;

        case 'logout':
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            session_destroy();
            echo json_encode(['status' => 'success', 'message' => 'You have logged out successfully.']);
            break;

        case 'get_dashboard_stats':
            if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? 'user') !== 'admin') {
                echo json_encode(['status' => 'error', 'message' => 'Admin access required.']);
                exit;
            }

            $movieCount = (int) $pdo->query("SELECT COUNT(*) FROM movies")->fetchColumn();
            $userCount = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
            $questionCount = (int) $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
            $adminCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
            $watchlistCount = (int) $pdo->query("SELECT COUNT(*) FROM watchlist")->fetchColumn();
            $favoriteCount = (int) $pdo->query("SELECT COUNT(*) FROM favorites")->fetchColumn();
            $recentUsers = $pdo->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 6")->fetchAll();
            $recentQuestions = $pdo->query("SELECT id, name, category, status, created_at FROM questions ORDER BY created_at DESC LIMIT 6")->fetchAll();

            echo json_encode([
                'status' => 'success',
                'stats' => [
                    'movies' => $movieCount,
                    'users' => $userCount,
                    'questions' => $questionCount,
                    'admins' => $adminCount,
                    'watchlist_items' => $watchlistCount,
                    'favorites' => $favoriteCount
                ],
                'recent_users' => $recentUsers,
                'recent_questions' => $recentQuestions
            ]);
            break;

        case 'switch_profile':
            $targetProfile = trim($_POST['profile_name'] ?? '');
            if (!empty($targetProfile)) {
                $_SESSION['active_profile'] = $targetProfile;
                if (!empty($_SESSION['user_id'])) {
                    $upd = $pdo->prepare("UPDATE users SET active_profile = ? WHERE id = ?");
                    $upd->execute([$targetProfile, $_SESSION['user_id']]);
                }
                echo json_encode(['status' => 'success', 'active_profile' => $targetProfile]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid profile name.']);
            }
            break;

        case 'add_profile':
            if (empty($_SESSION['user_id'])) {
                echo json_encode(['status' => 'error', 'message' => 'You must be logged in to create a profile.']);
                exit;
            }
            $pName = trim($_POST['profile_name'] ?? '');
            $pAvatar = trim($_POST['avatar_url'] ?? 'assets/avatar_rene.svg');
            $isKids = !empty($_POST['is_kids']) ? 1 : 0;

            if (empty($pName)) {
                echo json_encode(['status' => 'error', 'message' => 'Profile name cannot be blank.']);
                exit;
            }

            $ins = $pdo->prepare("INSERT INTO user_profiles (user_id, profile_name, avatar_url, is_kids) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE avatar_url = ?");
            $ins->execute([$_SESSION['user_id'], $pName, $pAvatar, $isKids, $pAvatar]);

            
            $pStmt = $pdo->prepare("SELECT id, profile_name, avatar_url, is_kids FROM user_profiles WHERE user_id = ?");
            $pStmt->execute([$_SESSION['user_id']]);
            $profiles = $pStmt->fetchAll();

            echo json_encode(['status' => 'success', 'profiles' => $profiles]);
            break;

        
        
        
        case 'submit_question':
            $qName = trim($_POST['name'] ?? ($_SESSION['user_name'] ?? 'Viewer'));
            $qEmail = trim($_POST['email'] ?? ($_SESSION['user_email'] ?? 'viewer@renetflix.local'));
            $qCategory = trim($_POST['category'] ?? 'General Inquiry');
            $qQuestion = trim($_POST['question'] ?? '');

            if (empty($qQuestion)) {
                echo json_encode(['status' => 'error', 'message' => 'Please enter your question.']);
                exit;
            }

            
            $answer = "Thank you for reaching out, $qName! ";
            $lower = strtolower($qQuestion);

            if (strpos($lower, 'free') !== false || strpos($lower, 'cost') !== false || strpos($lower, 'pay') !== false || strpos($lower, 'subscription') !== false) {
                $answer .= "RENEtflix is 100% free! You never have to pay a single peso or enter a credit card.";
            } elseif (strpos($lower, 'add') !== false || strpos($lower, 'upload') !== false || strpos($lower, 'request') !== false) {
                $answer .= "You can add new movies anytime using the '+ Add Movie' button in the top navigation bar.";
            } elseif (strpos($lower, 'quality') !== false || strpos($lower, '4k') !== false || strpos($lower, 'hd') !== false) {
                $answer .= "All titles on RENEtflix support Ultra HD 4K and 1080p high definition streaming.";
            } elseif (strpos($lower, 'account') !== false || strpos($lower, 'profile') !== false || strpos($lower, 'login') !== false) {
                $answer .= "You can switch profiles or sign in/out anytime using the profile icon in the top right corner.";
            } else {
                $answer .= "Your inquiry has been recorded by our RENEtflix support desk. Enjoy free unlimited movie streaming!";
            }

            $stmt = $pdo->prepare("INSERT INTO questions (name, email, category, question, answer, status) VALUES (?, ?, ?, ?, ?, 'Answered')");
            $stmt->execute([$qName, $qEmail, $qCategory, $qQuestion, $answer]);

            
            $allQ = $pdo->query("SELECT * FROM questions ORDER BY created_at DESC LIMIT 15")->fetchAll();

            echo json_encode([
                'status' => 'success',
                'message' => 'Your question has been submitted and answered!',
                'new_answer' => $answer,
                'questions' => $allQ
            ]);
            break;

        case 'get_questions':
            $allQ = $pdo->query("SELECT * FROM questions ORDER BY created_at DESC LIMIT 15")->fetchAll();
            echo json_encode(['status' => 'success', 'questions' => $allQ]);
            break;

        
        
        
        case 'get_movies':
            $category = $_GET['category'] ?? null;
            $search = $_GET['search'] ?? null;
            $movies = getAllMovies($pdo, $category, $search);
            echo json_encode(['status' => 'success', 'data' => $movies]);
            break;

        case 'get_movie':
            $id = intval($_GET['id'] ?? 0);
            $stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
            $stmt->execute([$id]);
            $movie = $stmt->fetch();

            if (!$movie) {
                echo json_encode(['status' => 'error', 'message' => 'Movie not found']);
                exit;
            }

            
            $pdo->prepare("UPDATE movies SET views_count = views_count + 1 WHERE id = ?")->execute([$id]);

            
            $revStmt = $pdo->prepare("SELECT * FROM reviews WHERE movie_id = ? ORDER BY created_at DESC");
            $revStmt->execute([$id]);
            $reviews = $revStmt->fetchAll();

            
            $firstGenre = explode(',', $movie['genres'])[0];
            $simStmt = $pdo->prepare("SELECT id, title, poster_url, backdrop_url, release_year, maturity_rating, duration, match_rate, genres FROM movies WHERE id != ? AND genres LIKE ? LIMIT 6");
            $simStmt->execute([$id, "%" . trim($firstGenre) . "%"]);
            $similar = $simStmt->fetchAll();

            
            $inWatchlist = false;
            $isFavorite = false;
            if (!empty($_SESSION['user_id'])) {
                $watchStmt = $pdo->prepare("SELECT id FROM watchlist WHERE owner_user_id = ? AND user_profile = ? AND movie_id = ?");
                $watchStmt->execute([$_SESSION['user_id'], $profile, $id]);
                $inWatchlist = (bool) $watchStmt->fetch();

                $favoriteStmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND movie_id = ?");
                $favoriteStmt->execute([$_SESSION['user_id'], $id]);
                $isFavorite = (bool) $favoriteStmt->fetch();
            }

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'movie' => $movie,
                    'reviews' => $reviews,
                    'similar' => $similar,
                    'in_watchlist' => $inWatchlist,
                    'is_favorite' => $isFavorite
                ]
            ]);
            break;

        case 'toggle_watchlist':
            if (empty($_SESSION['user_id'])) {
                echo json_encode(['status' => 'error', 'message' => 'Please sign in to save movies to your list.']);
                exit;
            }

            $movieId = intval($_POST['movie_id'] ?? 0);
            if (!$movieId) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid movie ID']);
                exit;
            }

            $check = $pdo->prepare("SELECT id FROM watchlist WHERE owner_user_id = ? AND user_profile = ? AND movie_id = ?");
            $check->execute([$_SESSION['user_id'], $profile, $movieId]);
            $existing = $check->fetch();

            if ($existing) {
                $del = $pdo->prepare("DELETE FROM watchlist WHERE owner_user_id = ? AND user_profile = ? AND movie_id = ?");
                $del->execute([$_SESSION['user_id'], $profile, $movieId]);
                $inList = false;
            } else {
                $ins = $pdo->prepare("INSERT INTO watchlist (owner_user_id, user_profile, movie_id) VALUES (?, ?, ?)");
                $ins->execute([$_SESSION['user_id'], $profile, $movieId]);
                $inList = true;
            }

            $cnt = $pdo->prepare("SELECT COUNT(*) FROM watchlist WHERE owner_user_id = ? AND user_profile = ?");
            $cnt->execute([$_SESSION['user_id'], $profile]);
            $totalCount = $cnt->fetchColumn();

            echo json_encode(['status' => 'success', 'in_watchlist' => $inList, 'total_count' => $totalCount]);
            break;

        case 'get_watchlist':
            if (empty($_SESSION['user_id'])) {
                echo json_encode(['status' => 'error', 'message' => 'Please sign in to view your list.']);
                exit;
            }

            $stmt = $pdo->prepare("
                SELECT m.* FROM movies m
                INNER JOIN watchlist w ON m.id = w.movie_id
                WHERE w.owner_user_id = ? AND w.user_profile = ?
                ORDER BY w.created_at DESC
            ");
            $stmt->execute([$_SESSION['user_id'], $profile]);
            $movies = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $movies]);
            break;

        case 'toggle_favorite':
            if (empty($_SESSION['user_id'])) {
                echo json_encode(['status' => 'error', 'message' => 'Please sign in to save favorites.']);
                exit;
            }

            $movieId = intval($_POST['movie_id'] ?? 0);
            if (!$movieId) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid movie ID.']);
                exit;
            }

            $check = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND movie_id = ?");
            $check->execute([$_SESSION['user_id'], $movieId]);
            if ($check->fetch()) {
                $delete = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND movie_id = ?");
                $delete->execute([$_SESSION['user_id'], $movieId]);
                $isFavorite = false;
            } else {
                $insert = $pdo->prepare("INSERT INTO favorites (user_id, movie_id) VALUES (?, ?)");
                $insert->execute([$_SESSION['user_id'], $movieId]);
                $isFavorite = true;
            }

            echo json_encode(['status' => 'success', 'is_favorite' => $isFavorite]);
            break;

        case 'get_favorites':
            if (empty($_SESSION['user_id'])) {
                echo json_encode(['status' => 'error', 'message' => 'Please sign in to view your favorites.']);
                exit;
            }

            $stmt = $pdo->prepare("
                SELECT m.* FROM movies m
                INNER JOIN favorites f ON m.id = f.movie_id
                WHERE f.user_id = ?
                ORDER BY f.created_at DESC
            ");
            $stmt->execute([$_SESSION['user_id']]);
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
            break;

        case 'add_review':
            $movieId = intval($_POST['movie_id'] ?? 0);
            $rating = intval($_POST['rating'] ?? 5);
            $reviewText = trim($_POST['review_text'] ?? '');

            if (!$movieId || empty($reviewText)) {
                echo json_encode(['status' => 'error', 'message' => 'Please provide a valid rating and review comment.']);
                exit;
            }

            $ins = $pdo->prepare("INSERT INTO reviews (movie_id, user_profile, rating, review_text) VALUES (?, ?, ?, ?)");
            $ins->execute([$movieId, $profile, $rating, htmlspecialchars($reviewText, ENT_QUOTES, 'UTF-8')]);

            
            $revStmt = $pdo->prepare("SELECT * FROM reviews WHERE movie_id = ? ORDER BY created_at DESC");
            $revStmt->execute([$movieId]);
            $reviews = $revStmt->fetchAll();

            echo json_encode(['status' => 'success', 'reviews' => $reviews]);
            break;

        case 'save_progress':
            $movieId = intval($_POST['movie_id'] ?? 0);
            $progress = intval($_POST['progress'] ?? 0);
            if ($movieId) {
                $stmt = $pdo->prepare("
                    INSERT INTO watch_progress (user_profile, movie_id, progress_percent)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE progress_percent = ?
                ");
                $stmt->execute([$profile, $movieId, $progress, $progress]);
            }
            echo json_encode(['status' => 'success']);
            break;

        case 'add_movie':
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $posterUrl = trim($_POST['poster_url'] ?? 'Movies/movie1.jpeg');
            $releaseYear = intval($_POST['release_year'] ?? date('Y'));
            $maturityRating = trim($_POST['maturity_rating'] ?? 'PG-13');
            $duration = trim($_POST['duration'] ?? '2h 00min');
            $genres = trim($_POST['genres'] ?? 'Action, Adventure');
            $director = trim($_POST['director'] ?? '');
            $cast = trim($_POST['cast_members'] ?? '');
            $videoUrl = trim($_POST['video_url'] ?? 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4');
            $trailerId = trim($_POST['trailer_youtube_id'] ?? '');

            if (empty($title) || empty($description)) {
                echo json_encode(['status' => 'error', 'message' => 'Title and description are required.']);
                exit;
            }

            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title))) . '-' . time();
            $backdropUrl = $posterUrl;

            $ins = $pdo->prepare("
                INSERT INTO movies (
                    title, original_title, slug, description, poster_url, backdrop_url,
                    release_year, maturity_rating, duration, match_rate, video_url,
                    trailer_youtube_id, director, cast_members, genres, tags,
                    is_featured, is_trending, top_10_rank
                ) VALUES (
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, 95, ?,
                    ?, ?, ?, ?, 'Free Stream, Full HD',
                    0, 1, 0
                )
            ");
            $ins->execute([
                $title, $title, $slug, $description, $posterUrl, $backdropUrl,
                $releaseYear, $maturityRating, $duration, $videoUrl,
                $trailerId, $director, $cast, $genres
            ]);

            echo json_encode(['status' => 'success', 'new_id' => $pdo->lastInsertId()]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
