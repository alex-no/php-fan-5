<p>Материал, описанный ниже, поначалу покажется Вам очень сложным и запутанным. Но это только на первый взгляд. Если у Вас нет опыта работы с PHP-FAN (хотя-бы предыдущих версий), то лучше не пытаться пройти все примеры за один раз. Лучше детально разберите работу с первыми двумя примерами, а через некоторое время с третьим, четвертым и т.д. После того как Вы дойдете до последнего примера Вы поймете, что работа с <span class="keyword">entity</span> проста до "безобразия". Удачи! ;)</p>
<h2>Краткая информаци об объектах entity/rowset/row</h2>
<p>PHP-FAN предоставляет досточно простой и удобный способ доступа к данным - классы: <span class="keyword">entity/rowset/row</span>. Объект класса <span class="keyword">entity</span> - это некая сущность, представляющая собой образ источника данных. Данные получаются в виде "строк". Каждая строка представляется объектом класса <span class="keyword">row</span>. Несколько строк данных можно получить в виде объекта класса <span class="keyword">rowset</span>. Классы <span class="keyword">rowset/row</span> имеют методы <b>toArray()</b>, позволяющие легко преобразовывать полученные данные в массивы. Класс <span class="keyword">rowset</span>, кроме этого, имеет еще несколько методов (<b>getArrayAssoc, getColumn, getArrayHash)</b> позволяющих преобразовывать полученные данные в различные массивы.</p>
<p>В настоящее время класс <span class="keyword">entity</span> может работать только с одним источником данных - БД MySQL. Однако сейчас идет работа над тем, чтобы была возможность получать данные из самых разных источников, среди которых могут быть не только различные БД, но и SOAP, REST, файловая система и др. Пока рассмотрим работу только с БД MySQL.</p>
<p>Для каждой таблицы БД создается отдельный класс <span class="keyword">entity</span> (<b>rowset/row</b> создавать не обязательно, но можно, если это необходимо). Изначально класс <span class="keyword">entity</span> может быть "пустышкой" (<i>обладать только свойствами/методами родительских классов</i>), при условии, что название каталога, в котором он находится в точности совпадает с названием таблицы в БД (<i>иначе в этом классе дополнительно прописываются свойства/методы определяющие источник</i>). Но Вы можете потом добавлять в этот класс свои методы для дополнительной обработки данных или переопределять родительские методы, если это необходимо. Методы класса <span class="keyword">entity</span> позволяют получать, как <span class="keyword">row</span> так и <span class="keyword">rowset</span> примерно одинаковыми способами:</p>
<ul class="article_list">
    <li><b>getRowById</b> - получить row по id (для rowset нет аналога). id может передаваться как скалярное значение или как массив, если ключевых полей несколько.</li>
    <li><b>getRowByParam/getRowsetByParam</b> - получить row/rowset по значениям одного или нескольких полей. Если при получении row, параметры указаны таким образом, что MySQL вернет несколько строк, то выбирается первая строка, либо Вы указываете "сдвиг" и получаете нужную строку из результата. Кроме того, при вызове этого метода Вы можете указать порядок сортировки. Для rowset можно указать limit - максимальное количество строк, которое мы хотим получить в результате.</li>
    <li><b>getRowByKey/getRowsetByKey</b> - методы во многом идентичны предыдущим, с той лишь разницей, что в предыдущем случае запрос к БД генерируется автоматически, а здесь мы пишем его сами. Условно говоря, название файла с SQL-запросом и будет тем самым "ключом", который нам необходимо передавать в эти методы. Остальные параметры (лимит, сдвиг, сортировка) используются как и в предыдущем случае. Тексты SQL-запросов условно разделяются на две группы: с классическими плэсхолдерами (в виде символа <b>?</b>) и с "<b>условными комметариями</b>" (более подробно эта тема будет раскрыта в отдельной статье). Следует отметить, благодаря "ручному" написанию SQL-запроса здесь в <span class="keyword">row</span> попадают не только поля таблицы, указанной в <span class="keyword">entity</span>, но и данные из других связанных таблиц. Во избежание ошибок, для "чужих" данных рекомендуется присваивать алиасы, начинающиеся с двух символов подчеркивания "__".</li>
    <li><b>getRowByQuery/getRowsetByQuery</b> - методы аналогичны предыдущим, с той лиш разницей, что вместо "ключа запроса" здесь передается сам "текст SQL-запроса". Сортировка здесь отдельно не указывается, т.к. она может быть указана в самом SQL-запросе. Эти методы следует использовать только в крайних случаях, когда по каким-либо причинам, использование <b>getRowByKey/getRowsetByKey</b> - невозможно.</li>
</ul>
<p>Для упрощения доступа к классам <span class="keyword">entity/row</span> существуют специальные функции:</p>
<ul class="article_list">
    <li><b>ge</b> (сокращение от "get entity") - возвращает объект класса <span class="keyword">entity</span>. Первым аргументом здесь передается <b>namespace</b>, неодходимого класса. Можно передавать полное значение <b>namespace</b>, но для удобочитаемости префикс "project/model" лучше опускать. Например, вместо "<b>project\model\common\test_primary</b>" лучше писать "<b>common\test_primary</b>". Функция имеет еще два дополнительных параметра, которые будут описаны отдельно.</li>
    <li><b>gr</b> (сокращение от "get row") - возвращает объект класса <span class="keyword">row</span>. Первый аргумент этой функции, как и в предыдущем случае "сокращенный namespace". Второй, необязательный параметр, это id записи в БД. Если id не указан - создается "пустая row", данные которой используются для вставки новой записи в БД (<i>INSERT</i>). Остальные параметры функции будут описаны отдельно.</li>
</ul>
<p>Объект класса <span class="keyword">rowset</span> является итератором, т.е. его можно использовать в циклах. Значения полей из объекта <span class="keyword">row</span> могут извлекаться несколькими способами: <b>$oRow->field_name, $oRow['field_name'], $oRow->get('field_name')</b>. Первый способ самый простой, но его можно использовать только если имя поля в БД содержит допустимые символы. Во втором и третьем способе поля могут называться как угодно. Третий способ позволяет еще указывать значение по умолчанию и запретить exception, если идет обращение к не существующему полю. Получить значения всех полей, как уже писалось выше можно с помощью метода toArray. Для задания новых значений в <span class="keyword">row</span>, соответсвенно существуют следующие способы: <b>$oRow->field_name = ..., $oRow['field_name'] = ..., $oRow->set('field_name', ...)</b>. Данные заданные в <span class="keyword">row</span> не сразу попадают в БД. Для того чтобы это произошло, необходимо у этого объекта вызвать метод <b>save</b> (<i>Для row, полученной без загрузки произойдет INSERT, а для "загруженной row" - произойдет UPDATE</i>).</p>
