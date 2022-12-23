<div class="container">
		<div class="row">
				<div class="col-xs-12">
						<h1>Login</h1>
						{$ERRORMESSAGE}
				</div>
		</div>
								
		<div class="row">
				<div class="col-sm-6 col-sm-offset-3">
						<form action="{$SITE_URL}?action=process" class="post-password-form form-horizontal" method="post">
								<div class="row">
										<div class="col-xs-12">
												<p>Bitte loggen Sie sich ein:</p>
										</div>
								</div>
								
								<div class="row">
										<div class="col-xs-12">
												<div class="form-group">
														<label for="login_name" class="col-sm-2 control-label">E-Mail:</label>
														<div class="col-sm-10">
																<input name="login_name" id="login_name" type="text" class="form-control" placeholder="E-Mail" />
														</div>
												</div>
																				
												<div class="form-group">
														<label for="login_password" class="col-sm-2 control-label">Passwort:</label>
														<div class="col-sm-10">
																<input name="login_password" id="login_password" type="password" class="form-control" placeholder="Passwort" />
														</div>
												</div>
												
												<div class="form-group">
														<div class="col-sm-2">&nbsp;</div>
														<div class="col-sm-10 text-left">
																<button type="submit" class="btn btn-primary">Prüfen</button>
														</div>
												</div>
										</div>
								</div>
						</form>
				</div>
		</div>
</div>