<main class="page__main page__main--publication">
    <div class="container">
        <h1 class="page__title page__title--publication"><?= htmlspecialchars($post['post_title']); ?></h1>
        <section class="post-details">
            <h2 class="visually-hidden">Публикация</h2>
            <div class="post-details__wrapper post-photo">
                <div class="post-details__main-block post post--details">
                    <?= $post_content; ?>
                    <div class="post__indicators">
                        <div class="post__info">
                            <time class="post__time" datetime="<?= $post['date_add']; ?>"
                                  title="<?= format_date($post['date_add']); ?>"><?= generate_passed_time_text($post['date_add']); ?>
                                назад
                            </time>
                        </div>
                        <span class="post__view"><?= $post['watch_count'] ?? 0; ?> просмотров</span>
                    </div>
                    <?php if ($hashtags) : ?>
                        <ul class="post__tags">
                            <?php foreach ($hashtags as $hash) : ?>
                                <li>
                                    <a href="search.php?q=%23<?= $hash['hashtag_title'] ?>">#<?= $hash['hashtag_title']; ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <div class="post__indicators">
                        <div class="post__buttons">
                            <?php
                            $href_like = $is_current_user ? '' : 'href="/like.php?id=' . $post['id'] . '"';
                            $href_repost = $is_current_user ? '' : 'href="/repost.php?id=' . $post['id'] . '"';
                            ?>
                            <a class="post__indicator post__indicator--likes button" <?= $href_like; ?> title="Лайк">
                                <svg class="post__indicator-icon" width="20" height="17">
                                    <use xlink:href="#icon-heart"></use>
                                </svg>
                                <svg class="post__indicator-icon post__indicator-icon--like-active" width="20"
                                     height="17">
                                    <use xlink:href="#icon-heart-active"></use>
                                </svg>
                                <span><?= $post['like_count'] ?? 0; ?></span>
                                <span class="visually-hidden">количество лайков</span>
                            </a>
                            <a class="post__indicator post__indicator--comments button" href="#comments"
                               title="Комментарии">
                                <svg class="post__indicator-icon" width="19" height="17">
                                    <use xlink:href="#icon-comment"></use>
                                </svg>
                                <span><?= $post['comment_count'] ?? 0; ?></span>
                                <span class="visually-hidden">количество комментариев</span>
                            </a>
                            <a class="post__indicator post__indicator--repost button"
                                <?= $href_repost; ?> title="Репост">
                                <svg class="post__indicator-icon" width="19" height="17">
                                    <use xlink:href="#icon-repost"></use>
                                </svg>
                                <span><?= $post['repost_count']; ?></span>
                                <span class="visually-hidden">количество репостов</span>
                            </a>
                        </div>
                    </div>
                    <div class="comments">
                        <?php if (!$is_current_user) : ?>
                            <form class="comments__form form" action="/post.php?id=<?= $post['id']; ?>" method="post">
                                <input type="hidden" name="post-id" value="<?= $post['id']; ?>">
                                <div class="comments__my-avatar">
                                    <?php if ($current_user_avatar) : ?>
                                        <img class="comments__picture" src="<?= $current_user_avatar; ?>" width="40"
                                             height="40" alt="Аватар пользователя">
                                    <?php endif; ?>
                                </div>
                                <div class="form__input-section <?php if (!empty($errors['comment'])) {
                                    echo 'form__input-section--error';
                                                                } ?>">
                <textarea class="comments__textarea form__textarea form__input"
                          id="comment" name="comment"
                          placeholder="Ваш комментарий"><?= get_post_value('comment'); ?></textarea>
                                    <label class="visually-hidden" for="comment">Ваш комментарий</label>
                                    <button class="form__error-button button" type="button">!</button>
                                    <div class="form__error-text">
                                        <h3 class="form__error-title">Ошибка валидации</h3>
                                        <p class="form__error-desc"><?= $errors['comment']; ?></p>
                                    </div>
                                </div>
                                <button class="comments__submit button button--green" type="submit">Отправить</button>
                            </form>
                        <?php endif; ?>
                        <div class="comments__list-wrapper">
                            <ul class="comments__list" id="comments">
                                <?php foreach ($comments as $comment) : ?>
                                    <li class="comments__item user">
                                        <div class="comments__avatar">
                                            <a class="user__avatar-link"
                                               href="/profile.php?id=<?= $comment['user_id'] ?>">
                                                <?php if ($comment['avatar']) : ?>
                                                    <img class="comments__picture" src="<?= $comment['avatar']; ?>"
                                                         alt="Аватар пользователя">
                                                <?php endif; ?>
                                            </a>
                                        </div>
                                        <div class="comments__info">
                                            <div class="comments__name-wrapper">
                                                <a class="comments__user-name"
                                                   href="/profile.php?id=<?= $comment['user_id'] ?>">
                                                    <span><?= htmlspecialchars($comment['username']); ?></span>
                                                </a>
                                                <time class="comments__time"
                                                      datetime="<?= $comment['comment_date']; ?>"><?= generate_passed_time_text($comment['comment_date']); ?>
                                                    назад
                                                </time>
                                            </div>
                                            <p class="comments__text">
                                                <?= htmlspecialchars($comment['comment_text']); ?>
                                            </p>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php if ($post['comment_count'] > count($comments)) : ?>
                                <a class="comments__more-link" href="<?= update_query_params('comments', 'all') ?>">
                                    <span>Показать все комментарии</span>
                                    <sup class="comments__amount"><?= $post['comment_count'] ?? 0; ?></sup>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="post-details__user user">
                    <div class="post-details__user-info user__info">
                        <div class="post-details__avatar user__avatar">
                            <a class="post-details__avatar-link user__avatar-link"
                               href="/profile.php?id=<?= $user['id']; ?>">
                                <?php if ($user['avatar']) : ?>
                                    <img class="post-details__picture user__picture" src="<?= $user['avatar']; ?>"
                                         width="60" height="60" alt="Аватар пользователя">
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="post-details__name-wrapper user__name-wrapper">
                            <a class="post-details__name user__name" href="/profile.php?id=<?= $user['id']; ?>">
                                <span><?= htmlspecialchars($user['username']); ?></span>
                            </a>
                            <time class="post-details__time user__time"
                                  datetime="<?= $user['date_add']; ?>"><?= generate_passed_time_text($user['date_add']); ?>
                                на сайте
                            </time>
                        </div>
                    </div>
                    <div class="post-details__rating user__rating">
                        <p class="post-details__rating-item user__rating-item user__rating-item--subscribers">
                            <span
                                    class="post-details__rating-amount user__rating-amount"><?= $user['subscriber_count']; ?></span>
                            <span class="post-details__rating-text user__rating-text">подписчиков</span>
                        </p>
                        <p class="post-details__rating-item user__rating-item user__rating-item--publications">
                            <span
                                    class="post-details__rating-amount user__rating-amount"><?= $user['posts_count']; ?></span>
                            <span class="post-details__rating-text user__rating-text">публикаций</span>
                        </p>
                    </div>
                    <div class="post-details__user-buttons user__buttons">
                        <?php if (!$is_current_user) : ?>
                            <a class="user__button user__button--subscription button button--main"
                               href="/subscribe.php?id=<?= $user['id'] ?>"><?= ($is_subscribed) ? 'Отписаться' : 'Подписаться' ?></a>
                        <?php endif; ?>
                        <?php if ($is_subscribed) : ?>
                            <a class="user__button user__button--writing button button--green"
                               href="/messages.php?id=<?= $user['id']; ?>">Отправить сообщение</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>
