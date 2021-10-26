<main class="page__main page__main--messages">
    <h1 class="visually-hidden">Личные сообщения</h1>
    <section class="messages tabs">
    <h2 class="visually-hidden">Сообщения</h2>
    <div class="messages__contacts">
        <ul class="messages__contacts-list tabs__list">
        <?php foreach ($recipients as $user): ?>
        <li class="messages__contacts-item <?php if ($user['new_count']) echo ' messages__contacts-item--new'; ?>">
            <?php
                $class = 'messages__contacts-tab tabs__item';
                $href_attribute = 'href="' . update_query_params('id', $user['id']) . '"';
                if ($active_user_id == $user['id']) {
                    $class = $class . ' messages__contacts-tab--active tabs__item--active';
                    $href_attribute = '';
                }
            ?>
            <a class="<?=$class; ?>" <?=$href_attribute; ?>>
            <div class="messages__avatar-wrapper">
                <?php if ($user['u_avatar']): ?>
                <img class="messages__avatar" src="<?=$user['u_avatar']; ?>" width="60" height="60" alt="Аватар пользователя">
                <?php endif;
                  if ($user['new_count']): ?>
                <i class="messages__indicator"><?=htmlspecialchars($user['new_count']); ?></i>
                <?php endif; ?>
            </div>
            <div class="messages__info">
                <span class="messages__contact-name">
                <?=htmlspecialchars($user['u_name']); ?>
                </span>
                <div class="messages__preview">
                <p class="messages__preview-text">
                    <?php if ($logged_user['id'] == $user['sender_id']) {
                        echo 'Вы: ';
                    }
                     echo htmlspecialchars(cut_excerpt_2($user['m_content'], 25));
                    ?>
                </p>
                <time class="messages__preview-time" datetime="<?=$user['dt_add']?>">
                    <?=$user['format_dt']; ?>
                </time>
                </div>
            </div>
            </a>
        </li>
        <?php endforeach; ?>
        </ul>
    </div>
    <div class="messages__chat">
        <div class="messages__chat-wrapper">
        <ul class="messages__list tabs__content tabs__content--active">
            <?php foreach($messages as $message): ?>
            <li class="messages__item <?php if ($logged_user['id'] == $message['u_id']) echo ' messages__item--my'; ?>">
            <div class="messages__info-wrapper">
                <div class="messages__item-avatar">
                <a class="messages__author-link" href="/profile.php?id=<?=$message['u_id']; ?>">
                    <?php if ($message['u_avatar']): ?>
                    <img class="messages__avatar" src="<?=$message['u_avatar']; ?>" width="40" height="40" alt="Аватар пользователя">
                    <?php endif; ?>
                </a>
                </div>
                <div class="messages__item-info">
                <a class="messages__author" href="/profile.php?id=<?=$message['u_id']; ?>">
                    <?=htmlspecialchars($message['u_name']); ?>
                </a>
                <time class="messages__time" datetime="
                <?=$message['dt_add']; ?>">
                    <?= generate_passed_time_text($message['dt_add']);?> назад
                </time>
                </div>
            </div>
            <p class="messages__text">
                <?=htmlspecialchars($message['m_content']); ?>
            </p>
            </li>
            <?php endforeach; ?>
        </ul>
        </div>
        <div class="comments">
        <?php if ($active_user_id): ?>
        <form class="comments__form form" action="/messages.php?id=<?=$active_user_id; ?>" method="post">
            <input type="hidden" name="active-user-id" value="<?=$active_user_id; ?>">
            <div class="comments__my-avatar">
            <?php if ($logged_user['u_avatar']): ?>
                <img class="comments__picture" src="<?=$logged_user['u_avatar']; ?>" width="40" height="40" alt="Аватар пользователя">
            <?php endif; ?>
            </div>
            <div class="form__input-section <?php if(!empty($errors['message'])) echo 'form__input-section--error'; ?>">
            <textarea class="comments__textarea form__textarea form__input" name="message" placeholder="Ваше сообщение"></textarea>
            <label class="visually-hidden">Ваше сообщение</label>
            <button class="form__error-button button" type="button">!</button>
            <div class="form__error-text">
                <h3 class="form__error-title">Ошибка валидации</h3>
                <p class="form__error-desc"><?=$errors['message']; ?></p>
            </div>
            </div>
            <button class="comments__submit button button--green" type="submit">Отправить</button>
        </form>
        <?php endif; ?>
        </div>
    </div>
    </section>
</main>
