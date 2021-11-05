<div class="post-link__wrapper">
    <a class="post-link__external" href="<?= $post['post_url'] ?>" title="Перейти по ссылке">
        <div class="post-link__icon-wrapper">
            <img src="<?= generate_favicon_url($post['post_url']); ?>" alt="Иконка">
        </div>
        <div class="post-link__info">
            <h3><?= htmlspecialchars($post['post_title']); ?></h3>
            <span><?= parse_url($post['post_url'], PHP_URL_HOST); ?></span>
        </div>
        <svg class="post-link__arrow" width="11" height="16">
            <use xlink:href="#icon-arrow-right-ad"></use>
        </svg>
    </a>
</div>
