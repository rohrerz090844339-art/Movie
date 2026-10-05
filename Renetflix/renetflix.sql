





;
;
;
;
;
;
;
;
;
;





DROP TABLE IF EXISTS `categories`;
;
;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
;





LOCK TABLES `categories` WRITE;
;
INSERT INTO `categories` VALUES (1,'Trending Now','trending',1),(2,'Action & Blockbusters','action',2),(3,'Sci-Fi & Fantasy','scifi',3),(4,'Comedy & Laughs','comedy',4),(5,'Thrillers & Suspense','thriller',5),(6,'Family & Animation','family',6),(7,'Top 10 Today','top10',7);
;
UNLOCK TABLES;





DROP TABLE IF EXISTS `movies`;
;
;
CREATE TABLE `movies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `original_title` varchar(255) DEFAULT '',
  `slug` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `poster_url` varchar(255) NOT NULL,
  `backdrop_url` varchar(255) NOT NULL,
  `release_year` int(11) NOT NULL,
  `maturity_rating` varchar(10) DEFAULT 'PG-13',
  `duration` varchar(50) NOT NULL,
  `match_rate` int(11) DEFAULT 95,
  `video_url` text NOT NULL,
  `trailer_youtube_id` varchar(50) DEFAULT '',
  `director` varchar(255) DEFAULT '',
  `cast_members` text DEFAULT NULL,
  `genres` varchar(255) DEFAULT '',
  `tags` varchar(255) DEFAULT '',
  `is_featured` tinyint(1) DEFAULT 0,
  `is_trending` tinyint(1) DEFAULT 0,
  `top_10_rank` int(11) DEFAULT 0,
  `views_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
;





LOCK TABLES `movies` WRITE;
;
INSERT INTO `movies` VALUES (1,'Avengers: Endgame (Encore Edition)','Marvel Studios Avengers: Endgame','avengers-endgame','After the devastating cosmic snap in Infinity War, the universe lies in ruins. Robert Downey Jr., Chris Evans, and the surviving Avengers assemble once more to travel through time, confront Thanos, and restore cosmic balance at any cost in this epic cinematic milestone.','Movies/movie1.jpeg','assets/hero_avengers.jpg',2019,'PG-13','3h 2min',99,'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4','TcMBFSGVi1c','Anthony Russo, Joe Russo','Robert Downey Jr., Chris Evans, Mark Ruffalo, Chris Hemsworth, Scarlett Johansson, Jeremy Renner, Paul Rudd','Action, Sci-Fi, Adventure, Superhero','Epic, Mind-Bending, Heartfelt, Action-Packed',1,1,1,0,'2026-10-03 18:57:27'),(2,'Avatar: Fire and Ash','Avatar 3: Fire and Ash','avatar-fire-and-ash','James Cameron takes audiences into the volcanic realm of Pandora. Jake Sully and Neytiri encounter the Ash PeopleΓÇöa fierce, war-ready volcanic clan of Na\'vi led by Varang who threaten to shatter the fragile peace across the bioluminescent biosphere.','Movies/movie5.jpg','assets/hero_avatar.jpg',2025,'PG-13','3h 15min',99,'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/Sintel.mp4','nb_mGepwG2Q','James Cameron','Sam Worthington, Zoe Salda├▒a, Sigourney Weaver, Stephen Lang, Michelle Yeoh, Oona Chaplin, David Thewlis','Sci-Fi, Action, Adventure, Fantasy','Visually Stunning, Alien Worlds, Epic Fantasy, Sci-Fi Blockbuster',1,1,2,0,'2026-10-03 18:57:27'),(3,'Kraven the Hunter','Marvel Studios & Sony: Kraven the Hunter','kraven-the-hunter','Villains aren\'t born. They\'re made. Sergei Kravinoff\'s complex and brutal relationship with his ruthless crime boss father puts him on a path of unrelenting vengeance, awakening unmatched predatory instincts to become the world\'s greatest apex hunter.','Movies/movie8.jpg','assets/hero_kraven.jpg',2024,'R','2h 07min',97,'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4','rze8QYwWGMs','J.C. Chandor','Aaron Taylor-Johnson, Ariana DeBose, Russell Crowe, Fred Hechinger, Alessandro Nivola, Christopher Abbott','Action, Thriller, Antihero, Sci-Fi','Gritty, High-Octane, Dark, Marvel Antihero',1,1,3,0,'2026-10-03 18:57:27'),(4,'The Runner','The Runner (Prime Original)','the-runner','One hour. One chance to save her son. An elite former intelligence agent (Gal Gadot) is forced to navigate a deadly gauntlet through high-density urban transit systems while eluding assassins and solving high-stakes puzzles against the clock.','Movies/movie6.webp','Movies/movie6.webp',2026,'PG-13','1h 56min',96,'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4','mqqft2x_Aa4','Scott Waugh','Gal Gadot, Karl Urban, Djimon Hounsou, Frank Grillo','Action, Thriller, Crime, Suspense','Fast-Paced, Adrenaline Rush, High Stakes, Heist',0,1,4,0,'2026-10-03 18:57:27'),(5,'Hexed','Disney Hexed','hexed','From the creators of Frozen and Zootopia! Something strange is happening to Billie. When she stumbles upon an eccentric family heirloom, the world around her spins into an enchanting whirlwind of mischievous spells, floating sneakers, and heartwarming chaos.','Movies/movie4.jpeg','Movies/movie4.jpeg',2026,'PG','1h 42min',95,'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4','0jF3h2R9-dE','Josie Trinidad, Jason Hand','Billie Eilish, Maya Rudolph, Jack Black, Awkwafina','Animation, Fantasy, Family, Comedy','Magical, Feel-Good, Family Fun, Disney Magic',0,1,5,0,'2026-10-03 18:57:27'),(6,'Scary Movie (New Extended Cut)','Scary Movie: Unrated Special Edition','scary-movie','The definitive spoof classic that changed horror parody forever! Cindy Campbell and her utterly clueless classmates are terrorized by Ghostface in hilarious satirical takes on Scream, I Know What You Did Last Summer, and The Matrix.','Movies/movie2.jpg','Movies/movie2.jpg',2000,'R','1h 30min',94,'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4','vP8i87zRsh0','Keenen Ivory Wayans','Anna Faris, Marlon Wayans, Shawn Wayans, Regina Hall, Shannon Elizabeth, Carmen Electra','Comedy, Parody, Horror','Hilarious, Cult Classic, Slapstick, Late Night',0,1,6,0,'2026-10-03 18:57:27'),(7,'Fall 2: Deadpoint','Fall 2: Deadpoint','fall-2-deadpoint','Pushing acrophobia to extreme heights! Determined to confront past trauma, two thrill-seeking rock climbers scale a treacherous 3,000-foot seaside cliff overhang. When the antique wooden scaffold crumbles behind them, survival teeters over the abyss.','Movies/movie9.jpg','Movies/movie9.jpg',2026,'PG-13','1h 50min',93,'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4','1356v0yB3bQ','Peter Thorwarth','Virginia Gardner, Grace Caroline Currey, Jeffrey Dean Morgan','Thriller, Survival, Adventure','Edge of Your Seat, Dizzying, Intense, Suspense',0,0,7,0,'2026-10-03 18:57:27'),(8,'Snow White','Disney Snow White Live Action','snow-white-2025','A visually enchanting live-action musical reimagining of the timeless fairy tale. Rachel Zegler stars as Snow White alongside Gal Gadot as the commanding Evil Queen, bringing classic songs and magical forest adventures to life.','Movies/movie3.jpeg','Movies/movie3.jpeg',2025,'PG','1h 48min',92,'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4','TbiPemzPTKM','Marc Webb','Rachel Zegler, Gal Gadot, Andrew Burnap, Ansu Kabia','Fantasy, Musical, Adventure, Family','Whimsical, Musical, Nostalgic, Fairy Tale',0,0,8,0,'2026-10-03 18:57:27'),(9,'One Mile: Chapter Two','One Mile: Chapter Two','one-mile-chapter-two','Ryan Phillippe stars as an ex-convict trying to rebuild trust with his daughter during a scenic cross-country university tour. Their journey turns into an unforgiving fight for survival when they cross paths with a rogue Appalachian paramilitary squad.','Movies/movie7.jpg','Movies/movie7.jpg',2026,'R','1h 45min',91,'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4','Jg9Z9yvXo6M','Adam Davidson','Ryan Phillippe, Am├⌐lie Hoeferle, Josh Duhamel, Richard Roxburgh','Thriller, Action, Drama, Crime','Gripping, Raw, Cat-and-Mouse, Survival',0,0,9,0,'2026-10-03 18:57:27'),(10,'The Thundermans: Clash of the Thundermans','Clash of the Thundermans','clash-of-the-thundermans','The super-powered family returns! Twin siblings Phoebe and Max find their hero team put to the ultimate test when an old rival surfaces with an arsenal that threatens Hiddenville. Fast-paced family superhero antics and laughs for all ages!','Movies/movie10.jpg','Movies/movie10.jpg',2024,'PG','1h 35min',90,'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerFun.mp4','hU1cT2yT_a8','Trevor Kirschner','Kira Kosarin, Jack Griffo, Addison Riecke, Diego Velazquez, Chris Tallman, Rosa Blasi','Comedy, Action, Family, Superhero','Funny, Superhero Family, Energetic, Laugh-Out-Loud',0,0,10,0,'2026-10-03 18:57:27');
;
UNLOCK TABLES;





