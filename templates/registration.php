<main class="page__main page__main--registration">
  <div class="container">
    <h1 class="page__title page__title--registration">Регистрация</h1>
  </div>
  <section class="registration container">
    <h2 class="visually-hidden">Форма регистрации</h2>
    <form class="registration__form form" action="register.php" method="post" enctype="multipart/form-data">
      <div class="form__text-inputs-wrapper">
        <div class="form__text-inputs">
          <div class="registration__input-wrapper form__input-wrapper">
            <label class="registration__label form__label" for="registration-email"><?=$label['email']; ?> <span class="form__input-required">*</span></label>
            <div class="form__input-section  <?php if(!empty($errors['email'])) echo 'form__input-section--error' ?>">
              <input class="registration__input form__input" id="registration-email" type="email" name="email" placeholder="Укажите эл.почту" value="<?=get_post_value('email') ?>">
              <button class="form__error-button button" type="button">!<span class="visually-hidden">Информация об ошибке</span></button>
              <div class="form__error-text">
                <h3 class="form__error-title"><?=$label['email']; ?></h3>
                <p class="form__error-desc"><?=$errors['email'] ?? '' ?></p>
              </div>
            </div>
          </div>
          <div class="registration__input-wrapper form__input-wrapper">
            <label class="registration__label form__label" for="registration-login"><?=$label['login']; ?> <span class="form__input-required">*</span></label>
            <div class="form__input-section  <?php if(!empty($errors['login'])) echo 'form__input-section--error' ?>">
              <input class="registration__input form__input" id="registration-login" type="text" name="login" placeholder="Укажите логин" value="<?=get_post_value('login') ?>">
              <button class="form__error-button button" type="button">!<span class="visually-hidden">Информация об ошибке</span></button>
              <div class="form__error-text">
                <h3 class="form__error-title"><?=$label['login']; ?></h3>
                <p class="form__error-desc"><?=$errors['login'] ?? '' ?></p>
              </div>
            </div>
          </div>
          <div class="registration__input-wrapper form__input-wrapper">
            <label class="registration__label form__label" for="registration-password"><?=$label['password']; ?><span class="form__input-required">*</span></label>
            <div class="form__input-section  <?php if(!empty($errors['password'])) echo 'form__input-section--error' ?>">
              <input class="registration__input form__input" id="registration-password" type="password" name="password" placeholder="Придумайте пароль" value="<?=get_post_value('password') ?>">
              <button class="form__error-button button" type="button">!<span class="visually-hidden">Информация об ошибке</span></button>
              <div class="form__error-text">
                <h3 class="form__error-title"><?=$label['password']; ?></h3>
                <p class="form__error-desc"><?=$errors['password'] ?? '' ?></p>
              </div>
            </div>
          </div>
          <div class="registration__input-wrapper form__input-wrapper">
            <label class="registration__label form__label" for="registration-password-repeat"><?=$label['password-repeat']; ?><span class="form__input-required">*</span></label>
            <div class="form__input-section  <?php if(!empty($errors['password-repeat'])) echo 'form__input-section--error' ?>">
              <input class="registration__input form__input" id="registration-password-repeat" type="password" name="password-repeat" placeholder="Повторите пароль" value="<?=get_post_value('password-repeat') ?>">
              <button class="form__error-button button" type="button">!<span class="visually-hidden">Информация об ошибке</span></button>
              <div class="form__error-text">
                <h3 class="form__error-title"><?=$label['password-repeat']; ?></h3>
                <p class="form__error-desc"><?=$errors['password-repeat'] ?? '' ?></p>
              </div>
            </div>
          </div>
        </div>
        <?=$invalid_block ?>>

      </div>
      <div class="registration__input-file-container form__input-container form__input-container--file">
        <div class="registration__input-file-button form__input-file-button form__input-file-button--photo button">
            <span>Выбрать фото</span>
            <svg class="registration__attach-icon form__attach-icon" width="10" height="20">
                <use xlink:href="#icon-attach"></use>
            </svg>
            <input class="registration__input-file form__input-file" id="file" type="file" name="file" title=" ">
        </div>
      </div>
      <button class="registration__submit button button--main" type="submit">Отправить</button>
    </form>
  </section>
</main>
