<section class="page__main page__main--popular">
    <div class="container">
        <h1 class="page__title page__title--popular">Популярное</h1>
    </div>
    <div class="popular container">
        <div class="popular__filters-wrapper">
            <div class="popular__sorting sorting">
                <b class="popular__sorting-caption sorting__caption">Сортировка:</b>
                <ul class="popular__sorting-list sorting__list">
                    <li class="sorting__item sorting__item--popular">
                        <a class="sorting__link sorting__link--active" href="#">
                            <span>Популярность</span>
                            <svg class="sorting__icon" width="10" height="12">
                                <use xlink:href="#icon-sort"></use>
                            </svg>
                        </a>
                    </li>
                    <li class="sorting__item">
                        <a class="sorting__link" href="#">
                            <span>Лайки</span>
                            <svg class="sorting__icon" width="10" height="12">
                                <use xlink:href="#icon-sort"></use>
                            </svg>
                        </a>
                    </li>
                    <li class="sorting__item">
                        <a class="sorting__link" href="#">
                            <span>Дата</span>
                            <svg class="sorting__icon" width="10" height="12">
                                <use xlink:href="#icon-sort"></use>
                            </svg>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="popular__filters filters">
                <b class="popular__filters-caption filters__caption">Тип контента:</b>
                <ul class="popular__filters-list filters__list">
                    <li class="popular__filters-item popular__filters-item--all filters__item filters__item--all">
                        <a class="filters__button filters__button--ellipse filters__button--all
                        <?=($filter === 0)
                        ? ' filters__button--active' : ''?>" href="/">
                            <span>Все</span>
                        </a>
                    </li>
                    <?php foreach ($types as $type): ?>
                    <li class="popular__filters-item filters__item">
                        <!-- TODO: Адрес с параметром запроса - тип фильтра -->
                        <a class="filters__button filters__button--<?=$type['p_type']?> <?=($filter === $type['id']) ? 'filters__button--active' : ''?>
                         button" href="<?=$type['url']?>">
                            <span class="visually-hidden">Фото</span>
                            <svg class="filters__icon"
                              width="<?=$type['width'] ?>" height="<?=$type['height'] ?>">
                                <use xlink:href="#icon-filter-<?=$type['p_type'] ?>"></use>
                            </svg>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="popular__posts">
            <?php foreach ($posts as $post): ?>
            <article class="popular__post post post-<?= $post['p_type'] ?>">
                <header class="post__header">
                    <a href="<?="/post.php?id=" . $post['id']?>">
                        <h2><?= htmlspecialchars($post['p_title']); ?></h2>
                    </a>
                </header>
                <div class="post__main">
                    <!-- Разные типы постов -->
                    <?php switch($post['p_type']):
                    case 'quote': ?>
                    <!-- Цитата -->
                    <blockquote>
                        <p><?= htmlspecialchars($post['p_text']); ?></p>
                        <cite>Неизвестный Автор</cite>
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
                        <img src="img/<?= $post['p_url'] ?>" alt="Фото от пользователя" width="360" height="240">
                    </div>
                    <?php break; ?>

                    <?php case 'link': ?>
                        <!-- Ссылка -->
                    <div class="post-link__wrapper">
                        <a class="post-link__external" href="http://<?= $post['p_url'] ?>" title="Перейти по ссылке">
                            <div class="post-link__info-wrapper">
                                <div class="post-link__icon-wrapper">
                                    <img src="https://www.google.com/s2/favicons?domain=vitadental.ru" alt="Иконка">
                                </div>
                                <div class="post-link__info">
                                    <h3><?= htmlspecialchars($post['p_text']); ?></h3>
                                </div>
                            </div>
                            <span><?= htmlspecialchars($post['p_url']); ?></span>
                        </a>
                    </div>
                    <?php break; ?>

                    <?php case 'video': ?>
                        <!-- Видео -->
                    <div class="post-video__block">
                        <div class="post-video__preview">
                            <?=embed_youtube_cover($post['p_url']); ?>
                            <img src="img/coast-medium.jpg" alt="Превью к видео" width="360" height="188">
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
                        <a class="post__author-link" href="#" title="Автор">
                            <div class="post__avatar-wrapper">
                                <!--укажите путь к файлу аватара-->
                                <img class="post__author-avatar" src="img/<?= $post['u_avatar'] ?>" alt="Аватар пользователя">
                            </div>
                            <div class="post__info">
                                <b class="post__author-name"><?= htmlspecialchars($post['u_name']); ?></b>
                                <time class="post__time" datetime="<?= $post['p_date'];?>" title="<?= format_date($post['p_date']);?>"><?= generate_passed_time_text($post['p_date']);?></time>
                            </div>
                        </a>
                    </div>
                    <div class="post__indicators">
                        <div class="post__buttons">
                            <a class="post__indicator post__indicator--likes button" href="#" title="Лайк">
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
                    </div>
                </footer>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
