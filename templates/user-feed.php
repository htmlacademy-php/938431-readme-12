<main class="page__main page__main--feed">
    <div class="container">
    <h1 class="page__title page__title--feed">Моя лента</h1>
    </div>
    <div class="page__main-wrapper container">
    <section class="feed">
        <h2 class="visually-hidden">Лента</h2>
        <div class="feed__main-wrapper">
        <div class="feed__wrapper">
            <?php foreach ($posts as $post): ?>
            <article class="feed__post post post-<?=$post['type_class'] ?>">
                <header class="post__header post__author">
                    <a class="post__author-link" href="/profile.php?id=<?=$post['user_id']?>" title="Автор">
                    <div class="post__avatar-wrapper">
                        <?php if ($post['avatar']): ?>
                        <img class="post__author-avatar" src="<?=$post['avatar']?>" alt="Аватар пользователя" width="60" height="60">
                        <?php endif; ?>
                    </div>
                    <div class="post__info">
                        <b class="post__author-name"><?= htmlspecialchars($post['username']); ?></b>
                        <span class="post__time"><?= generate_passed_time_text($post['post_date']);?> назад</span>
                    </div>
                    </a>
                </header>
                <div class="post__main">
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
                      <a class="post__indicator post__indicator--comments button" href="/post.php?id=<?=$post['id']; ?>" title="Комментарии">
                        <svg class="post__indicator-icon" width="19" height="17">
                        <use xlink:href="#icon-comment"></use>
                        </svg>
                        <span><?=$post['comment_count'] ?? 0;?></span>
                        <span class="visually-hidden">количество комментариев</span>
                      </a>
                      <a class="post__indicator post__indicator--repost button" href="/repost.php?id=<?=$post['id']; ?>" title="Репост">
                        <svg class="post__indicator-icon" width="19" height="17">
                        <use xlink:href="#icon-repost"></use>
                        </svg>
                        <span><?=$post['repost_count']?></span>
                        <span class="visually-hidden">количество репостов</span>
                      </a>
                    </div>
                    <ul class="post__tags">
                        <?php foreach ($post['hashtags'] as $hash): ?>
                        <li><a href="search.php?q=%23<?=$hash['hashtag_title']?>">#<?=$hash['hashtag_title']?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </footer>
            </article>
            <?php endforeach; ?>
        </div>
        </div>
        <ul class="feed__filters filters">
        <li class="feed__filters-item filters__item">
            <a class="filters__button <?php if (!$filter) {
    echo ' filters__button--active';
}?>" href="/feed.php?filter=0">
            <span>Все</span>
            </a>
        </li>
        <?php foreach ($types as $type): ?>
        <li class="feed__filters-item filters__item">
            <a class="filters__button filters__button--<?=$type['type_class']?> <?php if ($filter === $type['id']) {
    echo 'filters__button--active';
} ?> button" href="/feed.php?filter=<?=$type['id']?>">
            <span class="visually-hidden"><?=$type['type_title']?></span>
            <svg class="filters__icon" width="22" height="18">
                <use xlink:href="#icon-filter-<?=$type['type_class'] ?>"></use>
            </svg>
            </a>
        </li>
        <?php endforeach; ?>
        </ul>
    </section>
    <aside class="promo">
        <article class="promo__block promo__block--barbershop">
        <h2 class="visually-hidden">Рекламный блок</h2>
        <p class="promo__text">
            Все еще сидишь на окладе в офисе? Открой свой барбершоп по нашей франшизе!
        </p>
        <a class="promo__link" href="#">
            Подробнее
        </a>
        </article>
        <article class="promo__block promo__block--technomart">
        <h2 class="visually-hidden">Рекламный блок</h2>
        <p class="promo__text">
            Товары будущего уже сегодня в онлайн-сторе Техномарт!
        </p>
        <a class="promo__link" href="#">
            Перейти в магазин
        </a>
        </article>
        <article class="promo__block">
        <h2 class="visually-hidden">Рекламный блок</h2>
        <p class="promo__text">
            Здесь<br> могла быть<br> ваша реклама
        </p>
        <a class="promo__link" href="#">
            Разместить
        </a>
        </article>
    </aside>
    </div>
</main>
