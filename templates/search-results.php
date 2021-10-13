<main class="page__main page__main--search-results">
  <h1 class="visually-hidden">Страница результатов поиска</h1>
  <section class="search">
    <h2 class="visually-hidden">Результаты поиска</h2>
    <div class="search__query-wrapper">
        <div class="search__query container">
        <span>Вы искали:</span>
        <span class="search__query-text"><?=$search; ?></span>
        </div>
    </div>
    <div class="search__results-wrapper">
        <div class="container">
        <div class="search__content">
          <?php foreach ($posts as $post): ?>
          <article class="search__post post post-<?=$post['p_type'] ?>">
            <header class="post__header post__author">
              <a class="post__author-link" href="/profile.php?id=<?=$post['user_id']?>" title="Автор">
                <div class="post__avatar-wrapper">
                  <img class="post__author-avatar" src="<?=$post['u_avatar']?>" alt="Аватар пользователя" width="60" height="60">
                </div>
                <div class="post__info">
                  <b class="post__author-name"><?= htmlspecialchars($post['u_name']); ?></b>
                  <span class="post__time"><?= generate_passed_time_text($post['p_date']);?> назад</span>
                </div>
              </a>
            </header>
            <div class="post__main">
            <!-- Разные типы постов -->
                <?php echo generate_post_template($post); ?>
            </div>
            <footer class="post__footer post__indicators">
                <div class="post__buttons">
                <a class="post__indicator post__indicator--likes button" href="/like.php?id=<?=$post['id']; ?>" title="Лайк">
                    <svg class="post__indicator-icon" width="20" height="17">
                    <use xlink:href="#icon-heart"></use>
                    </svg>
                    <svg class="post__indicator-icon post__indicator-icon--like-active" width="20" height="17">
                    <use xlink:href="#icon-heart-active"></use>
                    </svg>
                    <span><?=$post['like_count'] ?? 0;?></span>
                    <span class="visually-hidden">количество лайков</span>
                </a>
                <a class="post__indicator post__indicator--comments button" href="#" title="Комментарии">
                    <svg class="post__indicator-icon" width="19" height="17">
                    <use xlink:href="#icon-comment"></use>
                    </svg>
                    <span><?=$post['comment_count'] ?? 0;?></span>
                    <span class="visually-hidden">количество комментариев</span>
                </a>
                </div>
            </footer>
          </article>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>
</main>
