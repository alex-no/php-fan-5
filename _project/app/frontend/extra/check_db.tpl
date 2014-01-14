{if !$isCorrect}
    {*==== MySQL isn't available or user don't have permission / MySQL не доступна или пользователь не имеет соответствующих прав ====*}
    {if $nOperationCode == 1}
        <h2>Error MySQL-connection</h2>
        <p>{$sErrorMessage}</p>
        {if $nErrorNumber == 1045}
            <p>Проверьте разрешен ли пользователю <b>{$aConnectParam['USER']}</b> доступ в БД. Возможно в <b>service.ini</b> не правильно указан пароль для этого пользователя.<br />Попробуйте загрузить в вашу БД файл <b>{$sSqlDir}/access.sql</b>. Этот файл обеспецивает доступ в БД пользователю, изначально указанному в <b>service.ini</b>. Загрузку этого файла может выполнять только пользователь имеющий права на запись в БД "mysql".<br />Если у Вас нет таких прав, узнайте у Вашего системного администратора данные пользователя, под которым Вы можете работать и укажите эти данные в <b>service.ini</b>, в разделе "<b>[database.DATABASE.common]</b>".</p>
        {else}
            <p>Проверьте запущен ли MySQL на сервере, на котором должна находится БД (<b>{$aConnectParam['HOST']}</b>). Если БД располагается на другом комьютере, убедитесь что к этому компьютеру есть доступ с сервера, на котором выполняется данная программа. Проверьте правильность данных в файле <b>service.ini</b>, в разделе "<b>[database.DATABASE.common]</b>".</p>
        {/if}
    {*==== DB isn't created / БД не создана ====*}
    {elseif $nOperationCode == 2}
        <h2>Error DB "{$aConnectParam['DATABASE']}"</h2>
        <p>{$sErrorMessage}</p>
        <p>Для работы с данным тестом в MySQL должна быть создана БД (<b>{$aConnectParam['DATABASE']}</b>). Попробуйте загрузить в вашу БД файл <b>{$sSqlDir}/min_tables.sql</b>. Если Ваша БД имеет иное название - исправьте его в файле <b>service.ini</b>, в разделе "<b>[database.DATABASE.common]</b>".</p>
    {*==== The entity class isn't created / Класс entity не создан ====*}
    {elseif $nOperationCode == 50}
        <h2>Error in Entity-class</h2>
        <p>{$sErrorMessage}</p>
        <p>Проверьте, что у Вас существует вышеназванный класс. Если Вы уже создали БД из файла <b>{$sSqlDir}/min_tables.sql</b>, то попробуйте воспользоваться <a href="/__tools/create_entity.html" target="_blank">инструментом для автоматического создания entity</a>. Этот класс так-же есть в исходном наборе файлов php-fan, который вы можете скачать с <a href="https://github.com/alex-no/php-fan-5/tree/test-FAN" target="_blank">github</a></p>
    {*==== The corresponding table isn't present in DB / Соответствующая таблица отсутствует в БД ====*}
    {elseif $nOperationCode == 51}
        <h2>Table isn't present</h2>
        <p>{$sErrorMessage}</p>
        <p>Таблица <b>{$sTableName}</b> отсутствует в БД. Воспользуйтесь файлом <b>{$sSqlDir}/min_tables.sql</b> для востановления этой таблицы.</p>
    {*==== Required fields isn't set in the table / Необходимые поля отсутствуют в таблице БД ====*}
    {elseif $nOperationCode == 60}
        <h2>Required fields isn't set in the table</h2>
        <p>Обязательные поля таблицы "<b>{$sTableName}</b>" не заданы:</p>
        <ul class="article_list">
        {foreach item=field from=$aNoFields}
            <li>{$field}</li>
        {/foreach}
        </ul>
        <p>Воспользуйтесь файлом <b>{$sSqlDir}/min_tables.sql</b> для востановления структуры этой таблицы.</p>
    {*==== Required data isn't present in the table / Необходимые записи отсутствуют в таблице БД ====*}
    {elseif $nOperationCode == 61}
        <h2>Required data isn't present in the table</h2>
        <p>Записи, необходимые для тестирования не внесены в таблицу "<b>{$sTableName}</b>". Список id записей:</p>
        <ul class="article_list">
        {foreach item=id from=$aNoData}
            <li>{$id}</li>
        {/foreach}
        </ul>
        <p>Воспользуйтесь файлом <b>{$sSqlDir}/test_data.sql</b> для востановления данных в этой таблице.</p>
    {/if}


    {if $nOperationCode < 60}
        <p>Сообщение об этой ошибке Вы также можете в <a href="/__log_viewer/" target="_blank">log_viewer</a>. Не забывайте просматривать там ошибки, в дальнейшем, если они будут возникать, в процессе разработки или эксплуатации проекта.</p>
    {/if}
{/if}