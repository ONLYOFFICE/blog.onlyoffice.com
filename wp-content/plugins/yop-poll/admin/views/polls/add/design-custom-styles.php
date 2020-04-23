<div class="row">
	<div class="col-md-12">
		&nbsp;
	</div>
</div>
<div class="row skins-no-template" style="margin-top: 30px;">
	<div class="col-md-12 text-center">
		<p>
			<h4>
				<?php
				_e( 'You need to select a template first to be able to customize it', 'yop-poll' );
				?>
			</h4>
		</p>
		<p style="margin-top: 30px;">
			<h4>
				<?php
				_e( 'You can select a template <a href="#" class="custom-style-select-template">here</a>', 'yop-poll' );
				?>
			</h4>
		</p>
	</div>
</div>
<div class="row skins-no-skin hide" style="margin-top: 30px;">
	<div class="col-md-12 text-center">
		<p>
			<h4>
				<?php
				_e( 'You need to select a skin to be able to customize it', 'yop-poll' );
				?>
			</h4>
		</p>
		<p style="margin-top: 30px;">
			<h4>
				<?php
				_e( 'You can select a skin <a href="#" class="custom-style-select-skin">here</a>', 'yop-poll' );
				?>
			</h4>
		</p>
	</div>
</div>
<div class="row skin-custom-style hide">
	<div class="col-md-12">
		<div class="panel-group" id="poll-style-accordion" role="tablist" aria-multiselectable="true">
			<div class="panel panel-default poll-style-settings">
				<div class="panel-heading poll-style-header" role="tab" id="style-poll-container-header">
					<h4 class="panel-title">
						<a role="button" data-toggle="collapse" data-parent="#poll-style-accordion" href="#style-poll-container-content" aria-expanded="true" aria-controls="style-poll-container-content">
							Poll Container
						</a>
					</h4>
				</div>
				<div id="style-poll-container-content" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="style-poll-container-header">
					<div class="panel-body">
						<div class="form-horizontal">
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Background color', 'yop-poll' );?>
								</div>
								<div class="col-md-10 colorpicker-component">
									<input type="text" name="poll[background-color]" value="#ffffff" class="form-control poll-background-color" style="width:100%"/>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Border Thickness', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" name="poll[border-size]" value="1" class="form-control poll-border-size" />
										<div class="input-group-addon">px</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Border color', 'yop-poll' );?>
								</div>
								<div class="col-md-10 colorpicker-component">
									<input type="text" name="poll[border-color]" value="#000000" class="form-control poll-border-color" />
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Border Radius', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<div class="input-group">
						                <input type="text" name="poll[border-radius]" value="5" class="form-control poll-border-radius" />
						                <span class="input-group-addon">px</span>
						            </div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Padding Left/Right', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" name="poll[padding-left-right]" value="10" class="form-control poll-padding-left-right" />
										<span class="input-group-addon">px</span>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Padding Top/Bottom', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" name="poll[padding-top-bottom]" value="10" class="form-control poll-padding-top-bottom" />
										<span class="input-group-addon">px</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="panel panel-default questions-style-settings">
				<div class="panel-heading poll-style-header" role="tab" id="style-questions-container-header">
					<h4 class="panel-title">
						<a role="button" data-toggle="collapse" data-parent="#poll-style-accordion" href="#style-questions-container-content" aria-expanded="true" aria-controls="style-questions-container-content">
							Questions
						</a>
					</h4>
				</div>
				<div id="style-questions-container-content" class="panel-collapse collapse" role="tabpanel" aria-labelledby="style-questions-container-header">
					<div class="panel-body">
						<div class="form-horizontal">
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Text color', 'yop-poll' );?>
								</div>
								<div class="col-md-10 colorpicker-component">
									<input type="text" name="questions[text-color]" value="#000" class="form-control questions-text-color" />
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Text size', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" name="questions[text-size]" value="16" class="form-control questions-text-size" />
										<div class="input-group-addon">px</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Text Weight', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<select class="questions-text-weight admin-select" style="width:100%">
						                <option value="normal" selected ><?php _e( 'Normal', 'yop-poll' );?></option>
						                <option value="bold"><?php _e( 'Bold', 'yop-poll' );?></option>
						            </select>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Text Align', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<select class="questions-text-align admin-select" style="width:100%">
						                <option value="left"><?php _e( 'Left', 'yop-poll' );?></option>
						                <option value="center" selected><?php _e( 'Center', 'yop-poll' );?></option>
										<option value="right"><?php _e( 'Right', 'yop-poll' );?></option>
						            </select>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="panel panel-default answers-style-settings">
				<div class="panel-heading poll-style-header" role="tab" id="style-answers-container-header">
					<h4 class="panel-title">
						<a role="button" data-toggle="collapse" data-parent="#poll-style-accordion" href="#style-answers-container-content" aria-expanded="true" aria-controls="style-answers-container-content">
							Answers
						</a>
					</h4>
				</div>
				<div id="style-answers-container-content" class="panel-collapse collapse" role="tabpanel" aria-labelledby="style-answers-container-header">
					<div class="panel-body">
						<div class="form-horizontal">
							<div class="form-group hide">
								<div class="col-md-2 field-caption">
									<?php _e( 'Skin', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<select class="answers-skin admin-select" style="width:100%">
						                <option value="minimal"><?php _e( 'Minimal', 'yop-poll' );?></option>
						                <option value="square"><?php _e( 'Square', 'yop-poll' );?></option>
						                <option value="flat"><?php _e( 'Flat', 'yop-poll' );?></option>
						            </select>
								</div>
							</div>
							<div class="form-group hide">
								<div class="col-md-2 field-caption">
									<?php _e( 'Color Scheme', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<ul class="color-scheme">
						                <li class="active" title="Black" data-id="black"></li>
						                <li class="red" title="Red" data-id="red"></li>
						                <li class="green" title="Green" data-id="green"></li>
						                <li class="blue" title="Blue" data-id="blue"></li>
						                <li class="aero" title="Aero" data-id="aero"></li>
						                <li class="grey" title="Grey" data-id="grey"></li>
						                <li class="orange" title="Orange" data-id="orange"></li>
						                <li class="yellow" title="Yellow" data-id="yellow"></li>
						                <li class="pink" title="Pink" data-id="pink"></li>
						                <li class="purple" title="Purple" data-id="purple"></li>
					                </ul>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Padding Left/Right', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" value="0" class="form-control answers-padding-left-right" name="answers[padding-left-right]" />
										<div class="input-group-addon">px</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Padding Top/Bottom', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" value="0" class="form-control answers-padding-top-bottom" value="answers[padding-top-bottom]" />
										<div class="input-group-addon">px</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Text color', 'yop-poll' );?>
								</div>
								<div class="col-md-10 colorpicker-component">
									<input type="text" value="#000" class="form-control answers-text-color" name="answers[text-color]" />
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Text size', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" name="answers[text-size]" value="14" class="form-control answers-text-size" />
										<div class="input-group-addon">px</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Text Weight', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<select class="answers-text-weight admin-select" style="width:100%">
						                <option value="normal" selected><?php _e( 'Normal', 'yop-poll' );?></option>
						                <option value="bold"><?php _e( 'Bold', 'yop-poll' );?></option>
						            </select>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="panel panel-default buttons-style-settings">
				<div class="panel-heading poll-style-header" role="tab" id="style-buttons-container-header">
					<h4 class="panel-title">
						<a role="button" data-toggle="collapse" data-parent="#poll-style-accordion" href="#style-buttons-container-content" aria-expanded="true" aria-controls="style-buttons-container-content">
							Buttons
						</a>
					</h4>
				</div>
				<div id="style-buttons-container-content" class="panel-collapse collapse" role="tabpanel" aria-labelledby="style-buttons-container-header">
					<div class="panel-body">
						<div class="form-horizontal">
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Background color', 'yop-poll' );?>
								</div>
								<div class="col-md-10 colorpicker-component">
									<input type="text" value="#ffffff" class="form-control buttons-background-color" name="buttons[background-color]" />
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Border Thickness', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" value="1" class="form-control buttons-border-size" name="buttons[border-size]" />
										<div class="input-group-addon">px</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Border color', 'yop-poll' );?>
								</div>
								<div class="col-md-10 colorpicker-component">
									<input type="text" value="#000" class="form-control buttons-border-color" name="buttons[border-color]" />
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Border Radius', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" value="0" class="form-control buttons-border-radius" name="buttons[border-radius]" />
										<div class="input-group-addon">px</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Padding Left/Right', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" value="10" class="form-control buttons-padding-left-right" name="buttons[padding-left-right]" />
										<div class="input-group-addon">px</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Padding Top/Bottom', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" value="5" class="form-control buttons-padding-top-bottom" name="buttons[padding-top-bottom]" />
										<div class="input-group-addon">px</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Text color', 'yop-poll' );?>
								</div>
								<div class="col-md-10 colorpicker-component">
									<input type="text" value="#000" class="form-control buttons-text-color" name="buttons[text-color]" />
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Text size', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" name="buttons[text-size]" value="14" class="form-control buttons-text-size" name="buttons[text-size]" />
										<div class="input-group-addon">px</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Text Weight', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<select class="buttons-text-weight admin-select" style="width:100%">
						                <option value="normal" selected><?php _e( 'Normal', 'yop-poll' );?></option>
						                <option value="bold"><?php _e( 'Bold', 'yop-poll' );?></option>
						            </select>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="panel panel-default errors-style-settings">
				<div class="panel-heading poll-style-header" role="tab" id="style-errors-container-header">
					<h4 class="panel-title">
						<a role="button" data-toggle="collapse" data-parent="#poll-style-accordion" href="#style-errors-container-content" aria-expanded="true" aria-controls="style-errors-container-content">
							<?php _e( 'Messages', 'yop-poll' );?>
						</a>
					</h4>
				</div>
				<div id="style-errors-container-content" class="panel-collapse collapse" role="tabpanel" aria-labelledby="style-errors-container-header">
					<div class="panel-body">
						<div class="form-horizontal">
						<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Border color for success', 'yop-poll' );?>
								</div>
								<div class="col-md-10 colorpicker-component">
									<input type="text" value="#008000" class="form-control errors-border-left-color-for-success" />
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Border color for error', 'yop-poll' );?>
								</div>
								<div class="col-md-10 colorpicker-component">
									<input type="text" value="#ff0000" class="form-control errors-border-left-color-for-error" />
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Border Left Thickness', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" value="10" class="form-control errors-border-left-size" />
										<div class="input-group-addon">px</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Padding Top/Bottom', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" value="0" class="form-control errors-padding-top-bottom" />
										<div class="input-group-addon">px</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Text color', 'yop-poll' );?>
								</div>
								<div class="col-md-10 colorpicker-component">
									<input type="text" value="#000" class="form-control errors-text-color" />
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Text size', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" name="errors[text-size]" value="14" class="form-control errors-text-size" />
										<div class="input-group-addon">px</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Text Weight', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<select class="errors-text-weight admin-select" style="width:100%">
						                <option value="normal" selected><?php _e( 'Normal', 'yop-poll' );?></option>
						                <option value="bold"><?php _e( 'Bold', 'yop-poll' );?></option>
						            </select>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="panel panel-default custom-code-settings">
				<div class="panel-heading poll-style-header" role="tab" id="custom-styles-custom-code-container-header">
					<h4 class="panel-title">
						<a role="button" data-toggle="collapse" data-parent="#poll-style-accordion" href="#custom-styles-custom-code-container-content" aria-expanded="true" aria-controls="style-errors-container-content">
							<?php _e( 'Advanced', 'yop-poll' );?>
						</a>
					</h4>
				</div>
				<div id="custom-styles-custom-code-container-content" class="panel-collapse collapse" role="tabpanel" aria-labelledby="custom-styles-custom-code-container-header">
					<div class="panel-body">
						<div class="form-group">
							<label for="">Custom CSS</label>
							<textarea id="custom-css" class="form-control custom-styles-custom-css" rows="15" name="custom[css]"></textarea>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		&nbsp;
	</div>
</div>