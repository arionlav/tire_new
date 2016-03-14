<?
use config\App;

$title = "Ошибка";
?>

<h1>Ошибка</h1>
<h2>Что-то не так...</h2>
<div class="errorLinksHead">
    <p><?=urldecode($errorMsg) ?><p>
</div>
<div class="errorLinks">
    <a href="<?= App::url(['site/index']) ?>" style="margin-top: 10px">В главное меню</a>
</div>