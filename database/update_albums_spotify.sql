-- Optional patch for an existing Queen Digital Stage database.
-- Adds a cover URL and the official Spotify album page for the five albums
-- requested by the team. Safe to re-run; no album rows are inserted.

UPDATE `albums`
SET `cover_url` = 'https://image-cdn-fa.spotifycdn.com/image/ab67616d00001e026dcd74eff57c54b830216b2d',
    `spotify_url` = 'https://open.spotify.com/album/28VpCXV4h5EgAAWGAnGu6x'
WHERE `album_title` = 'Queen' AND `release_year` = 1973;

UPDATE `albums`
SET `spotify_url` = 'https://open.spotify.com/album/0NouBnbXRJKFWzm9LwCW0K'
WHERE `album_title` = 'Queen II' AND `release_year` = 1974;

UPDATE `albums`
SET `cover_url` = 'https://image-cdn-fa.spotifycdn.com/image/ab67616d00001e022d5fece44454a7ea344022ef',
    `spotify_url` = 'https://open.spotify.com/album/1VP4MvaxYgl0XHeLZHnfcq'
WHERE `album_title` = 'Sheer Heart Attack' AND `release_year` = 1974;

UPDATE `albums`
SET `spotify_url` = 'https://open.spotify.com/album/3g8q0w3yxetDMMCgqe6Yx'
WHERE `album_title` = 'Greatest Hits II' AND `release_year` = 1991;

UPDATE `albums`
SET `cover_url` = 'https://image-cdn-ak.spotifycdn.com/image/ab67616d00001e027008b56448812cd5b066fe02',
    `spotify_url` = 'https://open.spotify.com/album/2SS9qutxzz0XZf4zmoQVdx'
WHERE `album_title` = 'Flash Gordon' AND `release_year` = 1980;
