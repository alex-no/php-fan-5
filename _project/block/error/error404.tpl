{if empty($sLng) || $sLng=='en'}
    <h1>Error 404</h1>
    <p>
        Requested page isn't found.
    </p>
    <p>
        Please use navigation for further action. Or click <a href="{uri '/'}">here</a>
        to go to the home page of the CoPAYCo-portal.
    </p>
{elseif $sLng=='ru'}
    <h1>Ошибка 404</h1>
    <p>
        Запрашиваемая страница отсутствует.
    </p>
    <p>
        Воспользуйтесь навигацией для совершения дальнейших действий. Или <a href="{uri '/'}">кликните здесь</a>,
        чтобы перейти на главную страницу портала CoPAYCo.
    </p>
{elseif $sLng=='ua'}
    <h1>Error 404</h1>
    <p>
        Запрошувана сторінка відсутня.
    </p>
    <p>
        Скористуйтесь навігацією для учинення подальший дій. Або клікніть <a href="{uri '/'}">тут</a>
        щоб перейти на головну сторінку порталу CoPAYCo.
    </p>
{/if}
