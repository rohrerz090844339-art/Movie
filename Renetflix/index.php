<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/db.php';


$isLoggedIn   = !empty($_SESSION['user_id']);
$sessionUser  = $isLoggedIn ? $_SESSION['user_name']  ?? 'User'    : null;
$sessionEmail = $isLoggedIn ? $_SESSION['user_email'] ?? ''        : null;
$sessionRole  = $isLoggedIn ? ($_SESSION['user_role'] ?? 'user')   : null;
$isAdmin      = $isLoggedIn && $sessionRole === 'admin';


$stmt = $pdo->query("SELECT * FROM movies WHERE is_featured = 1 ORDER BY top_10_rank ASC LIMIT 1");
$featured = $stmt->fetch() ?: $pdo->query("SELECT * FROM movies LIMIT 1")->fetch();


$top10Movies = $pdo->query("SELECT * FROM movies WHERE top_10_rank > 0 ORDER BY top_10_rank ASC LIMIT 10")->fetchAll();


$trendingMovies = $pdo->query("SELECT * FROM movies WHERE is_trending = 1 ORDER BY id ASC")->fetchAll();


$actionMovies = $pdo->query("SELECT * FROM movies WHERE genres LIKE '%Action%' ORDER BY id ASC")->fetchAll();


$scifiMovies = $pdo->query("SELECT * FROM movies WHERE genres LIKE '%Sci-Fi%' OR genres LIKE '%Fantasy%' ORDER BY id ASC")->fetchAll();


$comedyMovies = $pdo->query("SELECT * FROM movies WHERE genres LIKE '%Comedy%' OR genres LIKE '%Family%' ORDER BY id ASC")->fetchAll();


$thrillerMovies = $pdo->query("SELECT * FROM movies WHERE genres LIKE '%Thriller%' OR genres LIKE '%Survival%' ORDER BY id ASC")->fetchAll();


