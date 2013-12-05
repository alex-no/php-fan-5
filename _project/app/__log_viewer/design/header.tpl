{$menu}<form action="#" method="get" id="select_param">
{if $isDelete}
    <button type="button" id="delete_rec">Delete selected</button><button type="button" id="inv_select">Inv select</button>
{/if}
Select date: <select name="date">
    {foreach key=dt item=keys from=$aDate}
        {foreach item=key from=$keys}
            <option value="{$dt}_{$key}"{if $dt == $aCurSel[0] && $key == $aCurSel[1]} selected="selected"{/if}>{$dt}{*$dt|dateM2L*} ({$key})</option>
        {/foreach}
    {/foreach}
</select>
<span class="cbox"><label for="update">Auto update: </label><input type="checkbox" name="update" checked="checked" id="update" /></span>
<span class="cbox"><label for="group_idt">Group the identical: </label><input type="checkbox" name="group_idt" checked="checked" id="group_idt" /></span>
</form>