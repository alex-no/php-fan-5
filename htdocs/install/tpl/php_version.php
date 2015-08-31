<h2>{#CHECK_BASIC_PARAMETERS}</h2>
<section class="test">
    <h3>{#CHECK_PHP_VER}</h3>
    <div>
        <?php if ($nVerType < 0) : ?>
        <img src="image/incorrect.gif" /> {#INCORRECT_PHP_VER} - <b><?php echo $nVerValue; ?></b>.<br />
        {#RENEW_PHP}
        <?php elseif ($nVerType == 0) : ?>
        <img src="image/obsolete.gif" /> {#OBSOLETE_PHP_VER} - <b><?php echo $nVerValue; ?></b>.<br />
        {#RENEW_PHP}
        <?php else : ?>
        <img src="image/correct.gif" /> {#CORRECT_PHP_VER} - <b><?php echo $nVerValue; ?></b>.
        <?php endif ?>
    </div>
</section>