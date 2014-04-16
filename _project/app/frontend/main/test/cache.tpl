<section>
    <h1>PHP-FAN.5: Test of operation with cache<span> / PHP-FAN.5: Проверка работы с кэшем</span></h1>

    {*==== Show result of test / Отображение результатов теста ====*}
    <div class="result">
        <h2>Test file cache</h2>
        <dl>
            <dt>Data: </dt> <dd><pre>{$file_value}</pre></dd>
            <dt>Meta: </dt> <dd><pre>{$file_meta}</pre></dd>
        </dl>

        {if !empty($is_memcache)}
            <h2>Test memory cache</h2>
            <dl>
                <dt>Data: </dt> <dd><pre>{$memory_value}</pre></dd>
                <dt>Meta: </dt> <dd><pre>{$memory_meta}</pre></dd>
            </dl>
        {/if}
    </div>
    {*==== End of Result / Окончание результатов ====*}

    {$description}
    {$task_list}

</section>