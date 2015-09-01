<h2>{#RESULT_INFO}</h2>
<section class="test result">
    <h3>{#YOUR_PHP_FAN}</h3>
    <div>
        Version: <b><?php echo $sFanVer; ?></b>
    </div>

    <?php if (!empty($sLogViewer)) : ?>
        <div>
            {#SEE_LOG_VIEWER}: <a href="<?php echo $sLogViewer; ?>" target="_blank"><?php echo $sLogViewer; ?></a>.
        </div>
    <?php endif ?>

    <div>
        {#DO_NOT_FORGET_REMOVE_INSTALL}.
    </div>
</section>