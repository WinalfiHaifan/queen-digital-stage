<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="assets/images/favicon.svg" type="image/svg+xml">
  <title>Fan Zone | Queen Digital Stage</title>
  <meta name="description" content="Queen fan zone page with FAQ, discussion prompts, and a community form.">
  <link rel="stylesheet" href="assets/css/styles.css">
<link rel="stylesheet" href="assets/css/database-site.css"></head>
<body class="page-fan-zone">
  <a class="skip-link" href="#main-content">Skip to content</a>
  <header class="site-header">
    <div class="container nav-wrap">
      <a class="brand" href="index.php" aria-label="Queen Digital Stage Home"><span class="brand-mark">Q</span><span>Queen Digital Stage</span></a>
      <button class="nav-toggle" type="button" aria-label="Open navigation" aria-expanded="false">☰</button>
      <nav class="site-nav" aria-label="Main navigation">
        <a class="" href="index.php">Home</a>
<a class="" href="about.php">About</a>
<a class="" href="music.php">Music</a>
<a class="" href="history.php">History</a>
<a class="" href="gallery.php">Media</a>
<a class="active" href="fan-zone.php">Fan Zone</a>
<a class="" href="store.php">Store</a>
      </nav>
    </div>
  </header>
  <main id="main-content">

    <section class="page-hero fan-hero"><div class="container"><p class="eyebrow">Fan Zone</p><h1>Queen fan community.</h1><p class="lead">Join discussions, read fun facts, and share favorite Queen songs, albums, and live performances.</p></div></section>
    
    <section class="section alt">
      <div class="container">
        <div class="section-heading"><p class="eyebrow">Trending Discussion</p><h2>Community topics.</h2></div>
        <div class="discussion-list">
         <div class="discussion-item">

<span>○</span>

<p>
Favourite Queen song?
</p>

</div>


<div class="discussion-item">

<span>○</span>

<p>
Best live performance?
</p>

</div>


<div class="discussion-item">

<span>○</span>

<p>
Freddie Mercury tribute discussion
</p>

</div>


<div class="discussion-buttons">

<a class="btn btn-primary" href="#community-form">
START DISCUSSION
</a>


<a class="btn btn-secondary" href="#community-updates">
VIEW ALL DISCUSSIONS
</a>
</div>
</div>
</div>
</section>
<section class="section">
      <div class="container two-col wide-right">
        <div class="faq-box">
          <details open><summary>What is Queen Digital Stage?</summary><p>Queen Digital Stage is a curated fan archive that presents Queen music, history, media, and community content in one place.</p></details>
          <details><summary>Is this an official website?</summary><p>No. Queen Digital Stage is an educational fan archive and is not affiliated with the official Queen organization.</p></details>
          <details><summary>Where can I start?</summary><p>Start from the Music page if you want songs, or History if you want context.</p></details>
        </div>
        <div class="faq-content">

<p class="eyebrow">
FAQ
</p>

<h2>
Useful questions first.
</h2>

<p>
The Fan Zone gives visitors a simple place to read questions, follow topics, and connect with the Queen Digital Stage archive.
</p>

</div>
</div>
    </section>
<section class="section alt" id="community-updates">

<div class="container">

<div class="section-heading">

<p class="eyebrow">
FUN FACTS
</p>

<h2>
Queen Trivia
</h2>

</div>


<div class="card-grid three">


<article class="card card-pad">

<h3>
Bohemian Rhapsody
</h3>

<p>
The song combines rock, ballad, and opera elements in one composition.
</p>

</article>



<article class="card card-pad">

<h3>
Live Aid 1985
</h3>

<p>
Queen's performance became one of the most celebrated live performances in music history.
</p>

</article>



<article class="card card-pad">

<h3>
Freddie Mercury
</h3>

<p>
Known for his powerful vocals and iconic stage presence.
</p>

</article>


</div>

</div>

</section>
<section class="section">

<div class="container">


<div class="section-heading">

<p class="eyebrow">
COMMUNITY UPDATES
</p>

<h2>
Latest Fan Activities
</h2>

</div>



<div class="card-grid three">


<article class="card card-pad">
<h3>
New Music Discussion
</h3>
<p>
Fans discuss favourite Queen songs.
</p>
</article>


<article class="card card-pad">
<h3>
Live Performance Archive
</h3>
<p>
Explore iconic Queen concerts.
</p>
</article>


<article class="card card-pad">
<h3>
Fan Stories
</h3>
<p>
Share personal memories about Queen.
</p>
</article>


</div>


</div>

</section>
    <section class="section" id="join-community">
<div class="container two-col">
    <form class="community-form card card-pad" id="community-form" method="post" action="fan-zone.php#join-community">
        <?= csrfField() ?>
        <label for="name">Name</label><input class="input" id="name" name="name" maxlength="100" autocomplete="nickname" required value="<?= e($feedback['values']['name'] ?? '') ?>" placeholder="Your name">
        <label for="topic">Favorite Topic</label><input class="input" id="topic" name="favorite_topic" maxlength="150" required value="<?= e($feedback['values']['favorite_topic'] ?? '') ?>" placeholder="Music, history, performance…">
        <label for="message">Your Message</label><textarea class="input form-textarea" id="message" name="message" maxlength="2000" rows="4" required placeholder="Share your favorite Queen moment…"><?= e($feedback['values']['message'] ?? '') ?></textarea>
        <button class="btn btn-primary" type="submit">Share with Community</button>
        <?php if ($feedback): ?><p class="form-message <?= $feedback['error'] ? 'form-error' : '' ?>" role="<?= $feedback['error'] ? 'alert' : 'status' ?>"><?= e($feedback['message']) ?></p><?php endif; ?>
    </form>
    <div>
        <p class="eyebrow">Join Community</p><h2>Be part of the fan archive.</h2>
        <p>Share a Queen moment with the community. A moderator reviews every note before publication.</p>
        <div class="community-feed" id="community-feed" aria-live="polite">
            <p class="feed-label">Recent Fan Note</p>
            <?php foreach ($notes as $note): ?>
                <article class="published-note"><p class="fan-note">“<?= e($note['message']) ?>”</p><span>— <?= e($note['name']) ?> · <?= e($note['favorite_topic']) ?></span></article>
            <?php endforeach; ?>
            <?php if (!$notes): ?><p class="empty-state">Approved fan notes will appear here.</p><?php endif; ?>
        </div>
    </div>
</div>
</section>
  </main>
  <footer class="site-footer">
    <div class="container footer-grid">
      <div>
        <a class="brand" href="index.php"><span class="brand-mark">Q</span><span>Queen Digital Stage</span></a>
        <p>A curated fan archive for Queen music, history, live performances, visual media, and community interaction.</p>
      </div>
      <div>
        <h2>Explore</h2>
        <ul class="footer-links">
          <li><a href="music.php">Music</a></li>
          <li><a href="history.php">History &amp; Legacy</a></li>
          <li><a href="gallery.php">Media Gallery</a></li>
          <li><a href="credits.html">Media Credits</a></li>
        </ul>
      </div>
      <div>
        <h2>Official website</h2>
<ul class="footer-links">
<li><a href="https://www.queenonline.com/" target="_blank" rel="noopener">QueenOnline ↗</a></li>
</ul>
      </div>
    </div>
    <p class="copyright">© 2026 Queen Digital Stage. Educational fan archive.</p>
  </footer>
  <script src="assets/js/database-site.js" defer></script></body>
</html>
