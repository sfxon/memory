<div class="fix-button-to-bottom" id="start-intro-page">
		<p class="text-left mv-type-text"></p>
		{literal}
				<script>
						$(function(){
								$(".mv-type-text").typed({
										strings: ["{/literal}{$smarty.const.MEMORY_TEXT_15}{literal}"],
										typeSpeed: 0,
										callback: function() {
												$('#try_again_button').show();
										}
								});
						});
				</script>
		{/literal}
		
		{literal}
		<style>
				#try_again_button { display: none; }
		</style>
		{/literal}
	
		<div id="try_again_button">
				<a href="{$SITE_URL}"><button class="mv-btn">{$smarty.const.MEMORY_TEXT_16}</button></a>
		</div>

</div>

{literal}
		<style>
				html, body { min-height: 100%; height: 100%; }
				#start-intro-page { padding-top: 120px; margin-top: 0; position: relative; }
				.fix-button-to-bottom { position: relative; height: 100%; }
				#try_again_button { bottom: 180px; position: absolute; width: 100%; text-align: center; }
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