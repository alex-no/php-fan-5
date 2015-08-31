{if empty($sLng) || $sLng=='en'}
    <h1>Error 404</h1>
    <p>
        Requested page isn't found.
    </p>
    <p>
        Please use navigation for further action. Or click <a href="{$sHomeUri}">here</a>
        to go to the home page.
    </p>
{elseif $sLng=='ru'}
    <h1>Ошибка 404</h1>
    <p>
        Запрашиваемая страница отсутствует.
    </p>
    <p>
        Воспользуйтесь навигацией для совершения дальнейших действий. Или <a href="{$sHomeUri}">кликните здесь</a>,
        чтобы перейти на главную страницу.
    </p>
{elseif $sLng=='ua'}
    <h1>Error 404</h1>
    <p>
        Запрошувана сторінка відсутня.
    </p>
    <p>
        Скористуйтесь навігацією для учинення подальший дій. Або клікніть <a href="{$sHomeUri}">тут</a>
        щоб перейти на головну сторінку.
    </p>
{/if}
