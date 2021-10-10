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
              <h2>
                <a href="<?="/post.php?id=" . $post['id']?>">
                <?= htmlspecialchars($post['p_title']); ?>
                </a>
              </h2>
            <!-- Разные типы постов -->
            <?php switch($post['p_type']):
            //   Фото
            case 'photo': ?>
              <div class="post-photo__image-wrapper">
                <img src="<?=$post['p_url'] ?>" alt="Фото от пользователя" width="760" height="396">
              </div>
            <?php break; ?>

            <!-- Текст -->
            <?php case 'text':
            echo text_template($post['p_text']);
            break;
            ?>
            <!-- Видео -->
            <?php case 'video': ?>
            <div class="post-video__block">
                <div class="post-video__preview">
                <?=embed_youtube_video($post['p_url']); ?>
                </div>
                <div class="post-video__control">
                <button class="post-video__play post-video__play--paused button button--video" type="button"><span class="visually-hidden">Запустить видео</span></button>
                <div class="post-video__scale-wrapper">
                    <div class="post-video__scale">
                    <div class="post-video__bar">
                        <div class="post-video__toggle"></div>
                    </div>
                    </div>
                </div>
                <button class="post-video__fullscreen post-video__fullscreen--inactive button button--video" type="button"><span class="visually-hidden">Полноэкранный режим</span></button>
                </div>
                <button class="post-video__play-big button" type="button">
                <svg class="post-video__play-big-icon" width="27" height="28">
                    <use xlink:href="#icon-video-play-big"></use>
                </svg>
                <span class="visually-hidden">Запустить проигрыватель</span>
                </button>
            </div>
            <?php break; ?>

            <!-- Цитата -->
            <?php case 'quote': ?>
            <blockquote>
                <p><?= htmlspecialchars($post['p_text']); ?></p>
                <cite><?= htmlspecialchars($post['quote_author']); ?></cite>
            </blockquote>
            <?php break; ?>

            <!-- Ссылка -->
            <?php case 'link': ?>
            <div class="post-link__wrapper">
                <a class="post-link__external" href="http://<?=$post['p_url'] ?>" title="Перейти по ссылке">
                <div class="post-link__icon-wrapper">
                    <img src="https://www.google.com/s2/favicons?domain=<?=$post['p_url']; ?>" alt="Иконка">
                </div>
                <div class="post-link__info">
                    <h3><?= htmlspecialchars($post['p_title']); ?></h3>
                    <span><?= htmlspecialchars($post['p_url']); ?></span>
                </div>
                <svg class="post-link__arrow" width="11" height="16">
                    <use xlink:href="#icon-arrow-right-ad"></use>
                </svg>
                </a>
            </div>
            <?php break; ?>
            <?php endswitch; ?>
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
