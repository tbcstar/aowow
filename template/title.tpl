{include file='header.tpl'}

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">
{if !empty($announcements)}
    {foreach from=$announcements item=item}
        {include file='bricks/announcement.tpl' an=$item}
    {/foreach}
{/if}

            <script type="text/javascript">
{include file='bricks/community.tpl'}
                var g_pageInfo = {ldelim}type: {$page.type}, typeId: {$page.typeId}, name: '{$data.page.name|escape:"quotes"}'{rdelim};
                g_initPath({$page.path});
            </script>

{include file='bricks/infobox.tpl'}

            <div class="text">
                <a href="javascript:;" id="open-links-button" class="button-red" onclick="this.blur(); Links.show({ldelim} type: 11, typeId: {$data.page.id} {rdelim});">
                    <em><b><i>{$lang.link}</i></b><span>{$lang.link}</span></em>
                </a>
                <a href="http://old.wowhead.com/?{$query[0]}={$query[1]}" class="button-red"><em><b><i>Wowhead</i></b><span>Wowhead</span></em></a>
                <h1 class="h1-icon">{if isset($data.page.expansion)}<span class="{$data.page.expansion}-icon-right">{$data.page.name}</span>{else}{$data.page.name}{/if}</h1>
                <h2 class="clear">{$lang.related}</h2>
            </div>

            <div id="tabs-generic"></div>
            <div id="listview-generic" class="listview"></div>
            <script type="text/javascript">//<![CDATA[
            var tabsRelated = new Tabs({ldelim}parent: ge('tabs-generic'){rdelim});
            {if isset($data.page.acvReward)}   {include file='bricks/achievement_table.tpl' data=$data.page.acvReward   params=$data.page.acvParams  } {/if}
            {if isset($data.page.questReward)} {include file='bricks/quest_table.tpl'       data=$data.page.questReward params=$data.page.questParams} {/if}
            new Listview({ldelim}template: 'comment', id: 'comments', name: LANG.tab_comments, tabs: tabsRelated, parent: 'listview-generic', data: lv_comments{rdelim});
            new Listview({ldelim}template: 'screenshot', id: 'screenshots', name: LANG.tab_screenshots, tabs: tabsRelated, parent: 'listview-generic', data: lv_screenshots{rdelim});
            if (lv_videos.length || (g_user && g_user.roles & (U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO)))
                new Listview({ldelim}template: 'video', id: 'videos', name: LANG.tab_videos, tabs: tabsRelated, parent: 'listview-generic', data: lv_videos{rdelim});
            tabsRelated.flush();
            //]]></script>

        {include file='bricks/contribute.tpl'}

        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}