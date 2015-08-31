<section class="test">
    <h3>{#CHECK_LOG_DIRECTORIES}</h3>

    <ul class="log_dir_list">
        <?php foreach ($aLogDir as $k => $v) : ?>
            <li>
                <img src="image/<?php echo $v['writable'] ? 'correct' : 'incorrect' ; ?>.gif" />
                <b><?php echo $k; ?>-log-dir:</b>
                <code><?php echo $v['writable'] ? realpath($v['dir']) : $v['dir']; ?></code>
                <?php if (!$v['writable']) : ?>
                    <br />{#PLEASE_CREATE_DIR}.
                    <?php if (!empty($v['parent'])) : ?>
                        {#MAKE_WRITABLE_DIR_A} <b><?php echo $v['parent']; ?></b> {#MAKE_WRITABLE_DIR_B}.
                    <?php endif ?>
                <?php endif ?>
            </li>
        <?php endforeach ?>
    </ul>
</section>