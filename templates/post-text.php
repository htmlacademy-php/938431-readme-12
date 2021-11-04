<h2>
    <a href="<?= "/post.php?id=" . $post['id'] ?>">
        <?= htmlspecialchars($post['post_title']); ?>
    </a>
</h2>
<?php echo text_template($post['post_text']);
