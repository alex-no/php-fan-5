<p>В этом примере мы получаем объект <span class="keyword">row</span> с помощью другого объекта <span class="keyword">row</span>. Для выполнения таких действий необходимо, чтобы таблица в БД (исходного row) имела <b>Foreign Key</b> с другой таблицей. При вызове метода указывается название поля связанной таблицы. Далее система сама определяет связь и возвращает объект <span class="keyword">row</span> из связанной таблицы.</p>