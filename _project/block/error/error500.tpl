{if empty($sLng) || $sLng=='en'}
    <h1>Error 500</h1>
    <p>
        Access is temporarily impossible. Try again after some time.
    </p>
    <p>
        Please use navigation for further action. Or click <a href="{$sHomeUri}">here</a>
        to go to the home page.
    </p>
{elseif $sLng=='ru'}
    <h1>Ошибка 500</h1>
    <p>
        Доступ временно невозможен. Попробуйте еще раз через некоторое время.
    </p>
    <p>
        Воспользуйтесь навигацией для совершения дальнейших действий. Или кликните <a href="{$sHomeUri}">здесь</a>
        чтобы перейти на главную страницу.
    </p>
{elseif $sLng=='ua'}
    <h1>Помилка 500</h1>
    <p>
        Доступ тимчасово неможливий. Спробуйте ще раз через деякий час.
    </p>
    <p>
        Скористуйтесь навігацією для учинення подальший дій. Або клікніть <a href="{$sHomeUri}">тут </a>
        щоб перейти на головну сторінку.
    </p>
{/if}
