<div id="index-page" class="fix-button-to-bottom">
		<div id="index-teaser-image">
				<video controls autoplay loop>
						<source src="{$TEMPLATE_URL}data/videos/video3.mp4" type="video/mp4">
						Your browser does not support the video tag.
				</video> 
				<div id="index-teaser-image-hide-controls" style="position: absolute; bottom: 0; height: 50px; width: 100%; top: auto; background-color: #000;">{$smarty.const.MEMORY_TEXT_19}</div>
				<!-- <img src="{$TEMPLATE_URL}data/templates/{$TEMPLATE}/images/startscreen.gif" class="img-responsive"> -->
				{* <!-- <div id="index-teaser-image-text"><p>{$smarty.const.MEMORY_TEXT_1}</p></div> --> *}
		</div>
		<br />
		
		<a href="{$TEMPLATE_URL}start.html" id="mv-button-bottom"><button class="mv-btn">{$smarty.const.MEMORY_TEXT_2}</button></a>
</div>

{literal}
		<style>
				html, body { min-height: 100%; height: 100%; }
				#index-page { padding-top: 13%; }
				.fix-button-to-bottom { position: relative; height: 100%; }
				#mv-button-bottom { bottom: 180px; position: absolute; margin-left: -120px; }
				/*#mv-button-bottom { display: none; }*/
				#index-teaser-image-hide-controls { text-align: left; padding-left: 20px; padding-top: 20px; }
		</style>
		
		<script>
				function show_button() {
						$('#index-teaser-image-hide-controls').css('margin-left', (parseInt($('video').position().left)) + 'px');
						$('#index-teaser-image-hide-controls').show();
				}
				
				$(function() {
						setTimeout( function() { show_button(); }, 600);
				});
		</script>
{/literal}