<main class="page__main page__main--login">
    <div class="container">
        <h1 class="page__title page__title--login">Вход</h1>
    </div>
    <section class="login container">
        <h2 class="visually-hidden">Форма авторизации</h2>
        <form class="login__form form" action="/login.php" method="post">
            <div class="form__text-inputs-wrapper">
                <div class="form__text-inputs">
                    <div class="login__input-wrapper form__input-wrapper">
                        <label class="login__label form__label" for="login-email">Электронная почта</label>
                        <div class="form__input-section <?php if (isset($errors['login'])) {
                            echo 'form__input-section--error';
                        } ?>">
                            <input class="login__input form__input" id="login-email" type="email" name="login"
                                   placeholder="Укажите эл.почту" value="<?= get_post_value('login') ?>">
                            <button class="form__error-button button" type="button">!<span class="visually-hidden">Информация об ошибке</span>
                            </button>
                            <div class="form__error-text">
                                <h3 class="form__error-title">Электронная почта</h3>
                                <p class="form__error-desc"><?= $errors['login'] ?? ''; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="login__input-wrapper form__input-wrapper">
                        <label class="login__label form__label" for="login-password">Пароль</label>
                        <div class="form__input-section <?php if (isset($errors['password'])) {
                            echo 'form__input-section--error';
                        } ?>">
                            <input class="login__input form__input" id="login-password" type="password" name="password"
                                   placeholder="Введите пароль" value="<?= get_post_value('password') ?>">
                            <button class="form__error-button button button--main" type="button">!<span
                                    class="visually-hidden">Информация об ошибке</span></button>
                            <div class="form__error-text">
                                <h3 class="form__error-title">Пароль</h3>
                                <p class="form__error-desc"><?= $errors['password'] ?? '' ?></p>
                            </div>
                        </div>
                    </div>
                    <button class="login__submit button button--main" type="submit">Отправить</button>
                </div>
                <?= $invalid_block; ?>
            </div>
        </form>
    </section>
</main>
