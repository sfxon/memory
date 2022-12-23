<div class="fix-button-to-bottom" id="start-intro-page">
		<p class="text-left mv-type-text"></p>
														
		{literal}
				<script>
						function mv_show_button() {
								$('#mv-button-bottom').show();
						}
						
						$(function(){
								$(".mv-type-text").typed({
										strings: ["{/literal}{$smarty.const.MEMORY_TEXT_9}{if isset($smarty.session.name) && !empty($smarty.session.name)}, {$smarty.session.name}{/if}{$smarty.const.MEMORY_TEXT_10}<br />{$smarty.const.MEMORY_TEXT_11}{literal}"],
										typeSpeed: 0,
										callback: function() {
												mv_show_button();
										}
								});
						});
				</script>
		{/literal}
		
		{* <!--
				<input type="hidden" value="{$smarty.const.MEMORY_TEXT_9}{if isset($smarty.session.name) && !empty($smarty.session.name)}, {$smarty.session.name}{/if}{$smarty.const.MEMORY_TEXT_10}<br />{$smarty.const.MEMORY_TEXT_11}" />
		--> *}
		
		<a href="{$TEMPLATE_URL}game.html" id="mv-button-bottom"><button type="submit" class="mv-btn">{$smarty.const.MEMORY_TEXT_12}</button></a>
</div>

{literal}
		<style>
				html, body { min-height: 100%; height: 100%; }
				#start-intro-page { padding-top: 120px; margin-top: 0; position: relative; }
				.fix-button-to-bottom { position: relative; height: 100%; }
				#mv-button-bottom { display: none; text-align: center; }
				#index-teaser-image-hide-controls { text-align: left; padding-left: 20px; padding-top: 20px; }
				#start-intro-page { padding-top: 0; }
				#start-intro-page p { padding: 0 5%; font-size: 45px; padding-top: 0; width: 100%; }
		</style>
{/literal}

<div id="global-restart-button"><a href="{$SITE_URL}">restart</a></div>
{literal}
<style>
		#global-restart-button { position: absolute; right: 5%; bottom: 8px; }
		#global-restart-button a { color: #FFF; }
</style>
{/literal}