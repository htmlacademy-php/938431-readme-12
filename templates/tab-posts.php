<section class="profile__posts tabs__content tabs__content--active">
  <h2 class="visually-hidden">Публикации</h2>
  <?php foreach ($posts as $post): ?>
  <article class="profile__post post post-<?=$post['p_type'];?>">
    <header class="post__header">
      <?php if($post['p_repost']): ?>
        <div class="post__author">
          <?php
            $author = $post['author'];
          ?>
          <a class="post__author-link" href="/profile.php?id=<?=$author['id']; ?>" title="Автор">
            <div class="post__avatar-wrapper post__avatar-wrapper--repost">
              <img class="post__author-avatar" src="<?=$author['u_avatar']; ?>" alt="Аватар пользователя">
            </div>
            <div class="post__info">
              <b class="post__author-name">Репост: <?=$author['u_name']; ?></b>
              <time class="post__time" datetime="<?=$post['dt_add']?>"><?= generate_passed_time_text($post['dt_add']);?> назад</time>
            </div>
          </a>
        </div>
      <?php endif; ?>
    </header>
    <div class="post__main">
      <a href="<?="/post.php?id=" . $post['id']?>">
        <h2><?= htmlspecialchars($post['p_title']); ?></h2>
      </a>
    <!-- Разные типы постов -->
    <?php switch($post['p_type']):
    case 'quote': ?>
    <!-- Цитата -->
    <blockquote>
        <p><?= htmlspecialchars($post['p_text']); ?></p>
        <cite><?= htmlspecialchars($post['quote_author']); ?></cite>
    </blockquote>
    <?php break; ?>
    <? case 'text':
    // Текст
        echo text_template($post['p_text']);
        break;
    ?>

    <?php case 'photo': ?>
        <!-- Фото -->
    <div class="post-photo__image-wrapper">
        <img src="<?= $post['p_url'] ?>" alt="Фото от пользователя" width="760" height="396">
    </div>
    <?php break; ?>

    <?php case 'link': ?>
        <!-- Ссылка -->
    <div class="post-link__wrapper">
        <a class="post-link__external" href="<?=$post['p_url']; ?>" title="Перейти по ссылке">
            <div class="post-link__info-wrapper">
                <div class="post-link__icon-wrapper">
                    <img src="<?=generate_favicon_url($post['p_url']); ?>" alt="Иконка">
                </div>
                <div class="post-link__info">
                    <h3><?=extract_domain_name($post['p_url']); ?></h3>
                </div>
            </div>
        </a>
    </div>
    <?php break; ?>

    <?php case 'video': ?>
        <!-- Видео -->
    <div class="post-video__block">
        <div class="post-video__preview">
            <?=embed_youtube_video($post['p_url']); ?>
        </div>
    </div>
    <?php break; ?>
    <?php endswitch; ?>
    </div>
    <footer class="post__footer">
      <div class="post__indicators">
        <div class="post__buttons">
          <a class="post__indicator post__indicator--likes button" href="/like.php?id=<?=$post['id']; ?>" title="Лайк">
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
        <time class="post__time" datetime="<?=$post['dt_add'];?>"><?= generate_passed_time_text($post['dt_add']);?> назад</time>
      </div>
      <ul class="post__tags">
        <?php foreach ($post['hashtags'] as $hash): ?>
        <li><a href="search.php?q=%23<?=$hash['title']?>">#<?=$hash['title']?></a></li>
        <?php endforeach; ?>
      </ul>
    </footer>
    <?php if (isset($post['comments'])): ?>
    <div class="comments">
      <div class="comments__list-wrapper">
        <ul class="comments__list">
          <?php foreach ($post['comments'] as $comment): ?>
          <li class="comments__item user">
            <div class="comments__avatar">
              <a class="user__avatar-link" href="/profile.php?id=<?=$comment['user_id']; ?>">
                <img class="comments__picture" src="<?=$comment['u_avatar']; ?>" alt="Аватар пользователя">
              </a>
            </div>
            <div class="comments__info">
              <div class="comments__name-wrapper">
                <a class="comments__user-name" href="/profile.php?id=<?=$comment['user_id']; ?>">
                  <span><?=$comment['u_name']; ?></span>
                </a>
                <time class="comments__time" datetime="<?=$comment['dt_add'];?>"><?= generate_passed_time_text($comment['dt_add']);?> назад</time>
              </div>
              <p class="comments__text">
                <?= htmlspecialchars($comment['c_content']); ?>
              </p>
            </div>
          </li>
          <?php endforeach;
           if ($post['comment_count'] > count($post['comments'])): ?>
            <a class="comments__more-link" href="<?= update_query_params('post' . $post['id'], 'cmtsall')?>">
              <span>Показать все комментарии</span>
              <sup class="comments__amount"><?=$post['comment_count']; ?></sup>
            </a>
        <?php endif; ?>
      </div>
    </div>
    <form class="comments__form form" action="#" method="post">
      <div class="comments__my-avatar">
        <img class="comments__picture" src="img/userpic-medium.jpg" alt="Аватар пользователя">
      </div>
      <textarea class="comments__textarea form__textarea" placeholder="Ваш комментарий"></textarea>
      <label class="visually-hidden">Ваш комментарий</label>
      <button class="comments__submit button button--green" type="submit">Отправить</button>
    </form>
    <?php else: ?>
    <div class="comments">
      <a class="comments__button button" href="<?= update_query_params('post' . $post['id'], 'cmts')?>">Показать комментарии</a>
    </div>
    <? endif; ?>
  </article>
  <?php endforeach; ?>
</section>
