<section class="profile__posts tabs__content tabs__content--active">
  <h2 class="visually-hidden">Публикации</h2>
  <?php foreach ($posts as $post): ?>
  <article class="profile__post post post-<?=$post['type_class'];?>">
    <header class="post__header">
      <?php if ($post['is_repost']): ?>
        <div class="post__author">
          <?php
            $author = $post['author'];
          ?>
          <a class="post__author-link" href="/profile.php?id=<?=$author['id']; ?>" title="Автор">
            <div class="post__avatar-wrapper post__avatar-wrapper--repost">
              <?php if ($author['avatar']): ?>
              <img class="post__author-avatar" src="<?=$author['avatar']; ?>" alt="Аватар пользователя">
              <?php endif; ?>
            </div>
            <div class="post__info">
              <b class="post__author-name">Репост: <?=htmlspecialchars($author['username']); ?></b>
              <time class="post__time" datetime="<?=$post['date_add']?>"><?= generate_passed_time_text($post['date_add']);?> назад</time>
            </div>
          </a>
        </div>
      <?php endif; ?>
    </header>
    <div class="post__main">
        <!-- Разные типы постов -->
        <?php echo generate_post_template($post); ?>
    </div>
    <footer class="post__footer">
      <div class="post__indicators">
        <div class="post__buttons">
          <?php
              $href = $is_own_profile ? '' : 'href="/like.php?id=' . $post['id'] .'"';
          ?>
          <a class="post__indicator post__indicator--likes button" <?=$href; ?> title="Лайк">
            <svg class="post__indicator-icon" width="20" height="17">
              <use xlink:href="#icon-heart"></use>
            </svg>
            <svg class="post__indicator-icon post__indicator-icon--like-active" width="20" height="17">
              <use xlink:href="#icon-heart-active"></use>
            </svg>
            <span><?=$post['like_count'];?></span>
            <span class="visually-hidden">количество лайков</span>
          </a>
          <a class="post__indicator post__indicator--repost button" href="/repost.php?id=<?=$post['id']; ?>" title="Репост">
            <svg class="post__indicator-icon" width="19" height="17">
              <use xlink:href="#icon-repost"></use>
            </svg>
            <span><?=$post['repost_count']?></span>
            <span class="visually-hidden">количество репостов</span>
          </a>
        </div>
        <time class="post__time" datetime="<?=$post['date_add'];?>"><?= generate_passed_time_text($post['date_add']);?> назад</time>
      </div>
      <ul class="post__tags">
        <?php foreach ($post['hashtags'] as $hash): ?>
        <li><a href="search.php?q=%23<?=$hash['hashtag_title']?>">#<?=$hash['hashtag_title']?></a></li>
        <?php endforeach; ?>
      </ul>
    </footer>
    <?php if (array_key_exists('comments', $post)): ?>
    <div class="comments">
      <div class="comments__list-wrapper">
        <ul class="comments__list">
          <?php foreach ($post['comments'] as $comment): ?>
          <li class="comments__item user">
            <div class="comments__avatar">
              <a class="user__avatar-link" href="/profile.php?id=<?=$comment['user_id']; ?>">
                <?php if ($comment['avatar']): ?>
                <img class="comments__picture" src="<?=$comment['avatar']; ?>" alt="Аватар пользователя">
                <?php endif; ?>
              </a>
            </div>
            <div class="comments__info">
              <div class="comments__name-wrapper">
                <a class="comments__user-name" href="/profile.php?id=<?=$comment['user_id']; ?>">
                  <span><?=htmlspecialchars($comment['username']); ?></span>
                </a>
                <time class="comments__time" datetime="<?=$comment['date_add'];?>"><?= generate_passed_time_text($comment['date_add']);?> назад</time>
              </div>
              <p class="comments__text">
                <?= htmlspecialchars($comment['comment_text']); ?>
              </p>
            </div>
          </li>
          <?php endforeach;
           if ($post['comment_count'] > count($post['comments'])): ?>
            <a class="comments__more-link" href="<?= update_query_params('post' . $post['id'], 'comments_all')?>">
              <span>Показать все комментарии</span>
              <sup class="comments__amount"><?=$post['comment_count']; ?></sup>
            </a>
        <?php endif; ?>
      </div>
    </div>
    <?php if (!$is_own_profile): ?>
    <form class="comments__form form" action="" method="post">
      <input type="hidden" name="post-id" value="<?=$post['id']; ?>">
      <div class="comments__my-avatar">
        <?php if ($current_user_avatar): ?>
        <img class="comments__picture" src="<?=$current_user_avatar; ?>" alt="Аватар пользователя">
        <?php endif; ?>
      </div>
      <div class="form__input-section <?php if (!empty($errors['comment'])) {
               echo 'form__input-section--error';
           } ?>">
        <textarea class="comments__textarea form__textarea form__input"
        id="comment" name="comment" placeholder="Ваш комментарий"><?=get_post_value('comment'); ?></textarea>
        <label class="visually-hidden" for="comment">Ваш комментарий</label>
        <button class="form__error-button button" type="button">!</button>
        <div class="form__error-text">
          <h3 class="form__error-title">Ошибка: </h3>
          <p class="form__error-desc"><?=$errors['comment'];?></p>
        </div>
      </div>
      <button class="comments__submit button button--green" type="submit">Отправить</button>
    </form>
    <?php endif; ?>
    <a class="comments__button button" href="<?= update_query_params('post' . $post['id'], '')?>">Скрыть комментарии</a>
    <?php elseif ($post['comment_count'] || !$is_own_profile): ?>
    <div class="comments">
      <a class="comments__button button" href="<?= update_query_params('post' . $post['id'], 'comments')?>">Показать комментарии</a>
    </div>
    <?php endif; ?>
  </article>
  <?php endforeach; ?>
</section>
