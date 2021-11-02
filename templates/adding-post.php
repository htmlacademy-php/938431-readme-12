    <main class="page__main page__main--adding-post">
      <div class="page__main-section">
        <div class="container">
          <h1 class="page__title page__title--adding-post">Добавить публикацию</h1>
        </div>
        <div class="adding-post container">
          <div class="adding-post__tabs-wrapper tabs">
            <div class="adding-post__tabs filters">
              <ul class="adding-post__tabs-list filters__list tabs__list">
                <?php foreach ($types as $type): ?>
                <li class="adding-post__tabs-item filters__item">
                  <a class="adding-post__tabs-link filters__button filters__button--<?=$type['type_class']?> tabs__item button <?php if ($active_type === $type['type_class']) {
    echo 'filters__button--active tabs__item--active';
} ?>"
                    <?php if ($active_type !== $type['type_class']) {
    echo('href="' . $type['url'] . '"');
} ?>
                  >
                    <svg class="filters__icon" width="<?=$type['width'] ?>" height="<?=$type['height'] ?>">
                      <use xlink:href="#icon-filter-<?=$type['type_class'] ?>"></use>
                    </svg>
                    <span><?=$type['type_title'] ?></span>
                  </a>
                </li>
                <?php endforeach; ?>
              </ul>
            </div>
            <div class="adding-post__tab-content">
              <section class="adding-post__photo tabs__content <?php if ($active_type === 'photo') {
    echo ' tabs__content--active';
} ?>">
                <h2 class="visually-hidden">Форма добавления фото</h2>
                <form class="adding-post__form form" action="add.php" method="post" enctype="multipart/form-data" id="form-photo">
                  <div class="form__text-inputs-wrapper">
                    <div class="form__text-inputs">
                      <input type="hidden" name="post-type" id="post-type" value="photo">
                      <?=$title_field ?>
                      <div class="adding-post__input-wrapper form__input-wrapper">
                        <label class="adding-post__label form__label" for="photo-url"><?=$label['photo-url']; ?></label>
                        <div class="form__input-section  <?php if (isset($errors['photo-url'])) {
    echo 'form__input-section--error';
} ?>">
                          <input class="adding-post__input form__input" id="photo-url" type="text" name="photo-url" placeholder="Введите ссылку" value="<?=get_post_value('photo-url') ?>">
                          <button class="form__error-button button" type="button">!<span class="visually-hidden">Информация об ошибке</span></button>
                          <div class="form__error-text">
                            <h3 class="form__error-title"><?=$label['photo-url']; ?></h3>
                            <p class="form__error-desc"><?=$errors['photo-url'] ?? '' ?></p>
                          </div>
                        </div>
                      </div>
                      <?=$tags_field ?>
                    </div>
                    <?=$invalid_block ?>>
                  </div>
                  <div class="adding-post__input-file-container form__input-container form__input-container--file">
                    <div class="adding-post__input-file-button form__input-file-button form__input-file-button--photo button">
                        <span>Выбрать фото</span>
                        <svg class="adding-post__attach-icon form__attach-icon" width="10" height="20">
                            <use xlink:href="#icon-attach"></use>
                        </svg>
                        <input class="adding-post__input-file form__input-file" id="file" type="file" name="file" title=" ">
                    </div>
                  </div>
                  <div class="adding-post__buttons">
                    <button class="adding-post__submit button button--main" type="submit" id="photo-submit">Опубликовать</button>
                    <a class="adding-post__close" href="/">Закрыть</a>
                  </div>
                </form>
              </section>

              <section class="adding-post__video tabs__content <?php if ($active_type === 'video') {
    echo ' tabs__content--active';
} ?>">
                <h2 class="visually-hidden">Форма добавления видео</h2>
                <form class="adding-post__form form" action="add.php" method="post" enctype="multipart/form-data">
                  <div class="form__text-inputs-wrapper">
                    <div class="form__text-inputs">
                      <input type="hidden" name="post-type" id="post-type" value="video">
                      <?=$title_field ?>
                      <div class="adding-post__input-wrapper form__input-wrapper">
                        <label class="adding-post__label form__label" for="video-url"><?=$label['video-url']; ?> <span class="form__input-required">*</span></label>
                        <div class="form__input-section  <?php if (isset($errors['video-url'])) {
    echo 'form__input-section--error';
} ?>">
                          <input class="adding-post__input form__input" id="video-url" type="text" name="video-url" placeholder="Введите ссылку" value="<?=get_post_value('video-url'); ?>">
                          <button class="form__error-button button" type="button">!<span class="visually-hidden">Информация об ошибке</span></button>
                          <div class="form__error-text">
                            <h3 class="form__error-title"><?=$label['video-url']; ?></h3>
                            <p class="form__error-desc"><?=$errors['video-url']; ?></p>
                          </div>
                        </div>
                      </div>
                      <?=$tags_field ?>
                    </div>
                    <?=$invalid_block ?>
                  </div>

                  <div class="adding-post__buttons">
                    <button class="adding-post__submit button button--main" type="submit">Опубликовать</button>
                    <a class="adding-post__close" href="/">Закрыть</a>
                  </div>
                </form>
              </section>

              <section class="adding-post__text tabs__content <?php if ($active_type === 'text') {
    echo ' tabs__content--active';
} ?>">
                <h2 class="visually-hidden">Форма добавления текста</h2>
                <form class="adding-post__form form" action="add.php" method="post">
                  <div class="form__text-inputs-wrapper">
                    <div class="form__text-inputs">
                      <input type="hidden" name="post-type" id="post-type" value="text">
                      <?=$title_field ?>
                      <div class="adding-post__textarea-wrapper form__textarea-wrapper">
                        <label class="adding-post__label form__label" for="post-text"><?=$label['post-text']; ?> <span class="form__input-required">*</span></label>
                        <div class="form__input-section  <?php if (isset($errors['post-text'])) {
    echo 'form__input-section--error';
} ?>">
                          <textarea class="adding-post__textarea form__textarea form__input" id="post-text" name="post-text" placeholder="Введите текст публикации"><?=get_post_value('post-text'); ?></textarea>
                          <button class="form__error-button button" type="button">!<span class="visually-hidden">Информация об ошибке</span></button>
                          <div class="form__error-text">
                            <h3 class="form__error-title"><?=$label['post-text']; ?></h3>
                            <p class="form__error-desc"><?=$errors['post-text']; ?></p>
                          </div>
                        </div>
                      </div>
                      <?=$tags_field ?>
                    </div>
                    <?=$invalid_block ?>
                  </div>
                  <div class="adding-post__buttons">
                    <button class="adding-post__submit button button--main" type="submit">Опубликовать</button>
                    <a class="adding-post__close" href="/">Закрыть</a>
                  </div>
                </form>
              </section>

              <section class="adding-post__quote tabs__content <?php if ($active_type === 'quote') {
    echo ' tabs__content--active';
} ?>">
                <h2 class="visually-hidden">Форма добавления цитаты</h2>
                <form class="adding-post__form form" action="add.php" method="post">
                  <div class="form__text-inputs-wrapper">
                    <div class="form__text-inputs">
                      <input type="hidden" name="post-type" id="post-type" value="quote">
                      <?=$title_field ?>
                      <div class="adding-post__input-wrapper form__textarea-wrapper">
                        <label class="adding-post__label form__label" for="quote-text"><?=$label['quote-text']; ?> <span class="form__input-required">*</span></label>
                        <div class="form__input-section  <?php if (isset($errors['quote-text'])) {
    echo 'form__input-section--error';
} ?>">
                          <textarea class="adding-post__textarea adding-post__textarea--quote form__textarea form__input" id="quote-text" name="quote-text" placeholder="Текст цитаты"><?=get_post_value('quote-text'); ?></textarea>
                          <button class="form__error-button button" type="button">!<span class="visually-hidden">Информация об ошибке</span></button>
                          <div class="form__error-text">
                            <h3 class="form__error-title"><?=$label['quote-text']; ?></h3>
                            <p class="form__error-desc"><?=$errors['quote-text']; ?></p>
                          </div>
                        </div>
                      </div>
                      <div class="adding-post__textarea-wrapper form__input-wrapper">
                        <label class="adding-post__label form__label" for="quote-author"><?=$label['quote-author']; ?> <span class="form__input-required">*</span></label>
                        <div class="form__input-section  <?php if (isset($errors['quote-author'])) {
    echo 'form__input-section--error';
} ?>">
                          <input class="adding-post__input form__input" id="quote-author" type="text" name="quote-author" value="<?=get_post_value('quote-author'); ?>">
                          <button class="form__error-button button" type="button">!<span class="visually-hidden">Информация об ошибке</span></button>
                          <div class="form__error-text">
                            <h3 class="form__error-title"><?=$label['quote-author']; ?></h3>
                            <p class="form__error-desc"><?=$errors['quote-author']; ?></p>
                          </div>
                        </div>
                      </div>
                      <?=$tags_field ?>
                    </div>
                    <?=$invalid_block ?>
                  </div>
                  <div class="adding-post__buttons">
                    <button class="adding-post__submit button button--main" type="submit">Опубликовать</button>
                    <a class="adding-post__close" href="/">Закрыть</a>
                  </div>
                </form>
              </section>

              <section class="adding-post__link tabs__content <?php if ($active_type === 'link') {
    echo ' tabs__content--active';
} ?>">
                <h2 class="visually-hidden">Форма добавления ссылки</h2>
                <form class="adding-post__form form" action="add.php" method="post">
                  <div class="form__text-inputs-wrapper">
                    <div class="form__text-inputs">
                      <input type="hidden" name="post-type" id="post-type" value="link">
                      <?=$title_field ?>
                      <div class="adding-post__textarea-wrapper form__input-wrapper">
                        <label class="adding-post__label form__label" for="post-link"><?=$label['post-link']; ?> <span class="form__input-required">*</span></label>
                        <div class="form__input-section  <?php if (isset($errors['post-link'])) {
    echo 'form__input-section--error';
} ?>">
                          <input class="adding-post__input form__input" id="post-link" type="text" name="post-link" value="<?=get_post_value('post-link'); ?>">
                          <button class="form__error-button button" type="button">!<span class="visually-hidden">Информация об ошибке</span></button>
                          <div class="form__error-text">
                            <h3 class="form__error-title"><?=$label['post-link']; ?></h3>
                            <p class="form__error-desc"><?=$errors['post-link']; ?></p>
                          </div>
                        </div>
                      </div>
                      <?=$tags_field ?>
                    </div>
                    <?=$invalid_block; ?>
                  </div>
                  <div class="adding-post__buttons">
                    <button class="adding-post__submit button button--main" type="submit">Опубликовать</button>
                    <a class="adding-post__close" href="/">Закрыть</a>
                  </div>
                </form>
              </section>
            </div>
          </div>
        </div>
      </div>
    </main>
