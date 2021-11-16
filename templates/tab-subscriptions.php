<section class="profile__subscriptions tabs__content tabs__content--active">
    <h2 class="visually-hidden">Подписки</h2>
    <ul class="profile__subscriptions-list">
        <?php foreach ($users as $user) : ?>
            <li class="post-mini post-mini--photo post user">
                <div class="post-mini__user-info user__info">
                    <div class="post-mini__avatar user__avatar">
                        <a class="user__avatar-link" href="/profile.php?id=<?= $user['user_id'] ?>">
                            <?php if ($user['avatar']) : ?>
                                <img class="post-mini__picture user__picture" src="<?= $user['avatar'] ?>" width="60"
                                     height="60" alt="Аватар пользователя">
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="post-mini__name-wrapper user__name-wrapper">
                        <a class="post-mini__name user__name" href="/profile.php?id=<?= $user['user_id'] ?>">
                            <span><?= htmlspecialchars($user['username']); ?></span>
                        </a>
                        <time class="post-mini__time user__additional"
                              datetime="<?= $user['user_date'] ?>"><?= generate_passed_time_text($user['user_date']); ?>
                            на сайте
                        </time>
                    </div>
                </div>
                <div class="post-mini__rating user__rating">
                    <p class="post-mini__rating-item user__rating-item user__rating-item--publications">
                        <span
                            class="post-mini__rating-amount user__rating-amount"><?= $user['posts_count'] ?? 0; ?></span>
                        <span class="post-mini__rating-text user__rating-text">публикаций</span>
                    </p>
                    <p class="post-mini__rating-item user__rating-item user__rating-item--subscribers">
                        <span
                            class="post-mini__rating-amount user__rating-amount"><?= $user['subscriber_count'] ?? 0; ?></span>
                        <span class="post-mini__rating-text user__rating-text">подписчиков</span>
                    </p>
                </div>
                <div class="post-mini__user-buttons user__buttons">
                    <?php if ($current_user_id === $user['user_id']) : ?>
                        <a class="post-mini__user-button user__button user__button--subscription button button--quartz">.
                            . .</a>
                    <?php else : ?>
                        <a class="post-mini__user-button user__button user__button--subscription button <?= $user['is_logged_user_subscribed'] ? 'button--quartz' : 'button--main'; ?>"
                           href="/subscribe.php?id=<?= $user['user_id'] ?>">
                            <?= $user['is_logged_user_subscribed'] ? 'Отписаться' : 'Подписаться'; ?>
                        </a>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
