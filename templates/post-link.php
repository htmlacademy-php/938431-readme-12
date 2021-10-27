<div class="post-link__wrapper">
    <a class="post-link__external" href="<?=$post['p_url'] ?>" title="Перейти по ссылке">
    <div class="post-link__icon-wrapper">
        <img src="<?=generate_favicon_url($post['p_url']); ?>" alt="Иконка">
    </div>
    <div class="post-link__info">
        <h3><?= htmlspecialchars($post['p_title']); ?></h3>
        <span><?=parse_url($post['p_url'], PHP_URL_HOST); ?></span>
    </div>
    <svg class="post-link__arrow" width="11" height="16">
        <use xlink:href="#icon-arrow-right-ad"></use>
    </svg>
    </a>
</div>
