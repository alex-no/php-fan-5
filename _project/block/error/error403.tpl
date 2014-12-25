{if empty($sLng) || $sLng=='en'}
    <h1>Error 403</h1>
    <p>
        Access denied. The operation is available only to registered users.
    </p>
    <p>
        Please use navigation for further action. Or click <a href="{uri '/'}">here</a>
        to go to the home page of the CoPAYCo-portal.
    </p>
{elseif $sLng=='ru'}
    <h1>Ошибка 403</h1>
    <p>
        Доступ закрыт. Операция доступна только для зарегистрированных пользователей.
    </p>
    <p>
        Воспользуйтесь навигацией для совершения дальнейших действий. Или кликните <a href="{uri '/'}">здесь</a>
        чтобы перейти на главную страницу портала CoPAYCo.
    </p>
{elseif $sLng=='ua'}
    <h1>Помилка 403</h1>
    <p>
        Доступ закритий. Операція доступна тільки для зареєстрованих користувачів.
    </p>
    <p>
        Скористуйтесь навігацією для учинення подальший дій. Або клікніть  <a href="{uri '/'}">тут</a>
        щоб перейти на головну сторінку порталу CoPAYCo.
    </p>
{/if}
