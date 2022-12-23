<div class="container" id="enter-name-page">
		<div class="row">
				<div class="col-sm-6 col-sm-offset-3">
						<form action="{$TEMPLATE_URL}enter-name.html?action=process" id="enter-name-form" method="post">
								<div class="row">
										<div class="col-xs-12">
												<div class="form-group">
														<div class="col-sm-10">
																<input name="name" id="name" type="text" class="form-control" placeholder="{$smarty.const.MEMORY_TEXT_3}" autofocus />
														</div>
												</div>
												
												<div class="form-group">
														<div class="col-sm-2">&nbsp;</div>
														<div class="col-sm-10 text-left">
																<button type="submit" class="mv-btn">{$smarty.const.MEMORY_TEXT_4}</button>
														</div>
												</div>
										</div>
								</div>
						</form>
				</div>
		</div>
</div>