$movieFiles = glob(__DIR__ . '/Movies/*.{jpeg,jpg,png,webp,avif}', GLOB_BRACE);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RENEtflix - Watch Free Movies Online</title>
    <meta name="description" content="Stream the best movies for free on RENEtflix. Unlimited streaming, high definition 4K, zero subscription required.">
    
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    
    <link rel="icon" type="image/svg+xml" href="favicon.svg?v=2">
    <link rel="apple-touch-icon" href="favicon.svg?v=2">
    
    
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    
    <nav class="navbar" id="mainNavbar">
        <div class="nav-left">
            <a href="index.php" class="brand-logo" title="Go to RENEtflix Home">
                <span class="logo-rene">RENE</span><span class="logo-tflix">TFLIX</span>
                <span class="free-tag">FREE</span>
            </a>

            <ul class="nav-links" id="mainNavLinks">
                <li class="nav-link active"><a href="index.php">Home</a></li>
                <li class="nav-link"><a href="#rowTop10">Top 10</a></li>
                <li class="nav-link"><a href="#rowTrending">Trending</a></li>
                <li class="nav-link"><a href="#rowAction">Action</a></li>
                <li class="nav-link"><a href="#rowSciFi">Sci-Fi</a></li>
                <li class="nav-link"><a href="#rowComedy">Comedy</a></li>
                <li class="nav-link"><a href="#myListRow">My List</a></li>
                <li class="nav-link"><a href="#faqSection">FAQ</a></li>
                <?php if ($isAdmin): ?>
                <li class="nav-link nav-admin-link">
                    <a href="admin.php"><i class="fas fa-shield-alt"></i> Admin</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="nav-right">
            
            <div class="search-wrapper">
                <button class="search-btn" id="searchBtn" title="Search Movies">
                    <i class="fas fa-search"></i>
                </button>
                <input type="text" id="searchInput" class="search-input" placeholder="Titles, actors, genres...">
            </div>

            
            <button class="nav-action-btn" id="addMovieBtn" title="Add Movie to Database">
                <i class="fas fa-plus-circle"></i>
            </button>

            
            <a href="#myListRow" class="nav-action-btn" title="View My List">
                <i class="fas fa-bookmark"></i>
                <span class="badge-count" id="myListCountBadge">0</span>
            </a>

            
            <?php if (!$isLoggedIn): ?>
            <button class="btn-primary" id="navSignInBtn" style="padding: 6px 18px; font-size: 0.85rem; border-radius: 4px;" onclick="openAuthModal('login')">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
            <?php endif; ?>

            
            <div class="profile-menu-wrapper" id="navProfileWrapper">
                <div class="profile-avatar-btn" id="profileAvatarBtn" title="<?= $isLoggedIn ? htmlspecialchars($sessionUser) : 'Browse Profiles' ?>">
                    <img id="currentProfileAvatar" src="assets/avatar_rene.svg" alt="Profile" class="profile-avatar">
                    <i class="fas fa-caret-down" style="font-size: 0.8rem; color: #a3a3a3;"></i>
                </div>
                <div class="profile-dropdown" id="profileDropdownMenu">
                    <?php if ($isLoggedIn): ?>
                    
                    <div class="profile-dropdown-header">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <img src="assets/avatar_rene.svg" alt="<?= htmlspecialchars($sessionUser) ?>" style="width:38px;height:38px;border-radius:6px;border:2px solid var(--primary-red);">
                            <div>
                                <div style="font-weight:700;color:#fff;font-size:0.95rem;"><?= htmlspecialchars($sessionUser) ?></div>
                                <div style="font-size:0.75rem;color:var(--text-muted);"><?= htmlspecialchars($sessionEmail) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="profile-divider"></div>
                    <?php endif; ?>

                    <div style="padding: 6px 16px 4px; font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">
                        Active Profile: <span id="currentProfileNameDisplay" style="color: var(--primary-red); font-weight: 800;">Rene</span>
                    </div>
                    <div id="dynamicProfileList">
                        <div class="profile-option" onclick="switchProfile('Rene')">
                            <img src="assets/avatar_rene.svg" alt="Rene">
                            <span>Rene</span>
                        </div>
                        <div class="profile-option" onclick="switchProfile('Sherlyn')">
                            <img src="assets/avatar_sherlyn.svg" alt="Sherlyn">
                            <span>Sherlyn</span>
                        </div>
                        <div class="profile-option" onclick="switchProfile('Kids')">
                            <img src="assets/avatar_kids.svg" alt="Kids">
                            <span>Kids</span>
                        </div>
                        <div class="profile-option" onclick="switchProfile('Guest')">
                            <img src="assets/avatar_guest.svg" alt="Guest">
                            <span>Guest Mode</span>
                        </div>
                    </div>
                    <div class="profile-divider"></div>
                    <?php if ($isAdmin): ?>
                    <a class="profile-option" href="admin.php" style="color: var(--primary-red);">
                        <i class="fas fa-shield-alt" style="font-size: 1.05rem;"></i>
                        <span>Admin Dashboard</span>
                    </a>
                    <?php endif; ?>
                    <a class="profile-option" href="http://localhost/phpmyadmin" target="_blank" style="color: #82aaff;">
                        <i class="fas fa-database" style="font-size: 1.05rem;"></i>
                        <span>phpMyAdmin</span>
                    </a>
                    <div class="profile-divider"></div>
                    <?php if ($isLoggedIn): ?>
                    <div class="profile-option" id="menuSignOutBtn" onclick="handleLogout()" style="color: #ff4d4d;">
                        <i class="fas fa-sign-out-alt" style="font-size: 1.1rem;"></i>
                        <span>Sign Out</span>
                    </div>
                    <?php else: ?>
                    <div class="profile-option" id="menuSignInBtn" onclick="openAuthModal('login')" style="color: #46d369;">
                        <i class="fas fa-sign-in-alt" style="font-size: 1.1rem;"></i>
                        <span>Sign In / Register</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    
    <header class="billboard-hero" id="heroBillboard" style="background-image: url('<?= htmlspecialchars($featured['backdrop_url']) ?>');">
        <div class="billboard-overlay"></div>

        <div class="billboard-content">
            <div class="billboard-badge">
                <span>RENETFLIX</span> ORIGINAL • TOP 10 
            </div>

            <h1 class="billboard-title"><?= htmlspecialchars($featured['title']) ?></h1>

            <div class="billboard-meta">
                <span class="meta-match"><?= $featured['match_rate'] ?>% Match</span>
                <span class="meta-year"><?= $featured['release_year'] ?></span>
                <span class="meta-rating"><?= htmlspecialchars($featured['maturity_rating']) ?></span>
                <span class="meta-duration"><?= htmlspecialchars($featured['duration']) ?></span>
                <span class="meta-badge-hd">ULTRA HD 4K</span>
                <span class="meta-badge-hd" style="border-color: var(--primary-red); color: var(--primary-red);">100% FREE</span>
            </div>

            <p class="billboard-description">
                <?= htmlspecialchars($featured['description']) ?>
            </p>

            <div class="billboard-actions">
                <button class="btn-primary" onclick="openCinemaPlayer(<?= $featured['id'] ?>, '<?= addslashes($featured['title']) ?>', '<?= htmlspecialchars($featured['video_url']) ?>')">
                    <i class="fas fa-play"></i> Watch Free Now
                </button>
                <button class="btn-secondary" onclick="openMovieDetails(<?= $featured['id'] ?>)">
                    <i class="fas fa-info-circle"></i> More Info
                </button>
                <button class="btn-icon-round" onclick="toggleWatchlist(<?= $featured['id'] ?>, this)" title="Add to My List">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>

        <button class="billboard-sound-btn" id="heroSoundToggle" title="Play RENEtflix Intro Audio">
            <i class="fas fa-volume-up"></i>
        </button>
    </header>

    
    <main class="content-wrapper">

        
        <section class="row-container" id="searchResultsRow" style="display: none;">
            <div class="row-header">
                <h2 class="row-title">
                    <i class="fas fa-search title-accent"></i> Search Results for <span id="searchKeywordText" style="color: #ffffff; margin-left: 6px;"></span>
                </h2>
            </div>
            <div class="slider-wrapper">
                <div class="slider-track" id="searchResultsTrack"></div>
            </div>
        </section>

        
        <section class="row-container" id="myListRow" style="display: none;">
            <div class="row-header">
                <h2 class="row-title">
                    <i class="fas fa-bookmark title-accent"></i> My List
                </h2>
                <span class="row-explore">Explore All</span>
            </div>
            <div class="slider-wrapper">
                <button class="slider-arrow left" aria-label="Slide Left"><i class="fas fa-chevron-left"></i></button>
                <div class="slider-track" id="myListTrack"></div>
                <button class="slider-arrow right" aria-label="Slide Right"><i class="fas fa-chevron-right"></i></button>
            </div>
        </section>

        
        <section class="row-container" id="rowTop10">
            <div class="row-header">
                <h2 class="row-title">
                    <span class="title-accent">Top 10</span> Movies in RENEtflix Today
                </h2>
                <span class="row-explore">Explore Top 10</span>
            </div>
            <div class="slider-wrapper top10-slider">
                <button class="slider-arrow left" aria-label="Slide Left"><i class="fas fa-chevron-left"></i></button>
                <div class="slider-track">
                    <?php foreach ($top10Movies as $index => $m): ?>
                        <div class="movie-card" data-id="<?= $m['id'] ?>">
                            <div class="top10-item">
                                <div class="top10-number"><?= $m['top_10_rank'] ?></div>
                                <div class="top10-card-wrapper">
                                    <div class="card-inner">
                                        <img src="<?= htmlspecialchars($m['poster_url']) ?>" class="card-poster" alt="<?= htmlspecialchars($m['title']) ?>" loading="lazy">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-hover-panel">
                                <div class="hover-media">
                                    <img src="<?= htmlspecialchars($m['backdrop_url'] ?: $m['poster_url']) ?>" alt="<?= htmlspecialchars($m['title']) ?>">
                                    <div class="play-overlay-icon"><i class="fas fa-play"></i></div>
                                </div>
                                <div class="hover-body">
                                    <div class="hover-actions">
                                        <div class="actions-left">
                                            <button class="btn-mini-round play-btn" onclick="event.stopPropagation(); openCinemaPlayer(<?= $m['id'] ?>, '<?= addslashes($m['title']) ?>', '<?= htmlspecialchars($m['video_url']) ?>')" title="Play Free">
                                                <i class="fas fa-play"></i>
                                            </button>
                                            <button class="btn-mini-round" onclick="event.stopPropagation(); toggleWatchlist(<?= $m['id'] ?>, this)" title="Add to My List">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button class="btn-mini-round" onclick="event.stopPropagation(); showToast('❤️ Added to your likes!')" title="Like">
                                                <i class="far fa-thumbs-up"></i>
                                            </button>
                                        </div>
                                        <button class="btn-mini-round" onclick="event.stopPropagation(); openMovieDetails(<?= $m['id'] ?>)" title="More Info">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                    </div>
                                    <div class="hover-title"><?= htmlspecialchars($m['title']) ?></div>
                                    <div class="hover-meta">
                                        <span class="meta-match"><?= $m['match_rate'] ?>% Match</span>
                                        <span class="meta-rating"><?= htmlspecialchars($m['maturity_rating']) ?></span>
                                        <span class="meta-duration"><?= htmlspecialchars($m['duration']) ?></span>
                                    </div>
                                    <div class="hover-genres">
                                        <?php 
                                        $gList = explode(',', $m['genres']);
                                        foreach (array_slice($gList, 0, 3) as $g): ?>
                                            <span><?= trim(htmlspecialchars($g)) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="slider-arrow right" aria-label="Slide Right"><i class="fas fa-chevron-right"></i></button>
            </div>
        </section>

        
        <section class="row-container" id="rowTrending">
            <div class="row-header">
                <h2 class="row-title">
                    <span class="title-accent">Trending</span> Now on RENEtflix
                </h2>
                <span class="row-explore">Explore All</span>
            </div>
            <div class="slider-wrapper">
                <button class="slider-arrow left" aria-label="Slide Left"><i class="fas fa-chevron-left"></i></button>
                <div class="slider-track">
                    <?php foreach ($trendingMovies as $m): ?>
                        <div class="movie-card" data-id="<?= $m['id'] ?>">
                            <div class="card-inner">
                                <img src="<?= htmlspecialchars($m['poster_url']) ?>" class="card-poster" alt="<?= htmlspecialchars($m['title']) ?>" loading="lazy">
                            </div>
                            <div class="card-hover-panel">
                                <div class="hover-media">
                                    <img src="<?= htmlspecialchars($m['backdrop_url'] ?: $m['poster_url']) ?>" alt="<?= htmlspecialchars($m['title']) ?>">
                                    <div class="play-overlay-icon"><i class="fas fa-play"></i></div>
                                </div>
                                <div class="hover-body">
                                    <div class="hover-actions">
                                        <div class="actions-left">
                                            <button class="btn-mini-round play-btn" onclick="event.stopPropagation(); openCinemaPlayer(<?= $m['id'] ?>, '<?= addslashes($m['title']) ?>', '<?= htmlspecialchars($m['video_url']) ?>')" title="Play Free">
                                                <i class="fas fa-play"></i>
                                            </button>
                                            <button class="btn-mini-round" onclick="event.stopPropagation(); toggleWatchlist(<?= $m['id'] ?>, this)" title="Add to My List">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button class="btn-mini-round" onclick="event.stopPropagation(); showToast('❤️ Added to your likes!')" title="Like">
                                                <i class="far fa-thumbs-up"></i>
                                            </button>
                                        </div>
                                        <button class="btn-mini-round" onclick="event.stopPropagation(); openMovieDetails(<?= $m['id'] ?>)" title="More Info">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                    </div>
                                    <div class="hover-title"><?= htmlspecialchars($m['title']) ?></div>
                                    <div class="hover-meta">
                                        <span class="meta-match"><?= $m['match_rate'] ?>% Match</span>
                                        <span class="meta-rating"><?= htmlspecialchars($m['maturity_rating']) ?></span>
                                        <span class="meta-duration"><?= htmlspecialchars($m['duration']) ?></span>
                                    </div>
                                    <div class="hover-genres">
                                        <?php foreach (array_slice(explode(',', $m['genres']), 0, 3) as $g): ?>
                                            <span><?= trim(htmlspecialchars($g)) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="slider-arrow right" aria-label="Slide Right"><i class="fas fa-chevron-right"></i></button>
            </div>
        </section>

        
        <section class="row-container" id="rowAction">
            <div class="row-header">
                <h2 class="row-title">
                    Action & Superhero Blockbusters
                </h2>
                <span class="row-explore">Explore All</span>
            </div>
            <div class="slider-wrapper">
                <button class="slider-arrow left" aria-label="Slide Left"><i class="fas fa-chevron-left"></i></button>
                <div class="slider-track">
                    <?php foreach ($actionMovies as $m): ?>
                        <div class="movie-card" data-id="<?= $m['id'] ?>">
                            <div class="card-inner">
                                <img src="<?= htmlspecialchars($m['poster_url']) ?>" class="card-poster" alt="<?= htmlspecialchars($m['title']) ?>" loading="lazy">
                            </div>
                            <div class="card-hover-panel">
                                <div class="hover-media">
                                    <img src="<?= htmlspecialchars($m['backdrop_url'] ?: $m['poster_url']) ?>" alt="<?= htmlspecialchars($m['title']) ?>">
                                    <div class="play-overlay-icon"><i class="fas fa-play"></i></div>
                                </div>
                                <div class="hover-body">
                                    <div class="hover-actions">
                                        <div class="actions-left">
                                            <button class="btn-mini-round play-btn" onclick="event.stopPropagation(); openCinemaPlayer(<?= $m['id'] ?>, '<?= addslashes($m['title']) ?>', '<?= htmlspecialchars($m['video_url']) ?>')" title="Play Free">
                                                <i class="fas fa-play"></i>
                                            </button>
                                            <button class="btn-mini-round" onclick="event.stopPropagation(); toggleWatchlist(<?= $m['id'] ?>, this)" title="Add to My List">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button class="btn-mini-round" onclick="event.stopPropagation(); showToast('❤️ Added to your likes!')" title="Like">
                                                <i class="far fa-thumbs-up"></i>
                                            </button>
                                        </div>
                                        <button class="btn-mini-round" onclick="event.stopPropagation(); openMovieDetails(<?= $m['id'] ?>)" title="More Info">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                    </div>
                                    <div class="hover-title"><?= htmlspecialchars($m['title']) ?></div>
                                    <div class="hover-meta">
                                        <span class="meta-match"><?= $m['match_rate'] ?>% Match</span>
                                        <span class="meta-rating"><?= htmlspecialchars($m['maturity_rating']) ?></span>
                                        <span class="meta-duration"><?= htmlspecialchars($m['duration']) ?></span>
                                    </div>
                                    <div class="hover-genres">
                                        <?php foreach (array_slice(explode(',', $m['genres']), 0, 3) as $g): ?>
                                            <span><?= trim(htmlspecialchars($g)) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="slider-arrow right" aria-label="Slide Right"><i class="fas fa-chevron-right"></i></button>
            </div>
        </section>

        
        <section class="row-container" id="rowSciFi">
            <div class="row-header">
                <h2 class="row-title">
                    Sci-Fi & Fantasy Epics
                </h2>
                <span class="row-explore">Explore All</span>
            </div>
            <div class="slider-wrapper">
                <button class="slider-arrow left" aria-label="Slide Left"><i class="fas fa-chevron-left"></i></button>
                <div class="slider-track">
                    <?php foreach ($scifiMovies as $m): ?>
                        <div class="movie-card" data-id="<?= $m['id'] ?>">
                            <div class="card-inner">
                                <img src="<?= htmlspecialchars($m['poster_url']) ?>" class="card-poster" alt="<?= htmlspecialchars($m['title']) ?>" loading="lazy">
                            </div>
                            <div class="card-hover-panel">
                                <div class="hover-media">
                                    <img src="<?= htmlspecialchars($m['backdrop_url'] ?: $m['poster_url']) ?>" alt="<?= htmlspecialchars($m['title']) ?>">
                                    <div class="play-overlay-icon"><i class="fas fa-play"></i></div>
                                </div>
                                <div class="hover-body">
                                    <div class="hover-actions">
                                        <div class="actions-left">
                                            <button class="btn-mini-round play-btn" onclick="event.stopPropagation(); openCinemaPlayer(<?= $m['id'] ?>, '<?= addslashes($m['title']) ?>', '<?= htmlspecialchars($m['video_url']) ?>')" title="Play Free">
                                                <i class="fas fa-play"></i>
                                            </button>
                                            <button class="btn-mini-round" onclick="event.stopPropagation(); toggleWatchlist(<?= $m['id'] ?>, this)" title="Add to My List">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button class="btn-mini-round" onclick="event.stopPropagation(); showToast('❤️ Added to your likes!')" title="Like">
                                                <i class="far fa-thumbs-up"></i>
                                            </button>
                                        </div>
                                        <button class="btn-mini-round" onclick="event.stopPropagation(); openMovieDetails(<?= $m['id'] ?>)" title="More Info">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                    </div>
                                    <div class="hover-title"><?= htmlspecialchars($m['title']) ?></div>
                                    <div class="hover-meta">
                                        <span class="meta-match"><?= $m['match_rate'] ?>% Match</span>
                                        <span class="meta-rating"><?= htmlspecialchars($m['maturity_rating']) ?></span>
                                        <span class="meta-duration"><?= htmlspecialchars($m['duration']) ?></span>
                                    </div>
                                    <div class="hover-genres">
                                        <?php foreach (array_slice(explode(',', $m['genres']), 0, 3) as $g): ?>
                                            <span><?= trim(htmlspecialchars($g)) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="slider-arrow right" aria-label="Slide Right"><i class="fas fa-chevron-right"></i></button>
            </div>
        </section>

        
        <section class="row-container" id="rowComedy">
            <div class="row-header">
                <h2 class="row-title">
                    Comedy & Family Favorites
                </h2>
                <span class="row-explore">Explore All</span>
            </div>
            <div class="slider-wrapper">
                <button class="slider-arrow left" aria-label="Slide Left"><i class="fas fa-chevron-left"></i></button>
                <div class="slider-track">
                    <?php foreach ($comedyMovies as $m): ?>
                        <div class="movie-card" data-id="<?= $m['id'] ?>">
                            <div class="card-inner">
                                <img src="<?= htmlspecialchars($m['poster_url']) ?>" class="card-poster" alt="<?= htmlspecialchars($m['title']) ?>" loading="lazy">
                            </div>
                            <div class="card-hover-panel">
                                <div class="hover-media">
                                    <img src="<?= htmlspecialchars($m['backdrop_url'] ?: $m['poster_url']) ?>" alt="<?= htmlspecialchars($m['title']) ?>">
                                    <div class="play-overlay-icon"><i class="fas fa-play"></i></div>
                                </div>
                                <div class="hover-body">
                                    <div class="hover-actions">
                                        <div class="actions-left">
                                            <button class="btn-mini-round play-btn" onclick="event.stopPropagation(); openCinemaPlayer(<?= $m['id'] ?>, '<?= addslashes($m['title']) ?>', '<?= htmlspecialchars($m['video_url']) ?>')" title="Play Free">
                                                <i class="fas fa-play"></i>
                                            </button>
                                            <button class="btn-mini-round" onclick="event.stopPropagation(); toggleWatchlist(<?= $m['id'] ?>, this)" title="Add to My List">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button class="btn-mini-round" onclick="event.stopPropagation(); showToast('❤️ Added to your likes!')" title="Like">
                                                <i class="far fa-thumbs-up"></i>
                                            </button>
                                        </div>
                                        <button class="btn-mini-round" onclick="event.stopPropagation(); openMovieDetails(<?= $m['id'] ?>)" title="More Info">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                    </div>
                                    <div class="hover-title"><?= htmlspecialchars($m['title']) ?></div>
                                    <div class="hover-meta">
                                        <span class="meta-match"><?= $m['match_rate'] ?>% Match</span>
                                        <span class="meta-rating"><?= htmlspecialchars($m['maturity_rating']) ?></span>
                                        <span class="meta-duration"><?= htmlspecialchars($m['duration']) ?></span>
                                    </div>
                                    <div class="hover-genres">
                                        <?php foreach (array_slice(explode(',', $m['genres']), 0, 3) as $g): ?>
                                            <span><?= trim(htmlspecialchars($g)) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="slider-arrow right" aria-label="Slide Right"><i class="fas fa-chevron-right"></i></button>
            </div>
        </section>

        
        <section class="row-container" id="rowThriller">
            <div class="row-header">
                <h2 class="row-title">
                    Thrillers & High Suspense
                </h2>
                <span class="row-explore">Explore All</span>
            </div>
            <div class="slider-wrapper">
                <button class="slider-arrow left" aria-label="Slide Left"><i class="fas fa-chevron-left"></i></button>
                <div class="slider-track">
                    <?php foreach ($thrillerMovies as $m): ?>
                        <div class="movie-card" data-id="<?= $m['id'] ?>">
                            <div class="card-inner">
                                <img src="<?= htmlspecialchars($m['poster_url']) ?>" class="card-poster" alt="<?= htmlspecialchars($m['title']) ?>" loading="lazy">
                            </div>
                            <div class="card-hover-panel">
                                <div class="hover-media">
                                    <img src="<?= htmlspecialchars($m['backdrop_url'] ?: $m['poster_url']) ?>" alt="<?= htmlspecialchars($m['title']) ?>">
                                    <div class="play-overlay-icon"><i class="fas fa-play"></i></div>
                                </div>
                                <div class="hover-body">
                                    <div class="hover-actions">
                                        <div class="actions-left">
                                            <button class="btn-mini-round play-btn" onclick="event.stopPropagation(); openCinemaPlayer(<?= $m['id'] ?>, '<?= addslashes($m['title']) ?>', '<?= htmlspecialchars($m['video_url']) ?>')" title="Play Free">
                                                <i class="fas fa-play"></i>
                                            </button>
                                            <button class="btn-mini-round" onclick="event.stopPropagation(); toggleWatchlist(<?= $m['id'] ?>, this)" title="Add to My List">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button class="btn-mini-round" onclick="event.stopPropagation(); showToast('❤️ Added to your likes!')" title="Like">
                                                <i class="far fa-thumbs-up"></i>
                                            </button>
                                        </div>
                                        <button class="btn-mini-round" onclick="event.stopPropagation(); openMovieDetails(<?= $m['id'] ?>)" title="More Info">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                    </div>
                                    <div class="hover-title"><?= htmlspecialchars($m['title']) ?></div>
                                    <div class="hover-meta">
                                        <span class="meta-match"><?= $m['match_rate'] ?>% Match</span>
                                        <span class="meta-rating"><?= htmlspecialchars($m['maturity_rating']) ?></span>
                                        <span class="meta-duration"><?= htmlspecialchars($m['duration']) ?></span>
                                    </div>
                                    <div class="hover-genres">
                                        <?php foreach (array_slice(explode(',', $m['genres']), 0, 3) as $g): ?>
                                            <span><?= trim(htmlspecialchars($g)) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="slider-arrow right" aria-label="Slide Right"><i class="fas fa-chevron-right"></i></button>
            </div>
        </section>

    </main>

    
    <section class="faq-section" id="faqSection">
        <h2 class="faq-title">Frequently Asked Questions</h2>
        <div class="faq-accordion">
            <div class="faq-item">
                <button class="faq-question-btn">
                    <span>What is RENEtflix?</span>
                    <span class="faq-icon"><i class="fas fa-plus"></i></span>
                </button>
                <div class="faq-answer-panel">
                    <p>RENEtflix is a 100% free movie streaming platform inspired by Netflix's signature experience. Enjoy unlimited access to Hollywood blockbusters, action hits, family favorites, and trending titles without any subscription fees or credit card requirements.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question-btn">
                    <span>How much does RENEtflix cost?</span>
                    <span class="faq-icon"><i class="fas fa-plus"></i></span>
                </button>
                <div class="faq-answer-panel">
                    <p>Watch RENEtflix completely FREE! There are no hidden fees, no subscription plans, and no cancellation charges. Stream unlimited movies on your PC, smartphone, tablet, or TV anytime.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question-btn">
                    <span>Where can I watch RENEtflix?</span>
                    <span class="faq-icon"><i class="fas fa-plus"></i></span>
                </button>
                <div class="faq-answer-panel">
                    <p>Watch anywhere, anytime. Open RENEtflix directly in any browser at <code>http://localhost/Renetflix</code>. Our web app supports seamless streaming across desktops, laptops, tablets, and phones.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question-btn">
                    <span>How do I add or upload movies to the library?</span>
                    <span class="faq-icon"><i class="fas fa-plus"></i></span>
                </button>
                <div class="faq-answer-panel">
                    <p>Click the <strong>"+ Add Movie"</strong> button in the top navigation bar to register any new movie poster from your local <code>Movies/</code> folder or any MP4 stream URL directly into the MySQL database.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question-btn">
                    <span>What movies can I watch on RENEtflix?</span>
                    <span class="faq-icon"><i class="fas fa-plus"></i></span>
                </button>
                <div class="faq-answer-panel">
                    <p>We feature an exciting curated library including <em>Avengers: Endgame</em>, <em>Avatar: Fire and Ash</em>, <em>Kraven the Hunter</em>, <em>The Runner</em>, <em>Hexed</em>, <em>Scary Movie</em>, <em>Fall 2: Deadpoint</em>, <em>Snow White</em>, <em>One Mile: Chapter Two</em>, and <em>Clash of the Thundermans</em>.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question-btn">
                    <span>Is RENEtflix good for kids?</span>
                    <span class="faq-icon"><i class="fas fa-plus"></i></span>
                </button>
                <div class="faq-answer-panel">
                    <p>Yes! We have a dedicated <strong>Kids</strong> profile featuring family-friendly movies like Disney's Hexed, Snow White, and Nickelodeon's The Thundermans. You can switch to the Kids profile from the top-right profile avatar menu.</p>
                </div>
            </div>
        </div>

        <!-- FAQ Call to Action & Ask Question -->
        <div class="faq-cta-box">
            <h3 style="font-size: 1.4rem; color: #ffffff;">Have questions or need movie assistance?</h3>
            <p style="color: #aaaaaa; max-width: 600px;">Call our free hotline or submit your question below to get instant answers from our support system.</p>
            <div style="display: flex; gap: 14px; flex-wrap: wrap; justify-content: center;">
                <button class="btn-primary" onclick="openQuestionsModal()">
                    <i class="fas fa-question-circle"></i> Ask a Question
                </button>
                <a href="tel:09-RENETFLIX-FREE" class="btn-secondary">
                    <i class="fas fa-phone-alt"></i> Call 09-RENETFLIX-FREE
                </a>
            </div>
        </div>
    </section>

    <!-- ======================================================================
         Netflix Movie Detail Modal
         ====================================================================== -->
    <div class="modal-backdrop" id="movieDetailModal">
        <div class="netflix-modal">
            <button class="modal-close-btn" id="modalCloseBtn" aria-label="Close modal">
                <i class="fas fa-times"></i>
            </button>

            <div class="modal-header-banner" id="modalHeaderBanner">
                <div class="modal-header-gradient"></div>
                <div class="modal-header-content">
                    <h2 class="modal-title" id="modalMovieTitle">Movie Title</h2>
                    <div class="modal-header-actions">
                        <button class="btn-primary" id="modalPlayBtn">
                            <i class="fas fa-play"></i> Play Free
                        </button>
                        <button class="btn-icon-round" id="modalWatchlistBtn" title="Add to My List">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button class="btn-icon-round" onclick="showToast('❤️ You liked this movie!')" title="Like">
                            <i class="far fa-thumbs-up"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal-body">
                <!-- Meta Info & Synopsis -->
                <div class="modal-grid">
                    <div class="modal-left">
                        <div class="billboard-meta">
                            <span class="meta-match" id="modalMatchRate">98% Match</span>
                            <span class="meta-year" id="modalYear">2024</span>
                            <span class="meta-rating" id="modalRating">PG-13</span>
                            <span class="meta-duration" id="modalDuration">2h 15min</span>
                            <span class="meta-badge-hd">ULTRA HD 4K</span>
                        </div>
                        <p class="modal-synopsis" id="modalDescription">Movie plot overview will appear here.</p>
                    </div>

                    <div class="modal-right">
                        <div class="detail-row">
                            <span class="detail-label">Cast:</span>
                            <span class="detail-value" id="modalCast">Actor 1, Actor 2</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Director:</span>
                            <span class="detail-value" id="modalDirector">Director Name</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Genres:</span>
                            <span class="detail-value" id="modalGenres">Action, Adventure</span>
                        </div>
                    </div>
                </div>

                <!-- Navigation Tabs inside Modal -->
                <div class="modal-tabs">
                    <button class="modal-tab-btn active" data-tab="tabStream">
                        <i class="fas fa-film"></i> Free Direct Stream
                    </button>
                    <button class="modal-tab-btn" data-tab="tabTrailer">
                        <i class="fab fa-youtube" style="color: #ff0000;"></i> Official Trailer
                    </button>
                    <button class="modal-tab-btn" data-tab="tabSimilar">
                        <i class="fas fa-layer-group"></i> More Like This
                    </button>
                    <button class="modal-tab-btn" data-tab="tabReviews">
                        <i class="fas fa-star" style="color: #ffd700;"></i> Community Reviews
                    </button>
                </div>

                <!-- Tab 1: Direct Video Stream -->
                <div class="tab-pane active" id="tabStream">
                    <div class="video-embed-box">
                        <video id="modalDirectVideo" controls preload="metadata">
                            Your browser does not support HTML5 video.
                        </video>
                    </div>
                </div>

                <!-- Tab 2: YouTube Trailer -->
                <div class="tab-pane" id="tabTrailer">
                    <div class="video-embed-box">
                        <iframe id="modalTrailerIframe" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                </div>

                <!-- Tab 3: Similar Movies -->
                <div class="tab-pane" id="tabSimilar">
                    <div class="similar-grid" id="modalSimilarContainer"></div>
                </div>

                <!-- Tab 4: Reviews & Community Ratings -->
                <div class="tab-pane" id="tabReviews">
                    <div class="reviews-container">
                        <div class="review-form-box">
                            <h4 style="color: #ffffff;">Leave a Viewer Review</h4>
                            <form id="reviewSubmitForm">
                                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
                                    <span style="font-size: 0.9rem; color: #aaaaaa;">Your Rating:</span>
                                    <div class="star-rating-select star-picker">
                                        <i class="fas fa-star" data-value="1"></i>
                                        <i class="fas fa-star" data-value="2"></i>
                                        <i class="fas fa-star" data-value="3"></i>
                                        <i class="fas fa-star" data-value="4"></i>
                                        <i class="fas fa-star" data-value="5"></i>
                                    </div>
                                    <input type="hidden" id="reviewRatingValue" value="5">
                                </div>
                                <textarea id="reviewTextInput" class="review-textarea" placeholder="Share your thoughts about this movie..."></textarea>
                                <button type="submit" class="btn-primary" style="align-self: flex-start; margin-top: 8px; padding: 8px 20px; font-size: 0.9rem;">
                                    Post Review
                                </button>
                            </form>
                        </div>

                        <div id="modalReviewsList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================================
         Dedicated Fullscreen Cinema Player Modal
         ====================================================================== -->
    <div class="cinema-player-modal" id="cinemaPlayerModal">
        <div class="cinema-top-bar">
            <button class="cinema-back-btn" id="cinemaBackBtn">
                <i class="fas fa-arrow-left"></i> <span>Back to Browse</span>
            </button>
            <div class="cinema-title-badge">
                <span class="free-tag">STREAMING FREE</span>
                <span id="cinemaMovieTitle">Movie Title</span>
            </div>
            <div></div>
        </div>

        <div class="cinema-video-wrapper">
            <video id="cinemaVideo" preload="auto">
                Your browser does not support HTML5 video streaming.
            </video>
        </div>

        <div class="cinema-bottom-bar">
            <!-- Progress Bar -->
            <div class="cinema-progress-container" id="cinemaProgressContainer">
                <div class="cinema-progress-bar" id="cinemaProgressBar">
                    <div class="cinema-progress-thumb"></div>
                </div>
            </div>

            <!-- Controls Row -->
            <div class="cinema-controls-row">
                <div class="controls-left">
                    <button class="cinema-ctrl-btn" id="cinemaPlayPauseBtn" title="Play / Pause">
                        <i class="fas fa-pause"></i>
                    </button>
                    <button class="cinema-ctrl-btn" id="cinemaRewindBtn" title="Rewind 10 seconds">
                        <i class="fas fa-undo"></i>
                    </button>
                    <button class="cinema-ctrl-btn" id="cinemaForwardBtn" title="Forward 10 seconds">
                        <i class="fas fa-redo"></i>
                    </button>
                    
                    <!-- Volume Controls -->
                    <div class="volume-slider-box">
                        <button class="cinema-ctrl-btn" id="cinemaVolumeBtn" title="Mute">
                            <i class="fas fa-volume-up"></i>
                        </button>
                        <input type="range" class="volume-range" id="cinemaVolumeSlider" min="0" max="1" step="0.05" value="1">
                    </div>

                    <span class="cinema-time-text" id="cinemaTimeDisplay">00:00 / 00:00</span>
                </div>

                <div class="controls-right">
                    <span class="meta-badge-hd">4K UHD</span>
                    <button class="cinema-ctrl-btn" id="cinemaFullscreenBtn" title="Fullscreen">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================================
         Admin / Add Movie Modal
         ====================================================================== -->
    <div class="modal-backdrop" id="addMovieModal">
        <div class="netflix-modal" style="max-width: 650px;">
            <button class="modal-close-btn" id="closeAddMovieModalBtn">
                <i class="fas fa-times"></i>
            </button>
            <div style="padding: 30px 40px;">
                <h3 style="font-family: var(--font-logo); font-size: 2.2rem; color: var(--primary-red); margin-bottom: 20px;">
                    Add Movie to RENEtflix Database
                </h3>
                <form id="addMovieForm" class="admin-form-grid">
                    <div class="admin-form-group full-width">
                        <label>Movie Title *</label>
                        <input type="text" name="title" class="admin-input" placeholder="e.g. Inception 2" required>
                    </div>

                    <div class="admin-form-group full-width">
                        <label>Movie Synopsis / Description *</label>
                        <textarea name="description" class="review-textarea" placeholder="Movie synopsis..." required></textarea>
                    </div>

                    <div class="admin-form-group">
                        <label>Poster Image from Movies/ Folder</label>
                        <select name="poster_url" class="admin-select">
                            <?php foreach ($movieFiles as $file): 
                                $relPath = 'Movies/' . basename($file);
                            ?>
                                <option value="<?= htmlspecialchars($relPath) ?>"><?= basename($file) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="admin-form-group">
                        <label>Release Year</label>
                        <input type="number" name="release_year" class="admin-input" value="2026">
                    </div>

                    <div class="admin-form-group">
                        <label>Maturity Rating</label>
                        <select name="maturity_rating" class="admin-select">
                            <option value="PG">PG</option>
                            <option value="PG-13" selected>PG-13</option>
                            <option value="R">R</option>
                            <option value="TV-MA">TV-MA</option>
                        </select>
                    </div>

                    <div class="admin-form-group">
                        <label>Duration</label>
                        <input type="text" name="duration" class="admin-input" value="2h 10min">
                    </div>

                    <div class="admin-form-group full-width">
                        <label>Genres (comma-separated)</label>
                        <input type="text" name="genres" class="admin-input" value="Action, Sci-Fi, Thriller">
                    </div>

                    <div class="admin-form-group">
                        <label>Director</label>
                        <input type="text" name="director" class="admin-input" placeholder="Director Name">
                    </div>

                    <div class="admin-form-group">
                        <label>Cast Members</label>
                        <input type="text" name="cast_members" class="admin-input" placeholder="Actor 1, Actor 2">
                    </div>

                    <div class="admin-form-group full-width">
                        <label>Direct Stream Video URL (MP4)</label>
                        <input type="url" name="video_url" class="admin-input" value="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4">
                    </div>

                    <div class="admin-form-group full-width">
                        <label>YouTube Trailer ID (Optional)</label>
                        <input type="text" name="trailer_youtube_id" class="admin-input" placeholder="e.g. dQw4w9WgXcQ">
                    </div>

                    <div class="admin-form-group full-width" style="margin-top: 10px;">
                        <button type="submit" class="btn-primary" style="width: 100%; justify-content: center;">
                            <i class="fas fa-plus-circle"></i> Save Movie to MySQL Database
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal-backdrop" id="authModal">
        <div class="auth-box">
            <button class="modal-close-btn" id="closeAuthModal" aria-label="Close authentication dialog">
                <i class="fas fa-times"></i>
            </button>
            <div class="auth-tabs">
                <button type="button" class="auth-tab-btn active" data-auth-tab="login">Sign In</button>
                <button type="button" class="auth-tab-btn" data-auth-tab="register">Register</button>
            </div>

            <div class="auth-panel active" data-auth-panel="login">
                <div class="auth-quick-demos">
                    <button type="button" class="btn-demo-login" data-demo-email="rene@renetflix.com" data-demo-password="password123">
                        <i class="fas fa-user-circle"></i> Rene
                    </button>
                    <button type="button" class="btn-demo-login" data-demo-email="sherlyn@renetflix.com" data-demo-password="password123">
                        <i class="fas fa-user-circle"></i> Sherlyn
                    </button>
                </div>
                <form id="loginForm" data-auth-mode="login">
                    <div class="admin-form-group full-width">
                        <label>Email</label>
                        <input type="email" name="email" class="admin-input" placeholder="name@example.com" required>
                    </div>
                    <div class="admin-form-group full-width">
                        <label>Password</label>
                        <input type="password" name="password" class="admin-input" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 10px;">Sign In</button>
                </form>
            </div>

            <div class="auth-panel" data-auth-panel="register" style="display:none;">
                <form id="registerForm" data-auth-mode="register">
                    <div class="admin-form-group full-width">
                        <label>Full Name</label>
                        <input type="text" name="name" class="admin-input" placeholder="Your full name" required>
                    </div>
                    <div class="admin-form-group full-width">
                        <label>Email</label>
                        <input type="email" name="email" class="admin-input" placeholder="name@example.com" required>
                    </div>
                    <div class="admin-form-group full-width">
                        <label>Password</label>
                        <input type="password" name="password" class="admin-input" placeholder="Create a password" required>
                    </div>
                    <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 10px;">Create Account</button>
                </form>
            </div>
        </div>
    </div>

    <div class="modal-backdrop" id="questionModal">
        <div class="netflix-modal" style="max-width: 650px;">
            <button class="modal-close-btn" id="closeSupportModal" aria-label="Close support form">
                <i class="fas fa-times"></i>
            </button>
            <div style="padding: 30px 40px;">
                <div class="support-hotline-bar">
                    <div>
                        <strong style="display:block; font-size:1.1rem;">Support Center</strong>
                        <span style="color: var(--text-secondary);">Need help with movies, billing, or account access?</span>
                    </div>
                    <a href="tel:09-RENETFLIX-FREE" class="hotline-call-btn"><i class="fas fa-phone-alt"></i> Call Now</a>
                </div>
                <form id="supportForm">
                    <div class="admin-form-grid">
                        <div class="admin-form-group">
                            <label>Name</label>
                            <input type="text" name="name" class="admin-input" placeholder="Your name">
                        </div>
                        <div class="admin-form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="admin-input" placeholder="you@example.com">
                        </div>
                        <div class="admin-form-group full-width">
                            <label>Category</label>
                            <select name="category" class="admin-select">
                                <option>General Inquiry</option>
                                <option>Account Access</option>
                                <option>Billing & Cost</option>
                                <option>Movie Requests</option>
                                <option>Streaming Quality</option>
                            </select>
                        </div>
                        <div class="admin-form-group full-width">
                            <label>Your Question</label>
                            <textarea name="question" class="review-textarea" placeholder="Tell us how we can help..." required></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 14px;">Submit Question</button>
                </form>
                <div class="support-stream-box" id="supportQuestionList" style="margin-top: 28px;"></div>
            </div>
        </div>
    </div>

    <!-- ======================================================================
         Footer
         ====================================================================== -->
    <footer class="footer" id="siteFooter">
        <div class="footer-inner">
            <!-- Footer Brand -->
            <div class="footer-brand-section">
                <a href="index.php" class="footer-logo">
                    <span class="logo-rene">RENE</span><span class="logo-tflix">TFLIX</span>
                </a>
                <p class="footer-tagline">Watch Free. Stream Anything. No Subscription Required.</p>
                <div class="footer-social-links">
                    <a href="#" class="footer-social-btn" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="footer-social-btn" title="Twitter/X"><i class="fab fa-x-twitter"></i></a>
                    <a href="#" class="footer-social-btn" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="footer-social-btn" title="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

            <!-- Footer Nav Columns -->
            <div class="footer-columns">
                <div class="footer-col">
                    <h4 class="footer-col-title">Navigate</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="#rowTop10">Top 10</a></li>
                        <li><a href="#rowTrending">Trending Now</a></li>
                        <li><a href="#rowAction">Action Movies</a></li>
                        <li><a href="#rowSciFi">Sci-Fi &amp; Fantasy</a></li>
                        <li><a href="#rowComedy">Comedy &amp; Family</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4 class="footer-col-title">Account</h4>
                    <ul>
                        <?php if ($isLoggedIn): ?>
                        <li><a href="#" onclick="handleLogout();">Sign Out</a></li>
                        <?php if ($isAdmin): ?>
                        <li><a href="admin.php"><i class="fas fa-shield-alt" style="margin-right:5px;color:var(--primary-red);"></i>Admin Dashboard</a></li>
                        <?php endif; ?>
                        <?php else: ?>
                        <li><a href="#" onclick="openAuthModal('login')">Sign In</a></li>
                        <li><a href="#" onclick="openAuthModal('register')">Register Free</a></li>
                        <?php endif; ?>
                        <li><a href="#myListRow">My Watchlist</a></li>
                        <li><a href="http://localhost/phpmyadmin" target="_blank">phpMyAdmin</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4 class="footer-col-title">Help &amp; Support</h4>
                    <ul>
                        <li><a href="#faqSection">FAQ</a></li>
                        <li><a href="#" onclick="openQuestionsModal()">Ask a Question</a></li>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Terms of Use</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4 class="footer-col-title">Contact</h4>
                    <ul>
                        <li><a href="tel:09-RENETFLIX-FREE">📞 09-RENETFLIX-FREE</a></li>
                        <li><a href="mailto:support@renetflix.local">✉ support@renetflix.local</a></li>
                        <li><a href="#">Live Chat</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Footer Bottom Bar -->
        <div class="footer-bottom-bar">
            <div class="footer-copy">
                &copy; <?= date('Y') ?> <span style="color: var(--primary-red); font-weight: 700;">RENEtflix</span>. Free Movie Streaming Edition.
            </div>
            <div class="db-status-badge">
                <span class="db-status-dot"></span>
                <span>MySQL (renetflix_db) &bull; XAMPP Active</span>
            </div>
            <div style="font-size:0.78rem;color:var(--text-muted);">
                Powered by PHP &bull; MySQL &bull; XAMPP
            </div>
        </div>
    </footer>

    <!-- App JavaScript -->
    <script src="js/app.js"></script>
</body>
</html>