DROP TABLE IF EXISTS `reviews`;
;
;
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `movie_id` int(11) NOT NULL,
  `user_profile` varchar(50) DEFAULT 'Rene',
  `rating` int(11) NOT NULL,
  `review_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
;





LOCK TABLES `reviews` WRITE;
;
INSERT INTO `reviews` VALUES (1,1,'Rene',5,'Masterpiece of cinema! The final battle gives me goosebumps every single time. 10/10 streaming on RENEtflix!','2026-10-03 18:57:27'),(2,2,'Sherlyn',5,'Still one of the funniest parody movies ever made. Uncensored version is gold!','2026-10-03 18:57:27'),(3,5,'Alex',5,'The visuals of the Fire and Ash tribe look groundbreaking. Absolutely incredible!','2026-10-03 18:57:27'),(4,8,'Rene',4,'Aaron Taylor-Johnson plays Kraven with insane intensity. High-octane action!','2026-10-03 18:57:27');
;
UNLOCK TABLES;





DROP TABLE IF EXISTS `watch_progress`;
;
;
CREATE TABLE `watch_progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_profile` varchar(50) DEFAULT 'Rene',
  `movie_id` int(11) NOT NULL,
  `progress_percent` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_progress` (`user_profile`,`movie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
;





LOCK TABLES `watch_progress` WRITE;
;
;
UNLOCK TABLES;





DROP TABLE IF EXISTS `watchlist`;
;
;
CREATE TABLE `watchlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_profile` varchar(50) DEFAULT 'Rene',
  `movie_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_movie` (`user_profile`,`movie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
;





LOCK TABLES `watchlist` WRITE;
;
;
UNLOCK TABLES;
;

;
;
;
;
;
;
;


