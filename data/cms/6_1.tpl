<div class="container-fluid{if $SECOND_CHANCE == 1} mv-second-chance-main-container{/if}" id="card-game-screen">
		<div class="row row-alternate">
				{foreach from=$CARDS item=card name="card_loop"}
						{if $smarty.foreach.card_loop.index is div by 8 || $smarty.foreach.card_loop.index == 0}
								</div>
								<div class="row {cycle values="row-alternate,row-long-alternate"}">
						{/if}
								{if $card.card_type == 'image'}
										<div class="col-md-offset-2 col-md-1 card" id="card-image-{$card.card_data.id}" data-attr-card-id="{$card.card_data.id}" data-attr-wrong-animation="{$card.card_data.wrong_animations[1].filename_with_path}" data-attr-image="{if $SECOND_CHANCE == 1}data/templates/blitz2016/images/bg_wall.png{else}data/images/cardcovers/{$CARD_COLORS[$smarty.foreach.card_loop.index]}.jpg{/if}" data-attr-animation="{$card.card_data.animations[1].filename_with_path}" data-attr-card-type="image" data-attr-card-id-right-answer="{$card.card_data.right_answer}">
												<div class="card_border_container">
														<img src="{if $SECOND_CHANCE == 1}data/templates/blitz2016/images/bg_wall.png{else}data/images/cardcovers/{$CARD_COLORS[$smarty.foreach.card_loop.index]}.jpg{/if}" class="img-responsive" data-attr-test="{$CARD_COLORS[$smarty.foreach.card_loop.index]}.jpg" />
														<div class="image-card-highlite-container">
																<div class="image-card-highlite-inner-container"></div>
														</div>
												</div>
										</div>
								{else}
										<div class="col-md-offset-2 col-md-1 card" id="card-text-{$card.card_data.id}" data-attr-card-id="{$card.card_data.id}" data-attr-card-type="text" data-attr-card-id-right-answer="{$card.card_data.right_answer}" data-attr-image="{if $SECOND_CHANCE == 1}data/templates/blitz2016/images/bg_wall.png{else}data/images/cardcovers/{$CARD_COLORS[$smarty.foreach.card_loop.index]}.jpg{/if}" data-attr-image-show="{$card.card_data.images[3].filename_with_path}">
												<div class="card_border_container">
														<div class="card_text" {if $SECOND_CHANCE != 1}style="border-color: {$COLOR_VALUES[$CARD_COLORS[$smarty.foreach.card_loop.index]]}.jpg{else}style="border-color: #292929;"{/if}">
																<img src="{if $SECOND_CHANCE == 1}data/templates/blitz2016/images/bg_wall.png{else}data/images/cardcovers/{$CARD_COLORS[$smarty.foreach.card_loop.index]}.jpg{/if}" class="img-responsive" />
																<div class="card_text_info">
																		<div class="the-text">
																				<p>{$card.card_data.titles[0].title1}</p>
																		</div>
																</div>
														</div>
														
												</div>
										</div>
								{/if}
				{/foreach}
		</div>
</div>

<input type="hidden" id="final_action_wrong" value="{$FINAL_ACTION_WRONG}" />
<input type="hidden" id="final_action_right" value="{$FINAL_ACTION_RIGHT}" />
<input type="hidden" id="second_chance" value="{$SECOND_CHANCE}" />
<input type="hidden" id="total_card_count" value="{$TOTAL_CARD_COUNT}" />
<input type="hidden" id="final_action_end_game_with_information" value="{$FINAL_ACTION_END_GAME_WITH_INFORMATION}" />

<div id="load-waiter"><p>loading<span id="animate_dottes"></span></p></div>

<style>
		#animate_dottes { position: fixed; color: #FFF; }
</style>

{literal}
<script>
		$(function() {
				var dottes = 0;
				var mv_animated_dottes_timeout;
				
				function animate_dottes() {
						var dottes_text = '';
						
						dottes++;
						
						for(var i = 0; i < dottes; i++) {
								dottes_text += '.';
						}
						
						if(dottes == 3) {
								dottes = -1;
						}
					
						$('#animate_dottes').html(dottes_text);
						mv_animated_dottes_timeout = setTimeout(function() { animate_dottes(); }, 300);
				}
			
				mv_animated_dottes_timeout = setTimeout(function() { animate_dottes(); }, 300);
		});
		
		var mv_preload_images = [{/literal}{strip}
				{foreach from=$CARDS item=card name=image_preload_loop}
						{* <!-- load images --> *}
						{foreach from=$card.card_data.images item=image name=image_loop}
								{if $image != ''}
										{if $smarty.foreach.image_preload_loop.iteration != 1 || $smarty.foreach.image_loop.iteration != 1}, {/if}'{$image.filename_with_path}'
								{/if}
						{/foreach}
						
						
						{* <!-- load animations --> *}
						{foreach from=$card.card_data.animations item=image name=image_loop_animations}
								{if $image != ''}
										, '{$image.filename_with_path}'
								{/if}
						{/foreach}
						
						 {foreach from=$card.card_data.wrong_animations item=image name=image_loop_wrong_animations}
								{if $image != ''}
										, '{$image.filename_with_path}'
								{/if}
						{/foreach}
				{/foreach}{/strip}{literal}];
</script>

<style>
		html, body { height: 100%; }
</style>
{/literal}

<div id="global-restart-button"><a href="{$SITE_URL}">restart</a></div>
{literal}
<style>
		#global-restart-button { position: absolute; right: 5%; bottom: 8px; }
		#global-restart-button a { color: #FFF; }
</style>
{/literal}