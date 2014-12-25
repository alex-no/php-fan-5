{if !empty($showIfOnePage) || !empty($iPageQtt) && $iPageQtt > 1}
    {if $tplType=="references"}
        <div class="pager pgRef">
            {$oBlock->getEmbeddedForm()}
            <div>
                {foreach from=$aPages item=pageGr name=pageGrp}
                    {foreach from=$pageGr item=iPage}
                        {if $iPage != $iCurrentPage}
                            <a href="{$oBlock->getPageUri($iPage)}">{$iPage}</a>
                        {else}
                            <b>{$iPage}</b>
                        {/if}
                    {/foreach}
                    {if !$this->isLast('pageGrp')}<span>...</span>{/if}
                {/foreach}
            </div>
        </div>
    {/if}

    {if $tplType=="referencesNL"}
        <div class="pager pgRef">
            {$oBlock->getEmbeddedForm()}
            <div>
                {if $iCurrentPage > 1}
                    <a href="{$oBlock->getPageUri(1)}">&lt;&lt;</a>
                    <a href={$oBlock->getPageUri($iCurrentPage-1)}>&lt;</a>
                {else}
                    <i>&lt;&lt;</i>
                    <i>&lt;</i>
                {/if}
                    {foreach from=$aPagesNL item=iPage}
                        {if $iPage != $iCurrentPage}
                            <a href="{$oBlock->getPageUri($iPage)}">{$iPage}</a>
                        {else}
                            <b>{$iPage}</b>
                        {/if}
                    {/foreach}
                {if $iCurrentPage < $iPageQtt}
                    <a href="{$oBlock->getPageUri($iCurrentPage+1)}">&gt;</a>
                    <a href="{$oBlock->getPageUri($iPageQtt)}">&gt;&gt;</a>
                {else}
                    <i>&gt;</i>
                    <i>&gt;&gt;</i>
                {/if}
            </div>
        </div>
    {/if}

    {if $tplType=="images"}
        <div class="pager pgImg">
            {$oBlock->getEmbeddedForm()}
            <div>
                {foreach from=$aPages item=pageGr name=pageGrp}
                    {foreach from=$pageGr item=iPage}
                        {if $iPage!=$iCurrentPage}
                            <a href="{$oBlock->getPageUri($iPage)}"><img src="/image/pager/{$iPage}.gif" alt="{$iPage}" /></a>
                        {else}
                            <img src="/image/pager/c{$iPage}.gif" alt="{$iPage}" />
                        {/if}
                    {/foreach}
                    {if !$this->isLast('pageGrp')}<span>...</span>{/if}
                {/foreach}
            </div>
        </div>
    {/if}

    {if $tplType=="prev_next"}
        <div class="pager pgPnRef">
            {$oBlock->getEmbeddedForm()}
            <div>
                {if 1!=$iCurrentPage}
                    <a href="{$oBlock->getPageUri($iCurrentPage - 1)}" class="pgPrev">&lt;&lt; prev</a>
                {/if}
                {if $iPageQtt!=$iCurrentPage}
                    <a href="{$oBlock->getPageUri($iCurrentPage + 1)}" class="pgNext">next &gt;&gt;</a>
                {/if}
            </div>
        </div>
    {/if}

    {if $tplType=="prev_next_ru"}
        <div class="pager pgPnRef">
            {$oBlock->getEmbeddedForm()}
            <div>
                {if 1!=$iCurrentPage}
                    <a href="{$oBlock->getPageUri($iCurrentPage-1)}" class="pgPrev">&lt;&lt; Предыдущая</a>
                {/if}
                {if $iPageQtt!=$iCurrentPage}
                    <a href="{$oBlock->getPageUri($iCurrentPage+1)}" class="pgNext">Следующая &gt;&gt;</a>
                {/if}
            </div>
        </div>
    {/if}

    {if $tplType=="prev_next_ml"}
        <div class="pager pgPnRef">
            {$oBlock->getEmbeddedForm()}
            <div>
                {if 1!=$iCurrentPage}
                    <a href="{$oBlock->getPageUri($iCurrentPage-1)}" class="pgPrev">&lt;&lt; {msg LINK_PREV}</a>
                {/if}
                {if $iPageQtt!=$iCurrentPage}
                    <a href="{$oBlock->getPageUri($iCurrentPage+1)}" class="pgNext">{msg LINK_NEXT} &gt;&gt;</a>
                {/if}
            </div>
        </div>
    {/if}

    {if $tplType == "prev_nextimage"}
        <div class="pager pgPnImg">
            {$oBlock->getEmbeddedForm()}
            <div>
                {if 1!=$iCurrentPage}
                    <a href="{$oBlock->getPageUri($iCurrentPage - 1)}" class="pgPrev"><img src="/image/pager/prev.gif" alt="previous page" /></a>
                {/if}
                {if $iPageQtt!=$iCurrentPage}
                    <a href="{$oBlock->getPageUri($iCurrentPage + 1)}" class="pgNext"><img src="/image/pager/next.gif" alt="next page" /></a>
                {/if}
            </div>
        </div>
    {/if}

{/if}