<section class="page__main page__main--popular">
    <div class="container">
        <h1 class="page__title page__title--popular">Популярное</h1>
    </div>
    <div class="popular container">
        <div class="popular__filters-wrapper">
            <div class="popular__sorting sorting">
                <b class="popular__sorting-caption sorting__caption">Сортировка:</b>
                <ul class="popular__sorting-list sorting__list">
                    <?php foreach ($sort_types as $key => $value): ?>
                    <li class="sorting__item sorting__item--popular">
                        <a class="sorting__link <?=($sort === $key) ?"sorting__link--active" : "";?>" href="/popular.php?filter=<?=$filter; ?>&sort=<?=$key; ?>">
                            <span><?=$value;?></span>
                            <svg class="sorting__icon" width="10" height="12">
                                <use xlink:href="#icon-sort"></use>
                            </svg>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="popular__filters filters">
                <b class="popular__filters-caption filters__caption">Тип контента:</b>
                <ul class="popular__filters-list filters__list">
                    <li class="popular__filters-item popular__filters-item--all filters__item filters__item--all">
                        <a class="filters__button filters__button--ellipse filters__button--all
                        <?php if (!$filter) {
    echo ' filters__button--active';
}?>" href="/popular.php?filter=0">
                            <span>Все</span>
                        </a>
                    </li>
                    <?php foreach ($types as $type): ?>
                    <li class="popular__filters-item filters__item">
                        <a class="filters__button filters__button--<?=$type['type_class']?> <?php if ($filter === $type['id']) {
    echo 'filters__button--active';
} ?>
                         button" href="/popular.php?filter=<?=$type['id']?>">
                            <span class="visually-hidden"><?=$type['type_title']?></span>
                            <svg class="filters__icon"
                              width="<?=$type['width'] ?>" height="<?=$type['height'] ?>">
                                <use xlink:href="#icon-filter-<?=$type['type_class'] ?>"></use>
                            </svg>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="popular__posts">
            <?php foreach ($posts as $post): ?>
            <article class="popular__post post post-<?=$post['type_class'] ?>">
                <header class="post__header">
                    <a href="<?="/post.php?id=" . $post['id']?>">
                        <h2><?= htmlspecialchars($post['post_title']); ?></h2>
                    </a>
                </header>
                <div class="post__main">
                    <!-- Разные типы постов -->
                    <?php switch ($post['type_class']):
                    case 'quote': ?>
                    <!-- Цитата -->
                    <blockquote>
                        <p><?= htmlspecialchars($post['post_text']); ?></p>
                        <cite><?= htmlspecialchars($post['quote_author']); ?></cite>
                    </blockquote>
                    <?php break; ?>
                    <?php case 'text':
                    // Текст
                        echo text_template($post['post_text']);
                        break;
                    ?>

                    <?php case 'photo': ?>
                        <!-- Фото -->
                    <div class="post-photo__image-wrapper">
                        <img src="<?= $post['post_url'] ?>" alt="Фото от пользователя" width="360" height="240">
                    </div>
                    <?php break; ?>

                    <?php case 'link': ?>
                        <!-- Ссылка -->
                    <div class="post-link__wrapper">
                        <a class="post-link__external" href="<?=$post['post_url'] ?>" title="Перейти по ссылке">
                            <div class="post-link__info-wrapper">
                                <div class="post-link__icon-wrapper">
                                    <img src="<?=generate_favicon_url($post['post_url']); ?>" width="16" height="16" alt="Иконка">
                                </div>
                                <div class="post-link__info">
                                    <h3><?= htmlspecialchars($post['post_title']); ?></h3>
                                </div>
                            </div>
                            <span><?=parse_url($post['post_url'], PHP_URL_HOST); ?></span>
                        </a>
                    </div>
                    <?php break; ?>

                    <?php case 'video': ?>
                        <!-- Видео -->
                    <div class="post-video__block">
                        <div class="post-video__preview">
                            <?=embed_youtube_cover($post['post_url']); ?>
                        </div>
                        <a href="post-details.html" class="post-video__play-big button">
                            <svg class="post-video__play-big-icon" width="14" height="14">
                                <use xlink:href="#icon-video-play-big"></use>
                            </svg>
                            <span class="visually-hidden">Запустить проигрыватель</span>
                        </a>
                    </div>
                    <?php break; ?>
                    <?php endswitch; ?>
                </div>
                <footer class="post__footer">
                    <div class="post__author">
                        <a class="post__author-link" href="/profile.php?id=<?=$post['user_id']?>" title="Автор">
                            <div class="post__avatar-wrapper">
                                <?php if ($post['avatar']): ?>
                                <img class="post__author-avatar" src="<?=$post['avatar']?>" width="40" height="auto" alt="Аватар пользователя">
                                <?php endif; ?>
                            </div>
                            <div class="post__info">
                                <b class="post__author-name"><?= htmlspecialchars($post['username']); ?></b>
                                <time class="post__time" datetime="<?= $post['post_date'];?>" title="<?= format_date($post['post_date']);?>"><?= generate_passed_time_text($post['post_date']);?> назад</time>
                            </div>
                        </a>
                    </div>
                    <div class="post__indicators">
                        <div class="post__buttons">
                        <?php
                        $href = ($current_user_id === $post['user_id']) ? '' : 'href="/like.php?id=' . $post['id'] .'"';
                        ?>
                            <a class="post__indicator post__indicator--likes button" <?=$href; ?> title="Лайк">
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
                        </div>
                    </div>
                </footer>
            </article>
            <?php endforeach; ?>
        </div>
        <div class="popular__page-links">
            <?php if ($page > 1): ?>
            <a class="popular__page-link popular__page-link--prev button button--gray" href="<?=update_query_params('page', $page - 1)?>">Предыдущая страница</a>
            <?php endif;
            if ($total_count > $page): ?>
            <a class="popular__page-link popular__page-link--next button button--gray" href="<?=update_query_params('page', $page + 1)?>">Следующая страница</a>
            <?php endif; ?>
        </div>
    </div>
</section>
