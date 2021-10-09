<main class="page__main page__main--profile">
  <h1 class="visually-hidden">Профиль</h1>
  <div class="profile profile--default">
    <div class="profile__user-wrapper">
      <div class="profile__user user container">
        <div class="profile__user-info user__info">
          <div class="profile__avatar user__avatar">
            <img class="profile__picture user__picture" src="<?=htmlspecialchars($user['u_avatar']); ?>" alt="Аватар пользователя">
          </div>
          <div class="profile__name-wrapper user__name-wrapper">
            <span class="profile__name user__name"><?=$user['u_name']?></span>
            <time class="profile__user-time user__time" datetime="<?=$user['dt_add'];?>"><?= generate_passed_time_text($user['dt_add']);?> на сайте</time>
            </div>
          </div>
          <div class="profile__rating user__rating">
            <p class="profile__rating-item user__rating-item user__rating-item--publications">
            <span class="user__rating-amount"><?=$user['post_count'];?></span>
            <span class="profile__rating-text user__rating-text">публикаций</span>
            </p>
            <p class="profile__rating-item user__rating-item user__rating-item--subscribers">
            <span class="user__rating-amount"><?=$user['subs_count'];?></span>
            <span class="profile__rating-text user__rating-text">подписчиков</span>
            </p>
          </div>
          <div class="profile__user-buttons user__buttons">
            <button class="profile__user-button user__button user__button--subscription button button--main" type="button"><?=($is_subscribed) ? 'Отписаться' : 'Подписаться' ?></button>
            <?php if ($is_subscribed): ?>
            <a class="profile__user-button user__button user__button--writing button button--green" href="#">Сообщение</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <div class="profile__tabs-wrapper tabs">
      <div class="container">
        <div class="profile__tabs filters">
          <b class="profile__tabs-caption filters__caption">Показать:</b>
          <ul class="profile__tabs-list filters__list tabs__list">
            <li class="profile__tabs-item filters__item">
              <a class="profile__tabs-link filters__button filters__button--active tabs__item tabs__item--active button">Посты</a>
            </li>
            <li class="profile__tabs-item filters__item">
              <a class="profile__tabs-link filters__button tabs__item button" href="#">Лайки</a>
            </li>
            <li class="profile__tabs-item filters__item">
              <a class="profile__tabs-link filters__button tabs__item button" href="#">Подписки</a>
            </li>
          </ul>
        </div>
        <div class="profile__tab-content">
        <?=$tab_content;?>
        </div>
      </div>
    </div>
  </div>
</main>
