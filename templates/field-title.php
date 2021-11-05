<div class="adding-post__input-wrapper form__input-wrapper">
    <label class="adding-post__label form__label" for="title"><?= $label; ?><span class="form__input-required"> *</span></label>
    <div class="form__input-section <?php if ($error) {
        echo 'form__input-section--error';
    } ?>">
        <input class="adding-post__input form__input" id="title" type="text" name="title"
               placeholder="Введите заголовок" value="<?= get_post_value('title'); ?>">
        <button class="form__error-button button" type="button">!<span
                class="visually-hidden">Информация об ошибке</span></button>
        <div class="form__error-text">
            <h3 class="form__error-title"><?= $label; ?></h3>
            <p class="form__error-desc"><?= $error; ?></p>
        </div>
    </div>
</div>
