<h2>
    <a href="<?="/post.php?id=" . $post['id']?>">
        <?= htmlspecialchars($post['post_title']); ?>
    </a>
</h2>
<div class="post-photo__image-wrapper">
    <img src="<?=$post['post_url'] ?>" alt="Фото от пользователя" width="760" height="396">
</div>
