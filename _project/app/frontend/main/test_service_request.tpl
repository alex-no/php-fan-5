<article>
    <h1 class="header">PHP-FAN.5: Main/Add request. Service of request <span>/ PHP-FAN.5: Main/Add request. Сервис request</span></h1>

    {*==== Show result of test / Отображение результатов теста ====*}
    <dl class="result">
        <dt>All headers of request: </dt> <dd><pre>{$request0}</pre></dd>
        <dt>Add request "aaa": </dt> <dd><pre>{$request1}</pre></dd>
        <dt>All add requests: </dt> <dd><pre>{$request2}</pre></dd>
        <dt>All full requests: </dt> <dd><pre>{$request3}</pre></dd>
        <dt>Priority add request and GET: </dt> <dd><pre>{$request4}</pre></dd>
    </dl>
    {*==== End of Result / Окончание результатов ====*}

    <p>
        URN формируются таким образом, чтобы глядя на него можно было легко понять, какой файл будет определять основное содержимое страницы. При этом, на любой запрос от клиента сначала определяется наименование application (как это делается будет рассмотрено в отдельном примере). Далее, в каталоге "_project/app" находи подкаталог с названием application и внутри него подкаталог с именем "main". Все пути к файлам каталога main будут соответствовать URN запрошенному клиентом. Если взять путь к основному php-файлу от каталога main, отбросить расширение и сделать explode по разделителю каталога ("/"), то полученный массив будет так называемый <span class="keyword">Main Request</span>. К примеру, <span class="keyword">Main Request</span> для данного файла будет состоять из одного элемента в массиве: array('test_service_request').
    </p>
    <p>
        Если к URN добавить элементы (как в подкаталог), то "лишние элементы" попадут в другой специальный массив <span class="keyword">Additional Request</span>. Массив <span class="keyword">Additional Request</span> содержит элементы двух типов:
    </p>
    <ul class="article_list">
        <li>с числовыми индексами, по аналогии с <span class="keyword">Main Request</span>;</li>
        <li>в виде hash (ассоциативный массив: ключ => значение);</li>
    </ul>
    <p>
        Hash формируется на основании обычного числового массива. Каждый элемент исходного массива разбивается на две части (ключ и значение), а затем добавляется в общий массив. В качестве разделителей элементов используется символ, указанный в config-файле (изначально это "-").
    </p>
    <p>
        Более подробно правила формирования <span class="keyword">Main Request</span> и <span class="keyword">Additional Request</span> будут рассмотрены в отдельном тесте{*ToDo: link to this test*}. Цель данного теста - научиться получать данные из этих и других массивов.<br /><br /><br />
    </p>
    <p>
        Получение любой информации о запросе клиента осуществляется с помощью <span class="keyword">сервиса request</span>. Получить объект <span class="keyword">сервиса request</span> можно классическим способом{*ToDo: link to describing services*}, но для упрощения работы в любом блоке есть свойство request, содержащее ссылку на этот объект. Доступ к данным осуществляется с помощью двух основных методов:
    </p>
    <ul class="article_list">
        <li><b>get($sKey, $sType, $mDefaultValue)</b> - получение отдельного элемента массива;</li>
        <li><b>getAll($sType)</b> - получение массива целиком;</li>
    </ul><br />
    <p>
        Параметр $sType определяет источник/источники данных. Источник данных задается буквами латинского алфавита в верхем регистре:
    </p>
    <ul class="article_list">
        <li><b>A</b> - Add(itional) request;</li>
        <li><b>B</b> - Both = Main request + Add request;</li>
        <li><b>C</b> - Cookies &Gt; $_COOKIE;</li>
        <li><b>E</b> - Environment variables &Gt; $_ENV;</li>
        <li><b>F</b> - Files (uploaded) &Gt; $_FILES;</li>
        <li><b>G</b> - Get parameters &Gt; $_GET;</li>
        <li><b>H</b> - Headers;</li>
        <li><b>M</b> - Main request;</li>
        <li><b>O</b> - Option list in CLI-mode;</li>
        <li><b>P</b> - Post parameters &Gt; $_POST;</li>
        <li><b>R</b> - Request parameters &Gt; $_REQUEST;</li>
        <li><b>S</b> - Server data &Gt; $_SERVER;</li>
    </ul>
    <p>
        Использование букв для указания типа данных позволяет комбинировать данные из разных источников. Например, такой запрос: <b>service('request')-&gt;get('param1', 'PGC', 'Nothing');</b> - проверит наличие элемента 'param1' сначала в массиве $_POST, затем в $_GET и после этого в $_COOKIE. Какое значение будет раньше обнаружено, то и будет возвращено. Если указанный элемент не найден ни в одном из указанных массивов, метод вернет текст 'Nothing', указанный здесь по умолчанию. Таким образом, с помощь порядка буквенных индексов можно указывать не только источники данных но и приоритетность их выбора.
    </p>


    <div class="add_tasks">
        <h3>Для лучшего овладения материалом:</h3>
        <ul>
            <li>Попробуйте произвольно менять значение Additional Request, при вызове текущей страницы, и посмотрите как изменится значение $request3 выводимое здесь.</li>
            <li>Попробуйте добавить GET-парамеры в текущий запрос и посмотрите как изменится значение $request4. Попробуйте указать указать GET и Additional Request с одинаковыми ключами, а затем поменяйте приоритность их просмотра для $request4 и посмотрите на результат.</li>
            <li>Попробуйте получать данные из других источников, например из HTTP-заголовков.</li>
        </ul>
    </div>
</article>

<div class="back2index">
    <a href="/index.html"><span>Return to list of tests</span><span class="no_unl"> / </span><span>Вернуться к списку тестовых файлов</span></a>
</div>
