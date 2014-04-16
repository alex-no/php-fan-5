<section>
    <h1>PHP-FAN.5: Test of operation with DB by entity<span> / PHP-FAN.5: Проверка работы с БД при помощи entity</span></h1>
{if $isCorrect}

    {$entity_intro}

{*==== Test SELECT row by ID / Проверка загрузки row по ID ====*}
    <h2>Test SELECT row by ID / Проверка загрузки row по ID</h2>
    <dl class="result">
        <dt>Строка, полученная из таблицы "test_primary": </dt> <dd><pre>{$row_by_id}</pre></dd>
    </dl>

    {$entity_t1}

{*==== Test SELECT row by Key / Проверка загрузки row с помощью ключа ====*}
    <h2>Test SELECT row by Key / Проверка загрузки row с помощью ключа</h2>
    <dl class="result">
        <dt>Строка, полученная из таблицы "test_primary": </dt> <dd><pre>{$row_by_key}</pre></dd>
    </dl>

    {$entity_t2}

{*==== Test SELECT rowset by Key / Проверка загрузки rowset с помощью ключа ====*}
    <h2>Test SELECT rowset by Key / Проверка загрузки rowset с помощью ключа</h2>
    <dl class="result">
        <dt>Полный массив полученных данных: </dt> <dd><pre>{$rowset_by_key}</pre></dd>
        <dt>Хэш-массив, сформированный из rowset: </dt> <dd><pre>{$hash_by_rowset}</pre></dd>
    </dl>

    {$entity_t3}

{*==== Get Top row from linked tables / Получить Top row из связанных таблиц ====*}
    <h2>Get Top row from linked table / Получить Top row из связанной таблицы</h2>
    <dl class="result">
        <dt>Top row: </dt> <dd><pre>{$top_row}</pre></dd>
    </dl>

    {$entity_t4}

{*==== Get Bottom rowset by linked table / Получить Bottom rowset с помощью связанной таблицы ====*}
    <h2>Get Bottom rowset by linked table / Получить Bottom rowset с помощью связанной таблицы</h2>
    <dl class="result">
        <dt>Bottom rowset: </dt> <dd><pre>{$bottom_rowset}</pre></dd>
    </dl>

    {$entity_t5}

{*==== Table description for "test_subtable" / Описание таблицы "test_subtable" ====*}
    <h2>Table description for "test_subtable" / Описание таблицы "test_subtable"</h2>
    <dl class="result">
        <dt>Table commentary: </dt> <dd><pre>{$comment}</pre></dd>
        <dt>Description of table: </dt> <dd><pre>{$description}</pre></dd>
    </dl>

    {$entity_t6}

{*==== Modify DB-data / Модифицирование DB-данных ====*}
    <h2>Modify DB-data / Модифицирование DB-данных</h2>

    {$entity_t7}

{*==== Serialize/unserialize object of row-data / Сериализовать/десериализовать объект строки данных ====*}
    <h2>Serialize/unserialize object of row-data / Сериализовать/десериализовать объект строки данных</h2>
    <dl class="result">
        <dt>Serialized row: </dt> <dd>{$serialize}</dd>
        <dt>Unserialized row object: </dt> <dd><pre>{$test_unserialize}</pre></dd>
    </dl>

    {$entity_t8}

    {$task_list}

    {*==== Show error messages / Отображение сообщений об ошибках ====*}
{else}
    {$check_db}
{/if}
</section>