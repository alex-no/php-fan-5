<article>
    <h1 class="header">PHP-FAN.5: List of test-files<span> / PHP-FAN.5: Список основных тестовых файлов</span></h1>

    <p>Каждая ссылка из списка демонстрирует одну-две функции фреймворка. На страницах выводится готовый результат. Для изучения возможностей фреймворка нужно параллельно смотреть соответствующие исходные коды php- и tpl-файлов, которые расположены в каталоге:<br /><strong>{$test_dir}</strong></p>
    <p>Для лучшего овладения программой попробуйте самостоятельно изменять представленый код, выполнять команды со своими значениями.</p>
    <p>Желаю успехов!</p>

    <h2>Файлы и представленные в них тесты:</h2>
    <dl class="test_list">
        {foreach from=$tests item='description' key='file_name'}
            <dt>
                <a href="{=isset($description['link']) ? $description['link'] : $file_name}.html">{$file_name}.php</a>
            </dt>
            <dd>
                <div>{$description['ru']}</div>
                <div>{$description['en']}</div>
            </dd>
        {/foreach}
    </dl>
</article>