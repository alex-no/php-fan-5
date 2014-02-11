<article>
    <h1 class="header">PHP-FAN.5: Presentation and testing<span> / PHP-FAN.5: Презентация и тестирование</span></h1>

    <p>Каждая ссылка из списка демонстрирует одну-две функции фреймворка. На страницах выводится готовый результат. Для изучения возможностей фреймворка нужно параллельно смотреть соответствующие исходные коды php- и tpl-файлов, которые расположены в каталоге:<br /><strong>{$test_dir}</strong></p>
    <p>Для лучшего овладения программой попробуйте самостоятельно изменять представленый код, выполнять команды со своими значениями.</p>
    <p>Желаю успехов!</p>

    <h2>Представленные тесты:</h2>
    <div class="test_list">
        {foreach from=$tests item='description' key='file_name'}
            {*
            <dt>
                <a href="/test/{=isset($description['link']) ? $description['link'] : $file_name}.html">{$file_name}</a>
            </dt>
            <dd>
                <div>{$description['en']} / <span>{$description['ru']}</span></div>
            </dd>
            *}
            <a href="/test/{=isset($description['link']) ? $description['link'] : $file_name}.html">
                <span class="f_name">{$file_name}</span>
                <span class="desc_en">{$description['en']} / </span>
                <span class="desc_ru">{$description['ru']}</span>
            </a>

        {/foreach}
    </div>

    <h2>Другие возможности PHP-FAN:</h2>
    <ol id="other_possibility">
        <li><a href="/addition/tune_fan.html">Тонкая настройка PHP-FAN / Tuning PHP-FAN</a></li>
        <li><a href="/addition/auxiliary_app.html">Служебные приложения / Auxiliary applications</a></li>
        <li><a href="/addition/code_standard.html">Стандарты кодирования PHP-FAN / Coding Standards PHP-FAN</a></li>
    </ol>
</article>