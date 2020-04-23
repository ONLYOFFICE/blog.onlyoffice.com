<div class="row">
	<div class="col-md-12">
		&nbsp;
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="panel-group" id="poll-style-accordion" role="tablist" aria-multiselectable="true">
			<div class="panel panel-default poll-style-settings">
				<div class="panel-heading poll-style-header" role="tab" id="style-poll-container-header">
					<h4 class="panel-title">
						<a role="button" data-toggle="collapse" data-parent="#poll-style-accordion" href="#style-poll-container-content" aria-expanded="true" aria-controls="style-poll-container-content">
							<?php _e( 'Poll Container', 'yop-poll' );?>
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
									<input type="text" name="poll[background-color]" value="<?php echo esc_html( $poll->meta_data['style']['poll']['backgroundColor'] );?>" class="form-control poll-background-color" style="width:100%"/>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Border Thickness', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" name="poll[border-size]" value="<?php echo esc_html( $poll->meta_data['style']['poll']['borderSize'] );?>" class="form-control poll-border-size" />
										<div class="input-group-addon">px</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Border color', 'yop-poll' );?>
								</div>
								<div class="col-md-10 colorpicker-component">
									<input type="text" name="poll[border-color]" value="<?php echo esc_html( $poll->meta_data['style']['poll']['borderColor'] );?>" class="form-control poll-border-color" />
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Border Radius', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<div class="input-group">
						                <input type="text" name="poll[border-radius]" value="<?php echo esc_html( $poll->meta_data['style']['poll']['borderRadius'] );?>" class="form-control poll-border-radius" />
						                <span class="input-group-addon">px</span>
						            </div>
								</div>
							</div>
							<?php
							if ( ( false === isset( $poll->meta_data['style']['poll']['paddingLeftRight'] ) ) || ( '' !== $poll->meta_data['style']['poll']['paddingLeftRight'] ) ) {
								$poll->meta_data['style']['poll']['paddingLeftRight'] = '0';
							}
							?>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Padding Left/Right', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" name="poll[padding-left-right]" value="<?php echo esc_html( $poll->meta_data['style']['poll']['paddingLeftRight'] );?>" class="form-control poll-padding-left-right" />
										<span class="input-group-addon">px</span>
									</div>
								</div>
							</div>
							<?php
							if ( ( false === isset( $poll->meta_data['style']['poll']['paddingTopBottom'] ) ) || ( '' !== $poll->meta_data['style']['poll']['paddingTopBottom'] ) ) {
								$poll->meta_data['style']['poll']['paddingTopBottom'] = '0';
							}
							?>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Padding Top/Bottom', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" name="poll[padding-top-bottom]" value="<?php echo esc_html( $poll->meta_data['style']['poll']['paddingTopBottom'] );?>" class="form-control poll-padding-top-bottom" />
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
							<?php _e( 'Questions', 'yop-poll' );?>
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
									<input type="text" name="questions[text-color]" value="<?php echo esc_html( $poll->meta_data['style']['questions']['textColor'] );?>" class="form-control questions-text-color" />
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Text size', 'yop-poll' );?>
								</div>
								<?php
								if ( true === in_array( $poll->meta_data['style']['questions']['textSize'], array( 'small', 'medium', 'large' ) ) ) {
									switch ( $poll->meta_data['style']['questions']['textSize'] ) {
										case 'small': {
											$poll->meta_data['style']['questions']['textSize'] = '12';
											break;
										}
										case 'medium': {
											$poll->meta_data['style']['questions']['textSize'] = '16';
											break;
										}
										case 'large': {
											$poll->meta_data['style']['questions']['textSize'] = '20';
											break;
										}
									}
								}
								?>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" name="questions[text-size]" value="<?php echo esc_html( $poll->meta_data['style']['questions']['textSize'] );?>" class="form-control questions-text-size" />
										<div class="input-group-addon">px</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Text Weight', 'yop-poll' );?>
								</div>
								<?php
								if ( ( true === isset( $poll->meta_data['style']['questions']['textWeight'] ) ) && ( '' !== $poll->meta_data['style']['questions']['textWeight'] ) ) {
									if ( 'normal' === $poll->meta_data['style']['questions']['textWeight'] ) {
										$questions_text_weight_normal = 'selected';
										$questions_text_weight_bold = '';
									} else {
										$questions_text_weight_normal = '';
										$questions_text_weight_bold = 'selected';
									}
								} else {
									$questions_text_weight_normal = 'selected';
									$questions_text_weight_bold = '';
								}
								?>
								<div class="col-md-10">
									<select class="questions-text-weight admin-select" style="width:100%">
						                <option value="normal" <?php echo $questions_text_weight_normal;?>><?php _e( 'Normal', 'yop-poll' );?></option>
						                <option value="bold" <?php echo $questions_text_weight_bold;?>><?php _e( 'Bold', 'yop-poll' );?></option>
						            </select>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Text Align', 'yop-poll' );?>
								</div>
								<?php
								if ( ( true === isset( $poll->meta_data['style']['questions']['textAlign'] ) ) && ( '' !== $poll->meta_data['style']['questions']['textAlign'] ) ) {
									switch ( $poll->meta_data['style']['questions']['textAlign'] ) {
										case 'left': {
											$questions_text_align_left = 'selected';
											$questions_text_align_center = '';
											$questions_text_align_right = '';
											break;
										}
										case 'center': {
											$questions_text_align_left = '';
											$questions_text_align_center = 'selected';
											$questions_text_align_right = '';
											break;
										}
										case 'right': {
											$questions_text_align_left = '';
											$questions_text_align_center = '';
											$questions_text_align_right = 'selected';
											break;
										}
										default: {
											$questions_text_align_left = 'selected';
											$questions_text_align_center = '';
											$questions_text_align_right = '';
											break;
										}
									}
								} else {
									$questions_text_align_left = 'selected';
									$questions_text_align_center = '';
									$questions_text_align_right = '';
								}
								?>
								<div class="col-md-10">
									<select class="questions-text-align admin-select" style="width:100%">
						                <option value="left" <?php echo $questions_text_align_left;?>><?php _e( 'Left', 'yop-poll' );?></option>
						                <option value="center" <?php echo $questions_text_align_center;?>><?php _e( 'Center', 'yop-poll' );?></option>
										<option value="right" <?php echo $questions_text_align_right;?>><?php _e( 'Right', 'yop-poll' );?></option>
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
							<?php _e( 'Answers', 'yop-poll' );?>
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
									<?php
									$answers_skin_minimal = '';
									$answers_skin_square = '';
									$answers_skin_flat = '';
						            switch ( $poll->meta_data['style']['answers']['skin'] ) {
										case 'minimal': {
											$answers_skin_minimal = 'selected';
											break;
										}
										case 'square': {
											$answers_skin_square = 'selected';
											break;
										}
										case 'flat': {
											$answers_skin_flat = 'selected';
											break;
										}
									}
									?>
									<select class="answers-skin admin-select" style="width:100%">
						                <option value="minimal" <?php echo $answers_skin_minimal;?>><?php _e( 'Minimal', 'yop-poll' );?></option>
						                <option value="square"<?php echo $answers_skin_square;?>><?php _e( 'Square', 'yop-poll' );?></option>
						                <option value="flat"<?php echo $answers_skin_flat;?>><?php _e( 'Flat', 'yop-poll' );?></option>
						            </select>
								</div>
							</div>
							<div class="form-group hide">
								<div class="col-md-2 field-caption">
									<?php _e( 'Color Scheme', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<?php
									$color_scheme_black = '';
									$color_scheme_red = '';
									$color_scheme_green = '';
									$color_scheme_blue = '';
									$color_scheme_aero = '';
									$color_scheme_grey = '';
									$color_scheme_orange = '';
									$color_scheme_yellow = '';
									$color_scheme_pink = '';
									$color_scheme_purple = '';
						            switch ( $poll->meta_data['style']['answers']['colorScheme'] ) {
										case 'black': {
											$color_scheme_black = 'active';
											break;
										}
										case 'red': {
											$color_scheme_red = 'active';
											break;
										}
										case 'green': {
											$color_scheme_green = 'active';
											break;
										}
										case 'blue': {
											$color_scheme_blue = 'active';
											break;
										}
										case 'aero': {
											$color_scheme_aero = 'active';
											break;
										}
										case 'grey': {
											$color_scheme_grey = 'active';
											break;
										}
										case 'orange': {
											$color_scheme_orange = 'active';
											break;
										}
										case 'yellow': {
											$color_scheme_yellow = 'active';
											break;
										}
										case 'pink': {
											$color_scheme_pink = 'active';
											break;
										}
										case 'purple': {
											$color_scheme_purple = 'active';
											break;
										}
										default: {
											$color_scheme_black = 'active';
											break;
										}
									}
									?>
									<ul class="color-scheme">
						                <li class="<?php echo $color_scheme_black;?>" title="Black" data-id="black"></li>
						                <li class="red <?php echo $color_scheme_red;?>" title="Red" data-id="red"></li>
						                <li class="green <?php echo $color_scheme_green;?>" title="Green" data-id="green"></li>
						                <li class="blue <?php echo $color_scheme_blue;?>" title="Blue" data-id="blue"></li>
						                <li class="aero <?php echo $color_scheme_aero;?>" title="Aero" data-id="aero"></li>
						                <li class="grey <?php echo $color_scheme_grey;?>" title="Grey" data-id="grey"></li>
						                <li class="orange <?php echo $color_scheme_orange;?>" title="Orange" data-id="orange"></li>
						                <li class="yellow <?php echo $color_scheme_yellow;?>" title="Yellow" data-id="yellow"></li>
						                <li class="pink <?php echo $color_scheme_pink;?>" title="Pink" data-id="pink"></li>
						                <li class="purple <?php echo $color_scheme_purple;?>" title="Purple" data-id="purple"></li>
					                </ul>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Padding Left/Right', 'yop-poll' );?>
								</div>
								<?php
								if ( ( false === isset( $poll->meta_data['style']['answers']['paddingLeftRight'] ) ) || ( '' === $poll->meta_data['style']['answers']['paddingLeftRight'] ) ) {
									$poll->meta_data['style']['answers']['paddingLeftRight'] = 0;
								}
								?>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" name="answers[padding-left-right]" value="<?php echo esc_html( $poll->meta_data['style']['answers']['paddingLeftRight'] );?>" class="form-control answers-padding-left-right" />
										<div class="input-group-addon">px</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Padding Top/Bottom', 'yop-poll' );?>
								</div>
								<?php
								if ( ( false === isset( $poll->meta_data['style']['answers']['paddingTopBottom'] ) ) || ( '' === $poll->meta_data['style']['answers']['paddingTopBottom'] ) ) {
									$poll->meta_data['style']['answers']['paddingTopBottom'] = 0;
								}
								?>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" name="answers[padding-top-bottom]" value="<?php echo esc_html( $poll->meta_data['style']['answers']['paddingTopBottom'] );?>" class="form-control answers-padding-top-bottom" />
										<div class="input-group-addon">px</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Text color', 'yop-poll' );?>
								</div>
								<div class="col-md-10 colorpicker-component">
									<input type="text" name="answers[text-color]" value="<?php echo esc_html( $poll->meta_data['style']['answers']['textColor'] );?>" class="form-control answers-text-color" />
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Text size', 'yop-poll' );?>
								</div>
								<?php
								if ( true === in_array( $poll->meta_data['style']['answers']['textSize'], array( 'small', 'medium', 'large' ) ) ) {
									switch ( $poll->meta_data['style']['answers']['textSize'] ) {
										case 'small': {
											$poll->meta_data['style']['answers']['textSize'] = '12';
											break;
										}
										case 'medium': {
											$poll->meta_data['style']['answers']['textSize'] = '16';
											break;
										}
										case 'large': {
											$poll->meta_data['style']['answers']['textSize'] = '20';
											break;
										}
									}
								}
								?>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" name="answers[text-size]" value="<?php echo esc_html( $poll->meta_data['style']['answers']['textSize'] );?>" class="form-control answers-text-size" />
										<div class="input-group-addon">px</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Text Weight', 'yop-poll' );?>
								</div>
								<?php
								if ( ( true === isset( $poll->meta_data['style']['answers']['textWeight'] ) ) && ( '' !== $poll->meta_data['style']['answers']['textWeight'] ) ) {
									if ( 'normal' === $poll->meta_data['style']['answers']['textWeight'] ) {
										$answers_text_weight_normal = 'selected';
										$answers_text_weight_bold = '';
									} else {
										$answers_text_weight_normal = '';
										$answers_text_weight_bold = 'selected';
									}
								} else {
									$answers_text_weight_normal = 'selected';
									$answers_text_weight_bold = '';
								}
								?>
								<div class="col-md-10">
									<select class="answers-text-weight admin-select" style="width:100%">
						                <option value="normal" <?php echo $answers_text_weight_normal;?>><?php _e( 'Normal', 'yop-poll' );?></option>
						                <option value="bold" <?php echo $answers_text_weight_bold;?>><?php _e( 'Bold', 'yop-poll' );?></option>
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
							<?php _e( 'Buttons', 'yop-poll' );?>
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
									<input type="text" name="buttons[background-color]" value="<?php echo esc_html( $poll->meta_data['style']['buttons']['backgroundColor'] );?>" class="form-control buttons-background-color" />
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Border Thickness', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" name="buttons[border-size]" value="<?php echo esc_html( $poll->meta_data['style']['buttons']['borderSize'] );?>" class="form-control buttons-border-size" />
										<div class="input-group-addon">px</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Border color', 'yop-poll' );?>
								</div>
								<div class="col-md-10 colorpicker-component">
									<input type="text" name="buttons[border-color]" value="<?php echo esc_html( $poll->meta_data['style']['buttons']['borderColor'] );?>" class="form-control buttons-border-color" />
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Border Radius', 'yop-poll' );?>
								</div>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" name="buttons[border-radius]" value="<?php echo esc_html( $poll->meta_data['style']['buttons']['borderRadius'] );?>" class="form-control buttons-border-radius" />
										<div class="input-group-addon">px</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Padding Left/Right', 'yop-poll' );?>
								</div>
								<?php
								if ( ( false === isset( $poll->meta_data['style']['buttons']['paddingLeftRight']  ) ) || ( '' === $poll->meta_data['style']['buttons']['paddingLeftRight']  ) ) {
									$poll->meta_data['style']['buttons']['paddingLeftRight'] = '20';
								}
								?>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" name="buttons[padding-left-right]" value="<?php echo esc_html( $poll->meta_data['style']['buttons']['paddingLeftRight'] );?>" class="form-control buttons-padding-left-right" />
										<div class="input-group-addon">px</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Padding Top/Bottom', 'yop-poll' );?>
								</div>
								<?php
								if ( ( false === isset( $poll->meta_data['style']['buttons']['paddingTopBottom']  ) ) || ( '' === $poll->meta_data['style']['buttons']['paddingTopBottom']  ) ) {
									$poll->meta_data['style']['buttons']['paddingTopBottom'] = '10';
								}
								?>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" name="buttons[padding-top-bottom]" value="<?php echo esc_html( $poll->meta_data['style']['buttons']['paddingTopBottom'] );?>" class="form-control buttons-padding-top-bottom" />
										<div class="input-group-addon">px</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Text color', 'yop-poll' );?>
								</div>
								<div class="col-md-10 colorpicker-component">
									<input type="text" name="buttons[text-color]" value="<?php echo esc_html( $poll->meta_data['style']['buttons']['textColor'] );?>" class="form-control buttons-text-color" />
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Text size', 'yop-poll' );?>
								</div>
								<?php
								if ( true === in_array( $poll->meta_data['style']['buttons']['textSize'], array( 'small', 'medium', 'large' ) ) ) {
									switch ( $poll->meta_data['style']['buttons']['textSize'] ) {
										case 'small': {
											$poll->meta_data['style']['buttons']['textSize'] = '12';
											break;
										}
										case 'medium': {
											$poll->meta_data['style']['buttons']['textSize'] = '16';
											break;
										}
										case 'large': {
											$poll->meta_data['style']['buttons']['textSize'] = '20';
											break;
										}
									}
								}
								?>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" name="buttons[text-size]" value="<?php echo esc_html( $poll->meta_data['style']['buttons']['textSize'] );?>" class="form-control buttons-text-size" />
										<div class="input-group-addon">px</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Text Weight', 'yop-poll' );?>
								</div>
								<?php
								if ( ( true === isset( $poll->meta_data['style']['buttons']['textWeight'] ) ) && ( '' !== $poll->meta_data['style']['buttons']['textWeight'] ) ) {
									if ( 'normal' === $poll->meta_data['style']['buttons']['textWeight'] ) {
										$buttons_text_weight_normal = 'selected';
										$buttons_text_weight_bold = '';
									} else {
										$buttons_text_weight_normal = '';
										$buttons_text_weight_bold = 'selected';
									}
								} else {
									$buttons_text_weight_normal = 'selected';
									$buttons_text_weight_bold = '';
								}
								?>
								<div class="col-md-10">
									<select class="buttons-text-weight admin-select" style="width:100%">
						                <option value="normal" <?php echo $buttons_text_weight_normal;?>><?php _e( 'Normal', 'yop-poll' );?></option>
						                <option value="bold" <?php echo $buttons_text_weight_bold;?>><?php _e( 'Bold', 'yop-poll' );?></option>
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
								<?php
								if ( ( false === isset( $poll->meta_data['style']['errors']['borderLeftColorForSuccess'] ) ) || ( '' === $poll->meta_data['style']['errors']['borderLeftColorForSuccess'] ) ) {
									$poll->meta_data['style']['errors']['borderLeftColorForSuccess'] = '#008000';
								}
								?>
								<div class="col-md-10 colorpicker-component">
									<input type="text" value="<?php echo esc_html( $poll->meta_data['style']['errors']['borderLeftColorForSuccess'] );?>" class="form-control errors-border-left-color-for-success" />
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Border color for error', 'yop-poll' );?>
								</div>
								<?php
								if ( ( false === isset( $poll->meta_data['style']['errors']['borderLeftColorForError'] ) ) || ( '' === $poll->meta_data['style']['errors']['borderLeftColorForError'] ) ) {
									$poll->meta_data['style']['errors']['borderLeftColorForError'] = '#ff0000';
								}
								?>
								<div class="col-md-10 colorpicker-component">
									<input type="text" value="<?php echo esc_html( $poll->meta_data['style']['errors']['borderLeftColorForError'] );?>" class="form-control errors-border-left-color-for-error" />
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Border Left Thickness', 'yop-poll' );?>
								</div>
								<?php
								if ( ( false === isset( $poll->meta_data['style']['errors']['borderLeftSize'] ) ) || ( '' === $poll->meta_data['style']['errors']['borderLeftSize'] ) ) {
									$poll->meta_data['style']['errors']['borderLeftSize'] = '10';
								}
								?>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" value="<?php echo esc_html( $poll->meta_data['style']['errors']['borderLeftSize'] );?>" class="form-control errors-border-left-size" />
										<div class="input-group-addon">px</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Padding Top/Bottom', 'yop-poll' );?>
								</div>
								<?php
								if ( ( false === isset( $poll->meta_data['style']['errors']['paddingTopBottom'] ) ) || ( '' === $poll->meta_data['style']['errors']['paddingTopBottom'] ) ) {
									$poll->meta_data['style']['errors']['paddingTopBottom'] = '0';
								}
								?>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" value="<?php echo esc_html( $poll->meta_data['style']['errors']['paddingTopBottom'] );?>" class="form-control errors-padding-top-bottom" />
										<div class="input-group-addon">px</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Text color', 'yop-poll' );?>
								</div>
								<div class="col-md-10 colorpicker-component">
									<input type="text" value="<?php echo esc_html( $poll->meta_data['style']['errors']['textColor'] );?>" class="form-control errors-text-color" />
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Text size', 'yop-poll' );?>
								</div>
								<?php
								if ( true === in_array( $poll->meta_data['style']['errors']['textSize'], array( 'small', 'medium', 'large' ) ) ) {
									switch ( $poll->meta_data['style']['errors']['textSize'] ) {
										case 'small': {
											$poll->meta_data['style']['errors']['textSize'] = '12';
											break;
										}
										case 'medium': {
											$poll->meta_data['style']['errors']['textSize'] = '16';
											break;
										}
										case 'large': {
											$poll->meta_data['style']['errors']['textSize'] = '20';
											break;
										}
									}
								}
								?>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" name="errors[text-size]" value="<?php echo esc_html( $poll->meta_data['style']['errors']['textSize'] );?>" class="form-control errors-text-size" />
										<div class="input-group-addon">px</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-2 field-caption">
									<?php _e( 'Text Weight', 'yop-poll' );?>
								</div>
								<?php
								if ( ( true === isset( $poll->meta_data['style']['errors']['textWeight'] ) ) && ( '' !== $poll->meta_data['style']['errors']['textWeight'] ) ) {
									if ( 'normal' === $poll->meta_data['style']['errors']['textWeight'] ) {
										$errors_text_weight_normal = 'selected';
										$errors_text_weight_bold = '';
									} else {
										$errors_text_weight_normal = '';
										$errors_text_weight_bold = 'selected';
									}
								} else {
									$errors_text_weight_normal = 'selected';
									$errors_text_weight_bold = '';
								}
								?>
								<div class="col-md-10">
									<select class="errors-text-weight admin-select" style="width:100%">
						                <option value="normal" <?php echo $errors_text_weight_normal;?>><?php _e( 'Normal', 'yop-poll' );?></option>
						                <option value="bold" <?php echo $errors_text_weight_bold;?>><?php _e( 'Bold', 'yop-poll' );?></option>
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
						<?php
						if(  ( false === isset( $poll->meta_data['style']['custom']['css'] ) ) || ( '' === $poll->meta_data['style']['custom']['css'] ) ) {
							$poll->meta_data['style']['custom']['css'] = '';
						}
						?>
						<div class="form-group">
							<label for="">Custom CSS</label>
							<textarea id="custom-css" class="form-control custom-styles-custom-css" rows="15"><?php echo $poll->meta_data['style']['custom']['css'];?></textarea>
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
