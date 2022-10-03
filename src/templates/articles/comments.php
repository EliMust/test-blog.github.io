<?php include __DIR__ . '/../header.php'; ?>
<h1>Оставить комментарий</h1>
<?php if(!empty($error)): ?>
    <div style="color: red;"><?= $error ?></div>
<?php endif; ?>
<form action="/articles/add" method="post">
    <label for="text">Текст статьи</label><br>
    <textarea name="text" id="text" rows="10" cols="80"><?= $_POST['text'] ?? '' ?></textarea><br>
    <br>
    <input type="submit" value="Опубликовать комментарий">
</form>
<?php include __DIR__ . '/../footer.php'; ?>
