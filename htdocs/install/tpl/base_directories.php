<h2>{#CHECK_SYS_DIRECTORIES}</h2>
<section class="test">
    <h3>{#CHECK_BASE_DIRECTORIES}</h3>
    <p>{#ROOT_DIR}: <code class="important"><?php echo $sBaseDir; ?></code></p>

    <?php if (empty($sIndexDir)) : ?>
        <p>
            {#INDEX_FILE_NOT_FOUND}<img src="image/incorrect.gif" />
        </p>
    <?php else : ?>
        <p>
            {#INDEX_FILE}: <code><?php echo $sIndexDir; ?>/index.php</code>
            <?php if ($sBaseDir == $sIndexDir) : ?>
                <img src="image/correct.gif" />
            <?php else : ?>
                <br /><img src="image/warning.gif" /> {#INDEX_FILE_NOT_ROOT}.
            <?php endif ?>
        </p>

        <p>
            <?php if ($bIsCoreDir) : ?>
                {#CORE_DIR}: <code><?php echo $sCoreDir; ?></code>
                <?php if ($bIsCoreUnder) : ?>
                    <img src="image/correct.gif" />
                <?php else : ?>
                    <br /><img src="image/warning.gif" /> {#CORE_IS_NOT_UNDER}.
                <?php endif ?>
            <?php else : ?>
                {#CORE_DIR_NOT_FOUND} - <code><?php echo $sCoreDir; ?></code><img src="image/incorrect.gif" />
            <?php endif ?>
        </p>

        <p>
            <?php if ($bIsProjectDir) : ?>
                {#PROJECT_DIR}: <code><?php echo $sProjectDir; ?></code>
                <?php if ($bIsProjectUnder) : ?>
                    <img src="image/correct.gif" />
                <?php else : ?>
                    <br /><img src="image/warning.gif" /> {#PROJECT_IS_NOT_UNDER}.
                <?php endif ?>
            <?php elseif ($bIsDefinedProjectDir) : ?>
                {#PROJECT_DIR_INCORRECT_SET} - <code><?php echo $sProjectDir; ?></code><img src="image/incorrect.gif" />
            <?php else : ?>
                {#PROJECT_DIR_NOT_FOUND} - <code><?php echo $sProjectDir; ?></code><img src="image/incorrect.gif" />
            <?php endif ?>
        </p>

        <p>
            <?php if (empty($sBootstrapConfig)) : ?>
                <img src="image/warning.gif" /> {#BOOTSTRAP_CONFIG_NOT_FOUND}.
            <?php else : ?>
                {#BOOTSTRAP_CONFIG}: <code><?php echo $sBootstrapConfig; ?></code><img src="image/correct.gif" />
            <?php endif ?>
        </p>

    <?php endif ?>

</section>