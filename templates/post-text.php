<h2>
    <a href="<?="/post.php?id=" . $post['id']?>">
        <?= htmlspecialchars($post['p_title']); ?>
    </a>
</h2>
<?php echo text_template($post['p_text']);
