<div class="post__main">
    <div class="post-link__wrapper">
        <a class="post-link__external" href="<?= $post['post_url']; ?>" title="Перейти по ссылке">
            <div class="post-link__info-wrapper">
                <div class="post-link__icon-wrapper">
                    <img src="<?= generate_favicon_url($post['post_url']); ?>" alt="Иконка">
                </div>
                <div class="post-link__info">
                    <h3><?= parse_url($post['post_url'], PHP_URL_HOST); ?></h3>
                </div>
            </div>
        </a>
    </div>
</div>
