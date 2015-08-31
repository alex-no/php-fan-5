<section class="test">
    <h3>{#CHECK_CACHE_DIRECTORIES}</h3>

    <div id="tmp_dir">
        <img src="image/<?php echo $iIsTmp > 0 ? 'correct' : 'incorrect'; ?>.gif" />
        <?php if (empty($iIsTmp)) : ?>
            {#TEMP_DIR_IS_NOT_RECOGNIZED}
        <?php else : ?>
            <b>{#TEMP_DIR}:</b>
            <code><?php echo $sTempDir; ?></code>
            <?php if ($iIsTmp < -1) : ?>
                <br />{#TEMP_DIR_IS_NOT_WRITABLE}
            <?php elseif ($iIsTmp < 0) : ?>
                <br />{#TEMP_DIR_IS_NOT_EXISTS}
            <?php endif ?>
        <?php endif ?>
    </div>
    <ul class="cache_dir_list">
        <?php if ($iIsTmp > 0) : ?>
            <?php foreach ($aCacheDir as $k => $v) : ?>
                <li>
                    <img src="image/<?php echo $v['img']; ?>.gif" />
                    <b><?php echo $k; ?>-cache-dir:</b>
                    <code><?php echo $v['writable'] ? realpath($v['dir']) : $v['dir']; ?></code>
                    <?php if (!$v['writable'] && $v['required']) : ?>
                        <br />{#PLEASE_CREATE_DIR}.
                    <?php elseif (!$v['writable']) : ?>
                        <br />{#NEED_CREATE_DIR}.
                    <?php endif ?>
                </li>
            <?php endforeach ?>
        <?php endif ?>

    </ul>

</section>