<div class="form__invalid-block">
    <b class="form__invalid-slogan">Пожалуйста, исправьте следующие ошибки:</b>
    <ul class="form__invalid-list">
    <?php foreach ($errors as $key => $value): ?>
        <li class="form__invalid-item"><?=$label[$key], '. ', $value ?></li>
    <?php endforeach; ?>
    </ul>
</div>
