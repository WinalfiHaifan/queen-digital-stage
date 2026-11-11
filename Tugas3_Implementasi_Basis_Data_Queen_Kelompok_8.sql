-- TUGAS 3: IMPLEMENTASI BASIS DATA
-- Queen Digital Stage | Kelompok 8 | MySQL / MariaDB
--
-- Import this file into phpMyAdmin for a NEW local database.
-- The script creates/uses queen_digital_stage, defines all 11 tables and
-- foreign-key relationships, then inserts the demo archive/catalog data.
-- It does not include live orders, fan submissions, hosting credentials,
-- or an administrator password. Run only once against an empty database.

SET NAMES utf8mb4;
CREATE DATABASE IF NOT EXISTS `queen_digital_stage`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `queen_digital_stage`;

-- 1. Core schema: 9 tables
-- Queen Digital Stage. Run in the target database with utf8mb4.

-- Source: Rancangan Akhir Basis Data, sheets 2 through 8.

CREATE TABLE IF NOT EXISTS `admin_users` (
    `admin_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(254) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CHECK (is_active IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `band_members` (
    `member_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `member_name` VARCHAR(100) NOT NULL,
    `birth_date` DATE NULL DEFAULT NULL,
    `role` VARCHAR(100) NOT NULL,
    `biography` TEXT NULL DEFAULT NULL,
    `photo_url` VARCHAR(500) NULL DEFAULT NULL,
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `albums` (
    `album_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `album_title` VARCHAR(150) NOT NULL,
    `release_year` YEAR NULL DEFAULT NULL,
    `genre` VARCHAR(100) NULL DEFAULT NULL,
    `cover_url` VARCHAR(500) NULL DEFAULT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `spotify_url` VARCHAR(500) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `songs` (
    `song_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `album_id` INT UNSIGNED NULL DEFAULT NULL,
    `song_title` VARCHAR(150) NOT NULL,
    `release_year` YEAR NULL DEFAULT NULL,
    `genre` VARCHAR(100) NULL DEFAULT NULL,
    `songwriter_credit` VARCHAR(255) NULL DEFAULT NULL,
    `summary` VARCHAR(500) NULL DEFAULT NULL,
    `story` TEXT NULL DEFAULT NULL,
    `spotify_url` VARCHAR(500) NULL DEFAULT NULL,
    `youtube_video_id` VARCHAR(20) NULL DEFAULT NULL,
    `cover_url` VARCHAR(500) NULL DEFAULT NULL,
    `is_featured_song` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_songs_album (album_id),
    CONSTRAINT fk_songs_album FOREIGN KEY (album_id) REFERENCES albums (album_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CHECK (is_featured_song IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `timeline_events` (
    `event_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `event_title` VARCHAR(200) NOT NULL,
    `event_year` YEAR NULL DEFAULT NULL,
    `event_type` ENUM('history', 'award') NOT NULL DEFAULT 'history',
    `description` TEXT NOT NULL,
    `image_url` VARCHAR(500) NULL DEFAULT NULL,
    `source_url` VARCHAR(500) NULL DEFAULT NULL,
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `media_items` (
    `media_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `media_title` VARCHAR(200) NOT NULL,
    `media_type` ENUM('image', 'video') NOT NULL,
    `category` ENUM('band', 'member', 'live', 'album', 'archive') NOT NULL,
    `media_url` VARCHAR(500) NOT NULL,
    `thumbnail_url` VARCHAR(500) NULL DEFAULT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `media_year` YEAR NULL DEFAULT NULL,
    `source_url` VARCHAR(500) NULL DEFAULT NULL,
    `album_id` INT UNSIGNED NULL DEFAULT NULL,
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_media_album (album_id),
    CONSTRAINT fk_media_album FOREIGN KEY (album_id) REFERENCES albums (album_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_categories` (
    `category_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `category_name` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `products` (
    `product_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT UNSIGNED NOT NULL,
    `product_name` VARCHAR(150) NOT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `currency` CHAR(3) NOT NULL DEFAULT 'USD',
    `stock` INT UNSIGNED NOT NULL DEFAULT 0,
    `weight_gram` DECIMAL(10,2) NOT NULL,
    `image_url` VARCHAR(500) NULL DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_products_category (category_id),
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES product_categories (category_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CHECK (price >= 0),
    CHECK (stock >= 0),
    CHECK (weight_gram >= 0),
    CHECK (is_active IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fan_zone_submissions` (
    `submission_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `favorite_topic` VARCHAR(150) NOT NULL,
    `message` TEXT NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CHECK (is_active IN (0, 1)),
    KEY idx_fan_public (is_active, created_at, submission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Store checkout extension: orders and order_items
-- Store checkout extension. Run after schema.sql in the same database.
CREATE TABLE IF NOT EXISTS orders (
    order_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    order_number CHAR(19) NOT NULL UNIQUE,
    request_token CHAR(32) NOT NULL UNIQUE,
    buyer_name VARCHAR(100) NOT NULL,
    buyer_phone VARCHAR(32) NOT NULL,
    shipping_address VARCHAR(500) NOT NULL,
    shipping_city VARCHAR(100) NOT NULL,
    shipping_province VARCHAR(100) NOT NULL,
    postal_code VARCHAR(12) NOT NULL,
    payment_method ENUM('cod') NOT NULL DEFAULT 'cod',
    currency CHAR(3) NOT NULL,
    total_amount DECIMAL(16,2) NOT NULL,
    status ENUM('pending','processing','shipped','completed','cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CHECK (total_amount >= 0),
    KEY idx_order_status (status, order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
    order_item_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    size ENUM('standard','S','M','L','XL') NOT NULL DEFAULT 'standard',
    quantity INT UNSIGNED NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    UNIQUE KEY uk_order_product_size (order_id, product_id, size),
    KEY idx_order_items_product (product_id),
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders (order_id) ON DELETE RESTRICT,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products (product_id) ON DELETE RESTRICT,
    CHECK (quantity > 0),
    CHECK (unit_price >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Initial catalog data for a fresh installation
-- Seed for a fresh database. Contains original site content and demo Store inventory.

-- Dates and historical references: QueenOnline official member profiles and band history.

-- Stock and weight values are demo figures. No admin passwords or personal fan submissions are seeded.

INSERT INTO `band_members` (`member_name`, `birth_date`, `role`, `biography`, `photo_url`, `sort_order`) VALUES
    ('Freddie Mercury', '1946-09-05', 'Vocal / Piano', 'Lead vocalist known for theatrical presence and powerful vocal expression.', 'assets/images/member-freddie.webp', 1),
    ('Brian May', '1947-07-19', 'Guitar', 'Guitarist known for distinctive tone and melodic rock arrangements.', 'assets/images/member-brian.webp', 2),
    ('Roger Taylor', '1949-07-26', 'Drums', 'Drummer and vocalist contributing rhythm, energy, and harmonies.', 'assets/images/member-roger.webp', 3),
    ('John Deacon', '1951-08-19', 'Bass', 'Bassist whose lines helped shape many of Queen’s most memorable songs.', 'assets/images/member-john.webp', 4);

INSERT INTO `albums` (`album_id`, `album_title`, `release_year`, `genre`, `cover_url`, `description`, `spotify_url`) VALUES
    (1, 'Queen', 1973, 'Hard rock', 'https://image-cdn-fa.spotifycdn.com/image/ab67616d00001e026dcd74eff57c54b830216b2d', 'The band''s debut album.', 'https://open.spotify.com/album/28VpCXV4h5EgAAWGAnGu6x'),
    (2, 'Queen II', 1974, 'Progressive rock', 'assets/images/albums/queen-ii.webp', 'Queen''s second studio album.', 'https://open.spotify.com/album/0NouBnbXRJKFWzm9LwCW0K'),
    (3, 'Sheer Heart Attack', 1974, 'Rock', 'https://image-cdn-fa.spotifycdn.com/image/ab67616d00001e022d5fece44454a7ea344022ef', 'The 1974 album that includes Killer Queen.', 'https://open.spotify.com/album/1VP4MvaxYgl0XHeLZHnfcq'),
    (4, 'A Night At The Opera', '1975', 'Progressive rock', 'assets/images/albums/a-night-at-the-opera.jpg', 'The legendary album featuring Bohemian Rhapsody and Love Of My Life.', 'https://open.spotify.com/embed/album/1TkbyIkf6GSrO5e7gWS4AM?utm_source=generator&theme=0'),
    (5, 'A Day At The Races', '1976', 'Rock', 'assets/images/albums/a-day-at-the-races.webp', 'A powerful follow-up album featuring Somebody To Love.', 'https://open.spotify.com/embed/album/6o6PjyNMAOLGicYvCJ9EVw?utm_source=generator&theme=0'),
    (6, 'News Of The World', '1977', 'Rock', 'assets/images/albums/news-of-the-world.jpg', 'Home of stadium classics We Will Rock You and We Are The Champions.', 'https://open.spotify.com/embed/album/7hq6mnrJ7CWbemk6B3H8PJ?utm_source=generator&theme=0'),
    (7, 'Jazz', '1978', 'Rock', 'assets/images/albums/jazz.jpg', 'An experimental album featuring Don''t Stop Me Now.', 'https://open.spotify.com/embed/album/1yzF0wUwJFgtkkXmKNe5BE?utm_source=generator&theme=0'),
    (8, 'The Game', '1980', 'Rock', 'assets/images/albums/the-game.webp', 'Queen explored rock, funk, and electronic influences.', 'https://open.spotify.com/embed/album/6l8tOdUItDC01Qmb91Lo8W?utm_source=generator&theme=0'),
    (9, 'Hot Space', '1982', 'Funk rock', 'assets/images/albums/hot-space.webp', 'A unique era where Queen experimented with funk and dance influences.', 'https://open.spotify.com/embed/album/4pFJdTvK6zNyWOO3W1hkGd?utm_source=generator&theme=0'),
    (10, 'The Works', '1984', 'Rock', 'assets/images/albums/the-works.jpg', 'Featuring Radio Ga Ga and I Want To Break Free.', 'https://open.spotify.com/embed/album/6IXmSdbq5OmlR08vvcnHoC?utm_source=generator&theme=0'),
    (11, 'A Kind Of Magic', '1986', 'Rock', 'assets/images/albums/a-kind-of-magic.jpg', 'The soundtrack era album featuring some of Queen''s most memorable songs.', 'https://open.spotify.com/embed/album/2DK4ui1ETQySlHxbmNIvq3?utm_source=generator&theme=0'),
    (12, 'The Miracle', '1989', 'Rock', 'assets/images/albums/the-miracle.jpg', 'An album showing Queen''s creativity and musical evolution.', 'https://open.spotify.com/embed/album/4Ck0kT7cw6fXjFVBL3DODw?utm_source=generator&theme=0'),
    (13, 'Greatest Hits II', 1991, 'Rock', 'assets/images/album-greatest-hits-ii.webp', 'A collection of Queen singles.', 'https://open.spotify.com/album/3g8q0w3yxetDMMCgqe6YxB'),
    (14, 'Innuendo', '1991', 'Progressive rock', 'assets/images/albums/innuendo.webp', 'One of Queen''s most emotional and memorable final studio albums.', 'https://open.spotify.com/embed/album/0CNfnxzURJtjzeyELCBfvj?utm_source=generator&theme=0'),
    (15, 'Made In Heaven', '1995', 'Rock', 'assets/images/albums/made-in-heaven.webp', 'The final Queen studio album featuring Freddie Mercury.', 'https://open.spotify.com/embed/album/6QvcoJ6nbY8ACDvogCliiw?utm_source=generator&theme=0'),
    (16, 'Flash Gordon', '1980', 'Soundtrack', 'https://image-cdn-ak.spotifycdn.com/image/ab67616d00001e027008b56448812cd5b066fe02', 'Queen soundtrack album for the 1980 film.', 'https://open.spotify.com/album/2SS9qutxzz0XZf4zmoQVdx');

INSERT INTO `songs` (`album_id`, `song_title`, `release_year`, `genre`, `songwriter_credit`, `summary`, `story`, `spotify_url`, `youtube_video_id`, `cover_url`, `is_featured_song`) VALUES
    (4, 'Bohemian Rhapsody', '1975', 'Opera rock', 'Freddie Mercury', 'A multi-section composition that moves from intimate ballad to operatic drama and hard rock.', 'Bohemian Rhapsody is one of Queen''s most ambitious compositions. Written by Freddie Mercury, the six-minute song combines ballad, opera, and hard rock sections into a structure rarely seen in popular music.

One of the most influential songs in rock history
Combined opera, rock, and classical influences
Became Queen''s signature masterpiece worldwide', 'https://open.spotify.com/embed/track/2JiDi0qAXsPwhPqA2qaKGt?utm_source=generator&theme=0', 'fJ9rUzIMcZQ', 'assets/images/album-night-at-the-opera.webp', 1),
    (6, 'We Will Rock You', '1977', 'Arena anthem', 'Brian May', 'The famous stomp-stomp-clap pattern turns listeners into part of the performance.', 'Written by Brian May, We Will Rock You was created as an audience participation anthem. The famous stomp-stomp-clap rhythm allows fans around the world to become part of Queen''s live performances.

Created one of music''s most recognizable rhythms
Changed the interaction between band and audience
Remains one of the world''s greatest stadium anthems', 'https://open.spotify.com/embed/track/2ELSVi4kwWOXgxiIC592tJ?utm_source=generator&theme=0', '-tJYN-eG1zk', 'assets/images/album-news-of-the-world.webp', 1),
    (5, 'Somebody To Love', '1976', 'Gospel rock', 'Freddie Mercury', 'Gospel-inspired vocal layering supports a song about longing, doubt, and emotional intensity.', 'Somebody To Love was inspired by gospel music and highlights Freddie Mercury''s powerful vocal range. The song explores themes of loneliness, searching for love, and emotional vulnerability.

Showcases Freddie Mercury''s extraordinary vocal ability
Features Queen''s layered harmony techniques
Remains one of Queen''s most emotional performances', 'https://open.spotify.com/embed/track/18OMrZoyyZuVy06FttIFhq?utm_source=generator&theme=0', 'kijpcUv-b8M', 'assets/images/queen-group-shadow-music.webp', 1),
    (1, 'Keep Yourself Alive', '1973', 'Rock', 'Brian May', 'Keep Yourself Alive was Queen''s debut single and introduced the band''s energetic rock identity. Written by Brian May, the song combines powerful guitar…', 'Keep Yourself Alive was Queen''s debut single and introduced the band''s energetic rock identity. Written by Brian May, the song combines powerful guitar work, dynamic rhythm changes, and the unique vocal style that would define Queen''s early sound.

Queen''s first official single released in 1973
Introduced Brian May''s distinctive guitar style
Represented the beginning of Queen''s musical journey', 'https://open.spotify.com/embed/track/7zho1S5I6WhbQ90OWqZytT?utm_source=generator&theme=0', NULL, NULL, 0),
    (2, 'Seven Seas Of Rhye', '1974', 'Rock', 'Freddie Mercury', 'The closing track from Queen II introduced the band''s dramatic and theatrical style. The song became Queen''s first UK Top 10 hit and helped establish their…', 'The closing track from Queen II introduced the band''s dramatic and theatrical style. The song became Queen''s first UK Top 10 hit and helped establish their unique combination of fantasy, powerful vocals, and progressive rock influences.

Queen''s first major UK chart success
Introduced Queen''s theatrical and fantasy-inspired style
Showed the band''s early musical ambition', 'https://open.spotify.com/embed/track/2nbmAdtBKyhw8ghd8tsBVT?utm_source=generator&theme=0', NULL, NULL, 0),
    (3, 'Killer Queen', '1974', 'Rock', 'Freddie Mercury', 'Killer Queen became Queen''s first major international breakthrough. Written by Freddie Mercury, the song demonstrated his ability to combine sophisticated…', 'Killer Queen became Queen''s first major international breakthrough. Written by Freddie Mercury, the song demonstrated his ability to combine sophisticated lyrics, theatrical arrangements, and elegant melodies into a unique pop-rock composition.

First Queen song to achieve major international recognition
Showed Freddie Mercury''s theatrical songwriting style
Established Queen as a creative force in rock music', 'https://open.spotify.com/embed/track/46L5lm1Xzwr0TExEMpObtq?utm_source=generator&theme=0', NULL, NULL, 0),
    (4, 'I''m In Love With My Car', '1975', 'Rock', 'Roger Taylor', 'Written and performed by Roger Taylor, I''m In Love With My Car reflects his passion for automobiles and speed. The energetic rock track became one of Roger…', 'Written and performed by Roger Taylor, I''m In Love With My Car reflects his passion for automobiles and speed. The energetic rock track became one of Roger Taylor''s most recognizable songs with Queen.

Featured Roger Taylor as lead vocalist
Showed the different musical personalities within Queen
Became a memorable track from A Night At The Opera', 'https://open.spotify.com/embed/track/0p4yKNKmQ0CqvBHwaoDZHh?utm_source=generator&theme=0', NULL, NULL, 0),
    (4, 'Love Of My Life', '1975', 'Ballad', 'Freddie Mercury', 'A piano-led ballad that later became a powerful audience sing-along in concert.', 'Love Of My Life is a deeply emotional piano ballad written by Freddie Mercury. The song became one of Queen''s most beloved live performances because of the connection between the band and their fans.

One of Queen''s most emotional ballads
Shows Freddie Mercury''s softer songwriting style
Created a powerful connection with audiences worldwide', 'https://open.spotify.com/embed/track/0xcWxI1fUxfD6LrrljUH7L?utm_source=generator&theme=0', 'sUJkCXE4sAA', 'assets/images/album-night-at-the-opera.webp', 0),
    (4, 'You''re My Best Friend', '1976', 'Pop rock', 'John Deacon', 'A warm, melodic song built around electric piano and an affectionate directness.', 'Written by bassist John Deacon for his wife, You''re My Best Friend became one of Queen''s most beloved songs. The track highlights a warmer and more personal side of Queen through its melodic arrangement and heartfelt message.

One of Queen''s most popular romantic songs
Showed John Deacon''s songwriting ability
Expanded Queen''s sound beyond dramatic rock compositions', 'https://open.spotify.com/embed/track/46gybz6M2g4oiVvt1aWOV4?utm_source=generator&theme=0', 'HaZpZQG2z10', 'assets/images/album-night-at-the-opera.webp', 0),
    (6, 'Spread Your Wings', '1977', 'Narrative rock', 'John Deacon', 'A character-driven song about leaving a limited situation and pursuing a larger life.', 'Spread Your Wings is a reflective song written by John Deacon about overcoming limitations and pursuing personal dreams. Its emotional message and simple arrangement made it one of Queen''s most appreciated fan favorites.

Highlighted John Deacon''s thoughtful songwriting
Delivered a message about freedom and ambition
Became a respected deep cut among Queen fans', 'https://open.spotify.com/embed/track/7sk3xGIhSoDz7sCwoaFTqn?utm_source=generator&theme=0', NULL, 'assets/images/album-news-of-the-world.webp', 0),
    (6, 'We Are The Champions', '1977', 'Victory anthem', 'Freddie Mercury', 'A piano-led anthem that balances struggle, persistence, and public celebration.', 'We Are The Champions became an anthem of victory and perseverance. Written by Freddie Mercury, the song has become strongly associated with celebrations and sporting achievements worldwide.

One of the world''s most recognized victory songs
Frequently used in global sporting celebrations
Shows Queen''s ability to create universal anthems', 'https://open.spotify.com/embed/track/3QjYCAzQfNFDIL6kSMpPcw?utm_source=generator&theme=0', '04854XqcfCY', 'assets/images/album-news-of-the-world.webp', 0),
    (7, 'Don''t Stop Me Now', '1978', 'Piano rock', 'Freddie Mercury', 'Rapid piano, accelerating vocals, and a bright chorus create uninterrupted momentum.', 'Don''t Stop Me Now captures Freddie Mercury''s energetic songwriting style through powerful piano, vocals, and fast-paced rock arrangements celebrating freedom and confidence.

Recognized as one of Queen''s most energetic songs
Highlights Freddie Mercury''s piano-driven rock style
Continues to attract listeners across generations', 'https://open.spotify.com/embed/track/1NHWG8zxSEypSRF3UufrnO?utm_source=generator&theme=0', 'HgzGwKwLmgM', 'assets/images/queen-group-classic-music.webp', 0),
    (8, 'Save Me', '1980', 'Rock', 'Brian May', 'Save Me is an emotional ballad written by Brian May after witnessing the end of a relationship. The song combines piano, guitar, and powerful vocals to…', 'Save Me is an emotional ballad written by Brian May after witnessing the end of a relationship. The song combines piano, guitar, and powerful vocals to create one of Queen''s most heartfelt compositions. It became a highlight of Queen''s live performances and showed the band''s ability to deliver deeply emotional rock ballads.

One of Brian May''s most recognized ballads with Queen
Highlights Freddie Mercury''s emotional vocal performance
Shows Queen''s ability to combine rock power with personal storytelling', 'https://open.spotify.com/embed/track/5PYTziNGPjz0HohZVm2coE?utm_source=generator&theme=0', NULL, NULL, 0),
    (8, 'Another One Bites The Dust', '1980', 'Funk rock', 'John Deacon', 'A distinctive bass line anchors Queen’s move into funk- and dance-influenced rock.', 'Written by John Deacon, Another One Bites The Dust introduced funk influences into Queen''s sound. Its iconic bassline became one of the band''s biggest commercial successes.

Features one of rock music''s most recognizable basslines
Expanded Queen''s musical style beyond traditional rock
Became one of Queen''s biggest international hits', 'https://open.spotify.com/embed/track/1KPuohcXPmJYQfdyg9zKmR?utm_source=generator&theme=0', 'rY0WxgSXdEE', 'assets/images/album-the-game.webp', 0),
    (10, 'Radio Ga Ga', '1984', 'Synth rock', 'Roger Taylor', 'Synth-driven verses and a large hand-clap chorus examine changing relationships with radio.', 'Written by Roger Taylor, Radio Ga Ga reflects the changing relationship between music, technology, and audiences during the rise of electronic media.

Created one of Queen''s most iconic audience moments
Reflected changes in music culture during the 1980s
Featured legendary live performances', 'https://open.spotify.com/embed/track/6N717o83wbmahFGUqkjQj7?utm_source=generator&theme=0', 'azdwsXLmrHE', 'assets/images/album-greatest-hits-ii.webp', 0),
    (5, 'Good Old-Fashioned Lover Boy', 1976, 'Music hall pop', 'Freddie Mercury', 'A playful, tightly arranged song influenced by music hall and theatrical pop.', 'The song uses polished harmonies, light rhythmic shifts, and humorous elegance. It represents the band’s ability to place older theatrical traditions inside a modern rock album without losing their own identity.', NULL, NULL, 'assets/images/queen-group-classic-music.webp', 0),
    (7, 'Bicycle Race', 1978, 'Art pop', 'Freddie Mercury', 'Fast changes, stacked vocals, and playful contradictions create an eccentric pop-rock piece.', 'The arrangement deliberately changes direction and tone, matching the song’s rapid sequence of ideas. Its unusual structure and theatrical delivery show Queen’s comfort with making a single feel unpredictable while remaining memorable.', NULL, 'GugsCdLHm-Q', 'assets/images/queen-group-classic-music.webp', 0),
    (7, 'Fat Bottomed Girls', 1978, 'Hard rock', 'Brian May', 'A heavy guitar groove and broad vocal harmonies give the song its direct rock character.', 'The track emphasizes guitar weight, rhythmic confidence, and a chorus designed for immediate impact. It is often paired conceptually with Bicycle Race, showing two very different sides of the same album era.', NULL, 'VMnjF1O4eH0', 'assets/images/queen-group-classic-music.webp', 0),
    (8, 'Crazy Little Thing Called Love', 1979, 'Rockabilly', 'Freddie Mercury', 'A concise rockabilly-inspired song that highlights Queen’s stylistic flexibility.', 'Instead of the band’s densest studio style, the track uses a leaner arrangement and a retro rhythmic feel. Its simplicity is deliberate, demonstrating that Queen could create a strong identity without relying on maximal production.', NULL, 'zO6D_BAuYCI', 'assets/images/album-the-game.webp', 0),
    (8, 'Play the Game', 1980, 'Pop rock', 'Freddie Mercury', 'A romantic song that blends piano-led writing with a polished electronic texture.', 'The track marks a period in which Queen became more open to synthesizer textures while preserving vocal harmonies and guitar detail. Its smooth arrangement serves as an entry point into the broader sound of The Game.', NULL, '6_5O-nUiZ_0', 'assets/images/album-the-game.webp', 0),
    (16, 'Flash', 1980, 'Soundtrack rock', 'Brian May', 'A compact soundtrack theme built around dramatic calls, synthesizers, and guitar.', 'Created for a science-fiction film, the song functions as both a theme and a rock single. Dialogue fragments and cinematic transitions are integrated into the arrangement, connecting the track directly to its visual context.', NULL, 'LfmrHTdXgK4', 'assets/images/album-the-game.webp', 0),
    (9, 'Under Pressure', 1981, 'Art rock', 'Queen & David Bowie', 'A collaborative performance built around an instantly recognizable bass line and contrasting voices.', 'The recording brings Queen and David Bowie into a shared arrangement where different vocal personalities answer and overlap one another. The song grows from tension toward a final appeal for empathy and responsibility.', NULL, 'a01QQZyl-_I', 'assets/images/album-greatest-hits-ii.webp', 0),
    (10, 'I Want to Break Free', 1984, 'Pop rock', 'John Deacon', 'A clean pop-rock arrangement supports a direct statement about independence and release.', 'John Deacon’s writing is concise and melodic, allowing the repeated central phrase to carry multiple interpretations. The memorable video added another visual identity that became strongly associated with the track.', NULL, 'f4Mc-NYPHaQ', 'assets/images/album-greatest-hits-ii.webp', 0),
    (10, 'Hammer to Fall', 1984, 'Hard rock', 'Brian May', 'A guitar-forward track with urgent rhythm and a forceful live-performance character.', 'The song brings Queen back toward direct hard rock while keeping the polished scale of their 1980s production. Its strong rhythmic accents and guitar structure made it especially effective in large live settings.', NULL, 'JU5LMG3WFBw', 'assets/images/album-greatest-hits-ii.webp', 0),
    (11, 'A Kind of Magic', 1986, 'Pop rock', 'Roger Taylor', 'A sleek, optimistic song whose repeated hook gives it a cinematic and celebratory quality.', 'The song is connected with the Highlander film project but also works independently as a polished Queen single. Its arrangement balances electronic rhythm, guitar color, and an immediately recognizable refrain.', NULL, '0p_1QSUsbsM', 'assets/images/album-greatest-hits-ii.webp', 0),
    (11, 'Who Wants to Live Forever', 1986, 'Orchestral rock', 'Brian May', 'Orchestral scale and restrained vocals explore mortality, time, and the value of love.', 'Written for a dramatic film context, the song expands from quiet reflection into a large orchestral climax. The contrast between immortality and limited human time gives the arrangement its emotional weight.', NULL, '_Jtpf8N5IDE', 'assets/images/album-greatest-hits-ii.webp', 0),
    (12, 'I Want It All', 1989, 'Hard rock anthem', 'Queen', 'A direct, guitar-heavy anthem driven by ambition, urgency, and a large chorus.', 'The song uses a compact statement of determination and surrounds it with heavy guitar and group vocals. Its assertive structure helped it become one of the clearest rock anthems from Queen’s later period.', NULL, 'hFDcoX7s6rE', 'assets/images/album-greatest-hits-ii.webp', 0),
    (14, 'Innuendo', 1991, 'Progressive rock', 'Queen', 'An expansive late-period composition with dramatic sections and progressive-rock scale.', 'The track revisits Queen’s interest in large forms, moving through contrasting textures and rhythmic ideas. Its scale links the band’s early experimental ambition with the darker atmosphere of their final studio era with Freddie Mercury.', NULL, 'g2N0TkfrQhY', 'assets/images/album-greatest-hits-ii.webp', 0),
    (14, 'The Show Must Go On', 1991, 'Arena rock', 'Queen', 'A powerful late-period song about endurance, performance, and continuing despite difficulty.', 'The arrangement is built around rising tension and a demanding vocal line. Its language of performance and persistence gained additional emotional significance within the context of Queen’s final recordings with Freddie Mercury.', NULL, 't99KH0TR-J4', 'assets/images/album-greatest-hits-ii.webp', 0);

INSERT INTO `timeline_events` (`event_title`, `event_year`, `event_type`, `description`, `image_url`, `source_url`, `sort_order`) VALUES
    ('The Beginning', 1970, 'history', 'Queen was formed in London in 1970. The band began with Freddie Mercury, Brian May, and Roger Taylor, later joined by John Deacon. Together they created a unique sound that combined powerful vocals, theatrical performances, and experimental rock.', NULL, 'https://www.queenonline.com/queen', 1),
    ('First Album', 1973, 'history', 'Queen released their debut album "Queen" in 1973. The album introduced their progressive rock influences and showed the band''s ambition to create music beyond traditional rock formulas.', NULL, 'https://www.queenonline.com/queen', 2),
    ('First International Recognition', 1974, 'history', 'Through songs such as "Seven Seas Of Rhye" and "Killer Queen", Queen gained worldwide attention. Their creativity and musical diversity helped them stand apart from other rock bands of the era.', NULL, 'https://www.queenonline.com/queen', 3),
    ('Bohemian Rhapsody', 1975, 'history', 'Queen reached global fame with "Bohemian Rhapsody" from the album "A Night At The Opera". The song combined rock, opera, and ballad elements, becoming one of the most influential songs in music history.', NULL, 'https://www.queenonline.com/queen', 4),
    ('The Stadium Era', 1977, 'history', 'With the release of "News Of The World", Queen created legendary anthems such as "We Will Rock You" and "We Are The Champions". These songs became symbols of celebration and sporting events worldwide.', NULL, 'https://www.queenonline.com/queen', 5),
    ('Live Aid Performance', 1985, 'history', 'Queen delivered one of the greatest live performances in music history at Live Aid in Wembley Stadium. Freddie Mercury''s connection with the audience became an unforgettable cultural moment.', NULL, 'https://www.queenonline.com/queen', 6),
    ('The Magic Tour', 1986, 'history', 'The Magic Tour became Queen''s final major concert tour with Freddie Mercury. The Wembley Stadium performances remain among the band''s most iconic live shows.', NULL, 'https://www.queenonline.com/queen', 7),
    ('Freddie Mercury Legacy', 1991, 'history', 'Freddie Mercury passed away in 1991, but his influence continued through Queen''s timeless music. His voice, songwriting, and performances remain an inspiration for generations.', NULL, 'https://www.queenonline.com/queen', 8),
    ('A Timeless Legacy', NULL, 'history', 'Today, Queen remains one of the most influential bands in history. Their songs, performances, and artistic vision continue to connect with millions of fans around the world.', NULL, 'https://www.queenonline.com/queen', 9),
    ('BRIT Awards', NULL, 'award', 'Outstanding Contribution to British Music.', 'assets/images/brit-awards.jpg', NULL, 1),
    ('Ivor Novello Awards', NULL, 'award', 'Recognition for songwriting excellence.', 'assets/images/ivor-novello.jpg', NULL, 2),
    ('American Music Awards', 1981, 'award', 'Favorite Pop/Rock Single winner (1981).', 'assets/images/american-music-awards.jpg', NULL, 3),
    ('Hollywood Walk of Fame', NULL, 'award', 'Global legacy recognition star.', 'assets/images/hollywood-walk-fame.jpg', NULL, 4),
    ('Grammy Awards', 2018, 'award', 'Lifetime Achievement Award (2018).', 'assets/images/grammy-awards.jpg', NULL, 5),
    ('BRIT Billion Award', NULL, 'award', '1 billion+ UK streams milestone.', 'assets/images/brit-billion.jpg', NULL, 6),
    ('Polar Music Prize', NULL, 'award', 'International music excellence award.', 'assets/images/polar-music-prize.jpg', NULL, 7),
    ('UK Best-Selling Album', NULL, 'award', 'Greatest Hits best-selling album in UK history.', 'assets/images/uk-best-selling-album.jpg', NULL, 8);

INSERT INTO `media_items` (`media_title`, `media_type`, `category`, `media_url`, `thumbnail_url`, `description`, `media_year`, `source_url`, `album_id`, `sort_order`) VALUES
    ('Queen, 1975', 'image', 'band', 'assets/images/queen-1975-elektra-publicity.webp', NULL, 'A Night at the Opera publicity era', 1975, NULL, NULL, 1),
    ('Group portrait', 'image', 'member', 'assets/images/hero-queen-logo.webp', NULL, 'Dark visual identity', NULL, NULL, NULL, 2),
    ('Freddie Mercury', 'image', 'member', 'assets/images/member-freddie.webp', NULL, 'Lead vocals / piano', NULL, NULL, NULL, 3),
    ('Brian May', 'image', 'member', 'assets/images/member-brian.webp', NULL, 'Guitar', NULL, NULL, NULL, 4),
    ('Roger Taylor', 'image', 'member', 'assets/images/member-roger.webp', NULL, 'Drums', NULL, NULL, NULL, 5),
    ('John Deacon', 'image', 'member', 'assets/images/member-john.webp', NULL, 'Bass', NULL, NULL, NULL, 6),
    ('Live performance', 'image', 'live', 'assets/images/performance-freddie-live.webp', NULL, 'Freddie on stage', NULL, NULL, NULL, 7),
    ('New Haven, 1977', 'image', 'live', 'assets/images/freddie-nov1977-live.webp', NULL, 'Freddie live performance', 1977, NULL, NULL, 8),
    ('John Deacon live', 'image', 'live', 'assets/images/john-deacon-1977-live.webp', NULL, 'Live performance reference', NULL, NULL, NULL, 9),
    ('Brian May live', 'image', 'live', 'assets/images/brian-hannover-1979.webp', NULL, 'Hannover, 1979', NULL, NULL, NULL, 10),
    ('A Night at the Opera', 'image', 'album', 'assets/images/album-night-at-the-opera.webp', NULL, 'Album cover reference', NULL, NULL, 4, 11),
    ('News of the World', 'image', 'album', 'assets/images/album-news-of-the-world.webp', NULL, 'Album cover reference', NULL, NULL, 6, 12),
    ('The Game', 'image', 'album', 'assets/images/album-the-game.webp', NULL, 'Album cover reference', NULL, NULL, 8, 13),
    ('Greatest Hits II', 'image', 'album', 'assets/images/album-greatest-hits-ii.webp', NULL, 'Compilation cover reference', NULL, NULL, 13, 14),
    ('Queen II', 'image', 'album', 'assets/images/albums/queen-ii.webp', NULL, 'Second studio album released in 1974', NULL, NULL, 2, 15),
    ('A Day At The Races', 'image', 'album', 'assets/images/albums/a-day-at-the-races.webp', NULL, '1976 album featuring Somebody To Love', NULL, NULL, 5, 16),
    ('Jazz', 'image', 'album', 'assets/images/albums/jazz.jpg', NULL, '1978 album featuring Don''t Stop Me Now', NULL, NULL, 7, 17),
    ('Hot Space', 'image', 'album', 'assets/images/albums/hot-space.webp', NULL, '1982 album with funk and electronic influences', NULL, NULL, 9, 18),
    ('The Works', 'image', 'album', 'assets/images/albums/the-works.jpg', NULL, '1984 album featuring Radio Ga Ga', NULL, NULL, 10, 19),
    ('A Kind Of Magic', 'image', 'album', 'assets/images/albums/a-kind-of-magic.jpg', NULL, '1986 album connected with Highlander soundtrack', NULL, NULL, 11, 20),
    ('Queen Classic Band Portrait', 'image', 'band', 'assets/images/queen-classic-band-portrait.jpg', NULL, 'Freddie Mercury, Brian May, Roger Taylor, and John Deacon', NULL, NULL, NULL, 21),
    ('Queen Live Stadium Performance', 'image', 'live', 'assets/images/queen-live-stadium-performance.jpg', NULL, 'Queen performing in front of a massive audience', NULL, NULL, NULL, 22),
    ('Innuendo', 'image', 'album', 'assets/images/albums/innuendo.webp', NULL, '1991 final studio album with Freddie Mercury', NULL, NULL, 14, 23),
    ('Made In Heaven', 'image', 'album', 'assets/images/albums/made-in-heaven.webp', NULL, '1995 album completed after Freddie Mercury', NULL, NULL, 15, 24),
    ('Live Aid 1985', 'image', 'archive', 'assets/images/live-aid-jfk.webp', NULL, 'Large archive reference image', 1985, NULL, NULL, 25),
    ('Early era archive', 'image', 'archive', 'assets/images/early-era-archive-extra.webp', NULL, 'Historical visual reference', NULL, NULL, NULL, 26),
    ('Press portrait', 'image', 'archive', 'assets/images/freddie-press-extra.webp', NULL, 'Archive image reference', NULL, NULL, NULL, 27),
    ('Live Aid 1985', 'video', 'live', 'https://www.youtube.com/watch?v=frdBb9Slu1A', 'assets/images/performance-freddie-live.webp', 'A key performance reference in Queen''s live history.', 1985, 'https://www.youtube.com/watch?v=frdBb9Slu1A', NULL, 1),
    ('Bohemian Rhapsody', 'video', 'archive', 'https://www.youtube.com/watch?v=fJ9rUzIMcZQ', 'assets/images/album-night-at-the-opera.webp', 'Official video reference for Queen''s signature song.', NULL, 'https://www.youtube.com/watch?v=fJ9rUzIMcZQ', NULL, 2),
    ('Don''t Stop Me Now', 'video', 'archive', 'https://www.youtube.com/watch?v=HgzGwKwLmgM', 'assets/images/queen-1975-elektra-publicity.webp', 'High-energy music video reference.', NULL, 'https://www.youtube.com/watch?v=HgzGwKwLmgM', NULL, 3);

INSERT INTO `product_categories` (`category_id`, `category_name`, `description`) VALUES
    (1, 'Apparel', NULL),
    (2, 'Posters', NULL),
    (3, 'Accessories', NULL),
    (4, 'Collectibles', NULL);

INSERT INTO `products` (`category_id`, `product_name`, `description`, `price`, `currency`, `stock`, `weight_gram`, `image_url`, `is_active`) VALUES
    (1, 'Queen Legacy T-Shirt', 'Queen Digital Stage demo merchandise from the apparel collection.', '25', 'USD', 25, 250, 'assets/images/store/tshirt_queen.jpg', 1),
    (1, 'Queen Classic Hoodie', 'Queen Digital Stage demo merchandise from the apparel collection.', '60', 'USD', 12, 650, 'assets/images/store/hoodie_queen.jpg', 1),
    (3, 'Queen Heritage Mug', 'Queen Digital Stage demo merchandise from the accessories collection.', '15', 'USD', 8, 350, 'assets/images/store/mug_queen.jpg', 1),
    (2, 'Queen Signature Poster', 'Queen Digital Stage demo merchandise from the posters collection.', '20', 'USD', 0, 100, 'assets/images/store/poster_queen.jpg', 1);
