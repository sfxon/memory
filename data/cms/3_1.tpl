<div class="fix-button-to-bottom" id="start-intro-page">
		<p class="text-left mv-type-text"></p>
						{literal}
								<script>
										$(function(){
												$(".mv-type-text").typed({
														strings: ["{/literal}{$smarty.const.MEMORY_TEXT_5}{literal}"],
														typeSpeed: 0,
														callback: function() {
																$('#mv-button-bottom').show();
																/*setTimeout(function() { restart_game(); }, 20000);*/
														}
												});
												
												
												function restart_game() {
														var url = "{/literal}{$SITE_URL}game.html?action=restart{literal}";
														window.location = url;
												}
										});
								</script>
						{/literal}
						
						{literal}
						<style>
								#try_again_button { display: none; }
						</style>
						{/literal}

		<a href="{$SITE_URL}game.html?action=restart" id="mv-button-bottom"><button class="mv-btn">{$smarty.const.MEMORY_TEXT_6}</button></a>
</div>

{literal}
		<style>
				html, body { min-height: 100%; height: 100%; }
				#start-intro-page { padding-top: 120px; margin-top: 0; position: relative; }
				.fix-button-to-bottom { position: relative; height: 100%; }
				#mv-button-bottom { display: none; }
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