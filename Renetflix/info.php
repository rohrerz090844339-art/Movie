<?php
$pages = [
    'help' => [
        'title' => 'Help Center',
        'intro' => 'Find help with your RENEtflix account, saved movies, and playback.',
        'sections' => [
            [
                'heading' => 'Signing in and registration',
                'body' => 'Use Login from the home page to sign in with your registered email and password. New viewers can choose Register and create an account. If you cannot access your account, use the support form and include the email address on the account.'
            ],
            [
                'heading' => 'My List and Favorites',
                'body' => 'Sign in before saving a title. Use the bookmark control to add it to My List or the thumbs-up control to add it to Favorites. Your saved titles are stored with your account and are available from their sections on the home page.'
            ],
            [
                'heading' => 'Movie playback',
                'body' => 'Choose a movie and press Play. Playback depends on the video source being available and compatible with your browser. Check your internet connection and try another title if a stream does not load.'
            ],
            [
                'heading' => 'Contact support',
                'body' => 'Send a question using the support form. Include a short description of the problem and the page or movie you were using. Do not include your password.'
            ]
        ]
    ],
    'terms' => [
        'title' => 'Terms of Use',
        'intro' => 'These terms explain the basic rules for using this RENEtflix application.',
        'sections' => [
            [
                'heading' => 'Using the service',
                'body' => 'Use the application lawfully and do not attempt to disrupt, damage, or gain unauthorized access to the site, its accounts, or its database.'
            ],
            [
                'heading' => 'Your account',
                'body' => 'You are responsible for keeping your sign-in credentials private and for activity performed through your account. Provide accurate registration details and contact support if you believe your account has been accessed without permission.'
            ],
            [
                'heading' => 'Movies and availability',
                'body' => 'Movie information and playback sources may change or become unavailable. The application is provided as-is; uninterrupted playback and availability of external media sources are not guaranteed.'
            ],
            [
                'heading' => 'Changes and contact',
                'body' => 'These terms may be updated as the application changes. Continued use after an update means you accept the revised terms. Contact support through the Help Center if you have questions.'
            ]
        ]
    ],
    'privacy' => [
        'title' => 'Privacy Policy',
        'intro' => 'This policy describes the account and activity information this RENEtflix application stores.',
        'sections' => [
            [
                'heading' => 'Information stored',
                'body' => 'When you register, the application stores your name, email address, a securely hashed password, and account creation details. It also stores the titles you save to My List or Favorites, profile settings, reviews, and support questions you submit.'
            ],
            [
                'heading' => 'How information is used',
                'body' => 'Account information is used to sign you in, keep saved titles associated with your account, operate the application, and respond to support questions. Passwords are verified using password hashes; the original password is not stored.'
            ],
            [
                'heading' => 'Cookies and database storage',
                'body' => 'A session cookie keeps you signed in while you use the site. Application account and saved-title records are stored in the RENEtflix MySQL database configured by the site operator.'
            ],
            [
                'heading' => 'Your choices and contact',
                'body' => 'Do not share your password in support messages. Contact the site operator through the Help Center to ask about your account information or request assistance.'
            ]
        ]
    ]
];

$pageKey = $_GET['page'] ?? 'help';
if (!isset($pages[$pageKey])) {
    http_response_code(404);
    $pageKey = 'help';
}
$page = $pages[$pageKey];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page['title']) ?> | RENEtflix</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg?v=2">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=20261009">
</head>
<body class="info-page">
    <main class="info-shell">
        <header class="info-header">
            <a href="index.php" class="brand-logo" aria-label="RENEtflix home">
                <span class="logo-rene">RENE</span><span class="logo-tflix">TFLIX</span>
                <span class="free-tag">FREE</span>
            </a>
            <a class="info-home-link" href="index.php">Back to RENEtflix</a>
        </header>

        <article class="info-card">
            <nav class="info-nav" aria-label="Help and policies">
                <a href="info.php?page=help" <?= $pageKey === 'help' ? 'aria-current="page"' : '' ?>>Help Center</a>
                <a href="info.php?page=terms" <?= $pageKey === 'terms' ? 'aria-current="page"' : '' ?>>Terms of Use</a>
                <a href="info.php?page=privacy" <?= $pageKey === 'privacy' ? 'aria-current="page"' : '' ?>>Privacy Policy</a>
            </nav>
            <h1><?= htmlspecialchars($page['title']) ?></h1>
            <p class="info-intro"><?= htmlspecialchars($page['intro']) ?></p>

            <div class="info-sections">
                <?php foreach ($page['sections'] as $section): ?>
                    <section>
                        <h2><?= htmlspecialchars($section['heading']) ?></h2>
                        <p><?= htmlspecialchars($section['body']) ?></p>
                    </section>
                <?php endforeach; ?>
            </div>

            <?php if ($pageKey === 'help'): ?>
                <div class="info-actions">
                    <a class="btn-primary" href="index.php?open_support=1">Contact Support</a>
                    <a class="btn-secondary" href="index.php#faqSection">Browse FAQs</a>
                </div>
            <?php endif; ?>
        </article>

        <footer class="info-footer">
            <span>&copy; <?= date('Y') ?> RENEtflix</span>
            <a href="info.php?page=privacy">Privacy</a>
            <a href="info.php?page=terms">Terms</a>
        </footer>
    </main>
</body>
</html>
