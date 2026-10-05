<?php


$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = 'renetflix_db';

try {
    
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname`");

    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS movies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            original_title VARCHAR(255) DEFAULT '',
            slug VARCHAR(255) UNIQUE NOT NULL,
            description TEXT NOT NULL,
            poster_url VARCHAR(255) NOT NULL,
            backdrop_url VARCHAR(255) NOT NULL,
            release_year INT NOT NULL,
            maturity_rating VARCHAR(10) DEFAULT 'PG-13',
            duration VARCHAR(50) NOT NULL,
            match_rate INT DEFAULT 95,
            video_url TEXT NOT NULL,
            trailer_youtube_id VARCHAR(50) DEFAULT '',
            director VARCHAR(255) DEFAULT '',
            cast_members TEXT,
            genres VARCHAR(255) DEFAULT '',
            tags VARCHAR(255) DEFAULT '',
            is_featured TINYINT(1) DEFAULT 0,
            is_trending TINYINT(1) DEFAULT 0,
            top_10_rank INT DEFAULT 0,
            views_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) UNIQUE NOT NULL,
            sort_order INT DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS watchlist (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_profile VARCHAR(50) DEFAULT 'Rene',
            movie_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY user_movie (user_profile, movie_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            movie_id INT NOT NULL,
            user_profile VARCHAR(50) DEFAULT 'Rene',
            rating INT NOT NULL,
            review_text TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS watch_progress (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_profile VARCHAR(50) DEFAULT 'Rene',
            movie_id INT NOT NULL,
            progress_percent INT DEFAULT 0,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY user_progress (user_profile, movie_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            avatar VARCHAR(255) DEFAULT 'assets/avatar_rene.svg',
            active_profile VARCHAR(50) DEFAULT 'Rene',
            role VARCHAR(20) NOT NULL DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(20) NOT NULL DEFAULT 'user'");

    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_profiles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            profile_name VARCHAR(50) NOT NULL,
            avatar_url VARCHAR(255) NOT NULL,
            is_kids TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY user_profile_unique (user_id, profile_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS questions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL,
            category VARCHAR(100) NOT NULL DEFAULT 'General',
            question TEXT NOT NULL,
            answer TEXT,
            status VARCHAR(20) DEFAULT 'Answered',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($userCount == 0) {
        $reneHash = password_hash('password123', PASSWORD_DEFAULT);
        $sherlynHash = password_hash('password123', PASSWORD_DEFAULT);

        $uStmt = $pdo->prepare("INSERT INTO users (name, email, password, avatar, active_profile, role) VALUES (?, ?, ?, ?, ?, ?)");
        $uStmt->execute(['Rene', 'rene@renetflix.com', $reneHash, 'assets/avatar_rene.svg', 'Rene', 'admin']);
        $reneId = $pdo->lastInsertId();

        $uStmt->execute(['Sherlyn', 'sherlyn@renetflix.com', $sherlynHash, 'assets/avatar_sherlyn.svg', 'Sherlyn', 'admin']);
        $sherlynId = $pdo->lastInsertId();

        
        $pStmt = $pdo->prepare("INSERT INTO user_profiles (user_id, profile_name, avatar_url, is_kids) VALUES (?, ?, ?, ?)");
        $pStmt->execute([$reneId, 'Rene', 'assets/avatar_rene.svg', 0]);
        $pStmt->execute([$reneId, 'Sherlyn', 'assets/avatar_sherlyn.svg', 0]);
        $pStmt->execute([$reneId, 'Kids', 'assets/avatar_kids.svg', 1]);
        $pStmt->execute([$reneId, 'Guest', 'assets/avatar_guest.svg', 0]);

        
        $pStmt->execute([$sherlynId, 'Sherlyn', 'assets/avatar_sherlyn.svg', 0]);
        $pStmt->execute([$sherlynId, 'Kids', 'assets/avatar_kids.svg', 1]);
    }

    
    $qCount = $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
    if ($qCount == 0) {
        $qStmt = $pdo->prepare("INSERT INTO questions (name, email, category, question, answer) VALUES (?, ?, ?, ?, ?)");
        $qStmt->execute([
            'Rene',
            'rene@renetflix.com',
            'Billing & Cost',
            'Is RENEtflix really 100% free with no hidden charges?',
            'Yes! RENEtflix is completely free. There are no credit cards needed, no subscription tiers, and no trial expiration. You can watch anytime at zero cost.'
        ]);
        $qStmt->execute([
            'Sherlyn',
            'sherlyn@renetflix.com',
            'Movie Requests',
            'Can I request or add movies to the streaming library?',
            'Absolutely! You can click the "+ Add Movie" button in the top navigation bar to add any movie from your local Movies folder or online MP4 video link directly into the database.'
        ]);
        $qStmt->execute([
            'Alex',
            'alex@gmail.com',
            'Streaming Quality',
            'What resolution are movies streamed in on RENEtflix?',
            'Movies are delivered in Full HD and Ultra HD 4K with spatial sound and high-speed local streaming.'
        ]);
    }

    
    $catCount = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    if ($catCount == 0) {
        $categories = [
            ['Trending Now', 'trending', 1],
            ['Action & Blockbusters', 'action', 2],
            ['Sci-Fi & Fantasy', 'scifi', 3],
            ['Comedy & Laughs', 'comedy', 4],
            ['Thrillers & Suspense', 'thriller', 5],
            ['Family & Animation', 'family', 6],
            ['Top 10 Today', 'top10', 7],
        ];
        $catStmt = $pdo->prepare("INSERT INTO categories (name, slug, sort_order) VALUES (?, ?, ?)");
        foreach ($categories as $cat) {
            $catStmt->execute($cat);
        }
    }

    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(20) NOT NULL DEFAULT 'user'");
    $pdo->exec("UPDATE users SET role = 'admin' WHERE email IN ('rene@renetflix.com', 'sherlyn@renetflix.com') AND role != 'admin'");

    
    $movieCount = $pdo->query("SELECT COUNT(*) FROM movies")->fetchColumn();
    if ($movieCount == 0) {
        $initialMovies = [
            [
                'title' => 'Avengers: Endgame (Encore Edition)',
                'original_title' => 'Marvel Studios Avengers: Endgame',
                'slug' => 'avengers-endgame',
                'description' => 'After the devastating cosmic snap in Infinity War, the universe lies in ruins. Robert Downey Jr., Chris Evans, and the surviving Avengers assemble once more to travel through time, confront Thanos, and restore cosmic balance at any cost in this epic cinematic milestone.',
                'poster_url' => 'Movies/movie1.jpeg',
                'backdrop_url' => 'assets/hero_avengers.jpg',
                'release_year' => 2019,
                'maturity_rating' => 'PG-13',
                'duration' => '3h 2min',
                'match_rate' => 99,
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4',
                'trailer_youtube_id' => 'TcMBFSGVi1c',
                'director' => 'Anthony Russo, Joe Russo',
                'cast_members' => 'Robert Downey Jr., Chris Evans, Mark Ruffalo, Chris Hemsworth, Scarlett Johansson, Jeremy Renner, Paul Rudd',
                'genres' => 'Action, Sci-Fi, Adventure, Superhero',
                'tags' => 'Epic, Mind-Bending, Heartfelt, Action-Packed',
                'is_featured' => 1,
                'is_trending' => 1,
                'top_10_rank' => 1
            ],
            [
                'title' => 'Avatar: Fire and Ash',
                'original_title' => 'Avatar 3: Fire and Ash',
                'slug' => 'avatar-fire-and-ash',
                'description' => 'James Cameron takes audiences into the volcanic realm of Pandora. Jake Sully and Neytiri encounter the Ash People—a fierce, war-ready volcanic clan of Na\'vi led by Varang who threaten to shatter the fragile peace across the bioluminescent biosphere.',
                'poster_url' => 'Movies/movie5.jpg',
                'backdrop_url' => 'assets/hero_avatar.jpg',
                'release_year' => 2025,
                'maturity_rating' => 'PG-13',
                'duration' => '3h 15min',
                'match_rate' => 99,
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/Sintel.mp4',
                'trailer_youtube_id' => 'nb_mGepwG2Q',
                'director' => 'James Cameron',
                'cast_members' => 'Sam Worthington, Zoe Saldaña, Sigourney Weaver, Stephen Lang, Michelle Yeoh, Oona Chaplin, David Thewlis',
                'genres' => 'Sci-Fi, Action, Adventure, Fantasy',
                'tags' => 'Visually Stunning, Alien Worlds, Epic Fantasy, Sci-Fi Blockbuster',
                'is_featured' => 1,
                'is_trending' => 1,
                'top_10_rank' => 2
            ],
            [
                'title' => 'Kraven the Hunter',
                'original_title' => 'Marvel Studios & Sony: Kraven the Hunter',
                'slug' => 'kraven-the-hunter',
                'description' => 'Villains aren\'t born. They\'re made. Sergei Kravinoff\'s complex and brutal relationship with his ruthless crime boss father puts him on a path of unrelenting vengeance, awakening unmatched predatory instincts to become the world\'s greatest apex hunter.',
                'poster_url' => 'Movies/movie8.jpg',
                'backdrop_url' => 'assets/hero_kraven.jpg',
                'release_year' => 2024,
                'maturity_rating' => 'R',
                'duration' => '2h 07min',
                'match_rate' => 97,
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
                'trailer_youtube_id' => 'rze8QYwWGMs',
                'director' => 'J.C. Chandor',
                'cast_members' => 'Aaron Taylor-Johnson, Ariana DeBose, Russell Crowe, Fred Hechinger, Alessandro Nivola, Christopher Abbott',
                'genres' => 'Action, Thriller, Antihero, Sci-Fi',
                'tags' => 'Gritty, High-Octane, Dark, Marvel Antihero',
                'is_featured' => 1,
                'is_trending' => 1,
                'top_10_rank' => 3
            ],
            [
                'title' => 'The Runner',
                'original_title' => 'The Runner (Prime Original)',
                'slug' => 'the-runner',
                'description' => 'One hour. One chance to save her son. An elite former intelligence agent (Gal Gadot) is forced to navigate a deadly gauntlet through high-density urban transit systems while eluding assassins and solving high-stakes puzzles against the clock.',
                'poster_url' => 'Movies/movie6.webp',
                'backdrop_url' => 'Movies/movie6.webp',
                'release_year' => 2026,
                'maturity_rating' => 'PG-13',
                'duration' => '1h 56min',
                'match_rate' => 96,
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4',
                'trailer_youtube_id' => 'mqqft2x_Aa4',
                'director' => 'Scott Waugh',
                'cast_members' => 'Gal Gadot, Karl Urban, Djimon Hounsou, Frank Grillo',
                'genres' => 'Action, Thriller, Crime, Suspense',
                'tags' => 'Fast-Paced, Adrenaline Rush, High Stakes, Heist',
                'is_featured' => 0,
                'is_trending' => 1,
                'top_10_rank' => 4
            ],
            [
                'title' => 'Hexed',
                'original_title' => 'Disney Hexed',
                'slug' => 'hexed',
                'description' => 'From the creators of Frozen and Zootopia! Something strange is happening to Billie. When she stumbles upon an eccentric family heirloom, the world around her spins into an enchanting whirlwind of mischievous spells, floating sneakers, and heartwarming chaos.',
                'poster_url' => 'Movies/movie4.jpeg',
                'backdrop_url' => 'Movies/movie4.jpeg',
                'release_year' => 2026,
                'maturity_rating' => 'PG',
                'duration' => '1h 42min',
                'match_rate' => 95,
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
                'trailer_youtube_id' => '0jF3h2R9-dE',
                'director' => 'Josie Trinidad, Jason Hand',
                'cast_members' => 'Billie Eilish, Maya Rudolph, Jack Black, Awkwafina',
                'genres' => 'Animation, Fantasy, Family, Comedy',
                'tags' => 'Magical, Feel-Good, Family Fun, Disney Magic',
                'is_featured' => 0,
                'is_trending' => 1,
                'top_10_rank' => 5
            ],
            [
                'title' => 'Scary Movie (New Extended Cut)',
                'original_title' => 'Scary Movie: Unrated Special Edition',
                'slug' => 'scary-movie',
                'description' => 'The definitive spoof classic that changed horror parody forever! Cindy Campbell and her utterly clueless classmates are terrorized by Ghostface in hilarious satirical takes on Scream, I Know What You Did Last Summer, and The Matrix.',
                'poster_url' => 'Movies/movie2.jpg',
                'backdrop_url' => 'Movies/movie2.jpg',
                'release_year' => 2000,
                'maturity_rating' => 'R',
                'duration' => '1h 30min',
                'match_rate' => 94,
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
                'trailer_youtube_id' => 'vP8i87zRsh0',
                'director' => 'Keenen Ivory Wayans',
                'cast_members' => 'Anna Faris, Marlon Wayans, Shawn Wayans, Regina Hall, Shannon Elizabeth, Carmen Electra',
                'genres' => 'Comedy, Parody, Horror',
                'tags' => 'Hilarious, Cult Classic, Slapstick, Late Night',
                'is_featured' => 0,
                'is_trending' => 1,
                'top_10_rank' => 6
            ],
            [
                'title' => 'Fall 2: Deadpoint',
                'original_title' => 'Fall 2: Deadpoint',
                'slug' => 'fall-2-deadpoint',
                'description' => 'Pushing acrophobia to extreme heights! Determined to confront past trauma, two thrill-seeking rock climbers scale a treacherous 3,000-foot seaside cliff overhang. When the antique wooden scaffold crumbles behind them, survival teeters over the abyss.',
                'poster_url' => 'Movies/movie9.jpg',
                'backdrop_url' => 'Movies/movie9.jpg',
                'release_year' => 2026,
                'maturity_rating' => 'PG-13',
                'duration' => '1h 50min',
                'match_rate' => 93,
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4',
                'trailer_youtube_id' => '1356v0yB3bQ',
                'director' => 'Peter Thorwarth',
                'cast_members' => 'Virginia Gardner, Grace Caroline Currey, Jeffrey Dean Morgan',
                'genres' => 'Thriller, Survival, Adventure',
                'tags' => 'Edge of Your Seat, Dizzying, Intense, Suspense',
                'is_featured' => 0,
                'is_trending' => 0,
                'top_10_rank' => 7
            ],
            [
                'title' => 'Snow White',
                'original_title' => 'Disney Snow White Live Action',
                'slug' => 'snow-white-2025',
                'description' => 'A visually enchanting live-action musical reimagining of the timeless fairy tale. Rachel Zegler stars as Snow White alongside Gal Gadot as the commanding Evil Queen, bringing classic songs and magical forest adventures to life.',
                'poster_url' => 'Movies/movie3.jpeg',
                'backdrop_url' => 'Movies/movie3.jpeg',
                'release_year' => 2025,
                'maturity_rating' => 'PG',
                'duration' => '1h 48min',
                'match_rate' => 92,
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
                'trailer_youtube_id' => 'TbiPemzPTKM',
                'director' => 'Marc Webb',
                'cast_members' => 'Rachel Zegler, Gal Gadot, Andrew Burnap, Ansu Kabia',
                'genres' => 'Fantasy, Musical, Adventure, Family',
                'tags' => 'Whimsical, Musical, Nostalgic, Fairy Tale',
                'is_featured' => 0,
                'is_trending' => 0,
                'top_10_rank' => 8
            ],
            [
                'title' => 'One Mile: Chapter Two',
                'original_title' => 'One Mile: Chapter Two',
                'slug' => 'one-mile-chapter-two',
                'description' => 'Ryan Phillippe stars as an ex-convict trying to rebuild trust with his daughter during a scenic cross-country university tour. Their journey turns into an unforgiving fight for survival when they cross paths with a rogue Appalachian paramilitary squad.',
                'poster_url' => 'Movies/movie7.jpg',
                'backdrop_url' => 'Movies/movie7.jpg',
                'release_year' => 2026,
                'maturity_rating' => 'R',
                'duration' => '1h 45min',
                'match_rate' => 91,
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
                'trailer_youtube_id' => 'Jg9Z9yvXo6M',
                'director' => 'Adam Davidson',
                'cast_members' => 'Ryan Phillippe, Amélie Hoeferle, Josh Duhamel, Richard Roxburgh',
                'genres' => 'Thriller, Action, Drama, Crime',
                'tags' => 'Gripping, Raw, Cat-and-Mouse, Survival',
                'is_featured' => 0,
                'is_trending' => 0,
                'top_10_rank' => 9
            ],
            [
                'title' => 'The Thundermans: Clash of the Thundermans',
                'original_title' => 'Clash of the Thundermans',
                'slug' => 'clash-of-the-thundermans',
                'description' => 'The super-powered family returns! Twin siblings Phoebe and Max find their hero team put to the ultimate test when an old rival surfaces with an arsenal that threatens Hiddenville. Fast-paced family superhero antics and laughs for all ages!',
                'poster_url' => 'Movies/movie10.jpg',
                'backdrop_url' => 'Movies/movie10.jpg',
                'release_year' => 2024,
                'maturity_rating' => 'PG',
                'duration' => '1h 35min',
                'match_rate' => 90,
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerFun.mp4',
                'trailer_youtube_id' => 'hU1cT2yT_a8',
                'director' => 'Trevor Kirschner',
                'cast_members' => 'Kira Kosarin, Jack Griffo, Addison Riecke, Diego Velazquez, Chris Tallman, Rosa Blasi',
                'genres' => 'Comedy, Action, Family, Superhero',
                'tags' => 'Funny, Superhero Family, Energetic, Laugh-Out-Loud',
                'is_featured' => 0,
                'is_trending' => 0,
                'top_10_rank' => 10
            ],
        ];

        $stmt = $pdo->prepare("
            INSERT INTO movies (
                title, original_title, slug, description, poster_url, backdrop_url,
                release_year, maturity_rating, duration, match_rate, video_url,
                trailer_youtube_id, director, cast_members, genres, tags,
                is_featured, is_trending, top_10_rank
            ) VALUES (
                :title, :original_title, :slug, :description, :poster_url, :backdrop_url,
                :release_year, :maturity_rating, :duration, :match_rate, :video_url,
                :trailer_youtube_id, :director, :cast_members, :genres, :tags,
                :is_featured, :is_trending, :top_10_rank
            )
        ");

        foreach ($initialMovies as $m) {
            $stmt->execute($m);
        }

        
        $revStmt = $pdo->prepare("INSERT INTO reviews (movie_id, user_profile, rating, review_text) VALUES (?, ?, ?, ?)");
        $revStmt->execute([1, 'Rene', 5, 'Masterpiece of cinema! The final battle gives me goosebumps every single time. 10/10 streaming on RENEtflix!']);
        $revStmt->execute([2, 'Sherlyn', 5, 'Still one of the funniest parody movies ever made. Uncensored version is gold!']);
        $revStmt->execute([5, 'Alex', 5, 'The visuals of the Fire and Ash tribe look groundbreaking. Absolutely incredible!']);
        $revStmt->execute([8, 'Rene', 4, 'Aaron Taylor-Johnson plays Kraven with insane intensity. High-octane action!']);
    }

} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}


function getAllMovies($pdo, $category = null, $search = null) {
    $sql = "SELECT * FROM movies WHERE 1=1";
    $params = [];

    if ($search) {
        $sql .= " AND (title LIKE :search OR genres LIKE :search OR cast_members LIKE :search OR director LIKE :search)";
        $params[':search'] = "%$search%";
    }

    if ($category && $category !== 'all') {
        if ($category === 'trending') {
            $sql .= " AND is_trending = 1";
        } elseif ($category === 'top10') {
            $sql .= " AND top_10_rank > 0 ORDER BY top_10_rank ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } else {
            $sql .= " AND genres LIKE :cat";
            $params[':cat'] = "%$category%";
        }
    }

    $sql .= " ORDER BY top_10_rank ASC, id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
