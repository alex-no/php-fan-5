<p>В данном примере мы попытемся сериализовать объект <span class="keyword">row</span>, полученный в первом примере и посмотрим как выглядит этот объект в сериализованном виде. Затем десереиализуем объект и попробуем получить из него данные. Десериализованный объект имеет все те-же функциональные возможности, что и объект созданный обычным способом. Все это означает что мы можем свободно сохранять такие объекты в сессию и потом как угодно ими манипулировать. Это позволяет экономить ресурсы web-сервера, при условии что в сессию сохраняюются те объекты, которые часто используются. Избыток таких объектов в сессии приведет к обратному результату - увеличению нагрузки. Однако, следует учитывать, что если данные в БД изменятся без использования такого объекта, тогда этот объект будет содержать устаревшую информацию.</p>
<p>Объекты <span class="keyword">rowset</span> можно так-же сериализовать аналогичным образом.</p>
