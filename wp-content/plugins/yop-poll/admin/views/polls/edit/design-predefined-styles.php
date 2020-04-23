<?php
switch( $poll->template_base ) {
    case 'basic': {
        $basic_skins_class = '';
        $basic_pretty_skins_class = ' hide';
        break;
    }
    case 'basic-pretty': {
        $basic_skins_class = ' hide';
        $basic_pretty_skins_class = '';
        break;
    }
    default: {
        $basic_skins_class = '';
        $basic_pretty_skins_class = ' hide';
        break;
    }
}
?>
<div class="row">
	<div class="col-md-12">
		&nbsp;
	</div>
</div>
<div class="row skins-no-template hide" style="margin-top: 30px;">
	<div class="col-md-12 text-center">
		<p>
			<h4>
				<?php
				_e( 'You need to select a template first to see the available skins for it', 'yop-poll' );
				?>
			</h4>
		</p>
		<p style="margin-top: 30px;">
			<h4>
				<?php
				_e( 'You can select a template <a href="#" class="skins-select-template">here</a>', 'yop-poll' );
				?>
			</h4>
		</p>
	</div>
</div>
<div class="row skins-basic<?php echo $basic_skins_class;?>">
	<?php
	foreach( $skins as $skin ) {
		if ( 'basic' === $skin->template_base ) {
			$skin_meta_data = unserialize( $skin->meta_data );
			if ( $skin->base === $poll->skin_base ) {
				$skin_basic_selected = ' selected';
			} else {
				$skin_basic_selected = '';
			}
			?>
			 <div class="col-xs-6 col-sm-3 col-md-2 col-width">
			 	<h4 class="text-center">
					<?php
					echo $skin->name;
					?>
				</h4>
			 	<figure class="yp-figure <?php echo $skin_basic_selected;?>">
				 	<div class="selected-overlay">
						<i class="glyphicon glyphicon-ok"></i>
					</div>
				 	<?php echo $skin->html_preview;?>
					<figcaption class="yp-figcaption">
						<button class="btn btn-primary choose-skin"
							 data-temp="basic"
							 data-skin-id="<?php echo $skin->id;?>"
							 data-skin-base="<?php echo $skin->base;?>"
							 data-poll-background-color="<?php echo $skin_meta_data['poll']['backgroundColor'];?>"
							 data-poll-border-size="<?php echo $skin_meta_data['poll']['borderSize'];?>"
							 data-poll-border-color="<?php echo $skin_meta_data['poll']['borderColor'];?>"
							 data-poll-border-radius="<?php echo $skin_meta_data['poll']['borderRadius'];?>"
							 data-poll-padding-left-right="<?php echo $skin_meta_data['poll']['paddingLeftRight'];?>"
							 data-poll-padding-top-bottom="<?php echo $skin_meta_data['poll']['paddingTopBottom'];?>"
							 data-questions-text-color="<?php echo $skin_meta_data['questions']['textColor']?>"
							 data-questions-text-size="<?php echo $skin_meta_data['questions']['textSize']?>"
							 data-questions-text-weight="<?php echo $skin_meta_data['questions']['textWeight']?>"
							 data-questions-text-align="<?php echo $skin_meta_data['questions']['textAlign']?>"
							 data-answers-padding-left-right="<?php echo $skin_meta_data['answers']['paddingLeftRight']?>"
							 data-answers-padding-top-bottom="<?php echo $skin_meta_data['answers']['paddingTopBottom']?>"
							 data-answers-text-color="<?php echo $skin_meta_data['answers']['textColor']?>"
							 data-answers-text-size="<?php echo $skin_meta_data['answers']['textSize']?>"
							 data-answers-text-weight="<?php echo $skin_meta_data['answers']['textWeight']?>"
							 data-answers-skin="<?php echo $skin_meta_data['answers']['skin']?>"
							 data-answers-padding-color-scheme="<?php echo $skin_meta_data['answers']['colorScheme']?>"
							 data-buttons-background-color="<?php echo $skin_meta_data['buttons']['backgroundColor']?>"
							 data-buttons-border-size="<?php echo $skin_meta_data['buttons']['borderSize']?>"
							 data-buttons-border-color="<?php echo $skin_meta_data['buttons']['borderColor']?>"
							 data-buttons-border-radius="<?php echo $skin_meta_data['buttons']['borderRadius']?>"
							 data-buttons-padding-left-right="<?php echo $skin_meta_data['buttons']['paddingLeftRight']?>"
							 data-buttons-padding-top-bottom="<?php echo $skin_meta_data['buttons']['paddingTopBottom']?>"
							 data-buttons-text-color="<?php echo $skin_meta_data['buttons']['textColor']?>"
							 data-buttons-text-size="<?php echo $skin_meta_data['buttons']['textSize']?>"
							 data-buttons-text-weight="<?php echo $skin_meta_data['buttons']['textWeight']?>"
							 data-errors-border-left-color-for-success="<?php echo $skin_meta_data['errors']['borderLeftColorForSuccess']?>"
							 data-errors-border-left-color-for-error="<?php echo $skin_meta_data['errors']['borderLeftColorForError']?>"
							 data-errors-border-left-size="<?php echo $skin_meta_data['errors']['borderLeftSize']?>"
							 data-errors-padding-top-bottom="<?php echo $skin_meta_data['errors']['paddingTopBottom']?>"
							 data-errors-border-text-color="<?php echo $skin_meta_data['errors']['textColor']?>"
							 data-errors-border-text-size="<?php echo $skin_meta_data['errors']['textSize']?>"
							 data-errors-border-text-weight="<?php echo $skin_meta_data['errors']['textWeight']?>"
							 data-custom-css="<?php echo $skin_meta_data['custom']['css']?>"
						>
							<?php _e( 'Use as is', 'yop-poll');?>
						</button>
						<button class="btn btn-primary customize-skin"
							data-temp="basic"
							data-skin-id="<?php echo $skin->id;?>"
							data-skin-base="<?php echo $skin->base;?>"
						>
							<?php _e( 'Customize', 'yop-poll');?>
						</button>
					</figcaption>
				</figure>
			 </div>
			<?php
		}
	}
	?>
</div>
<div class="row skins-basic-pretty<?php echo $basic_pretty_skins_class;?>">
	<?php
	foreach( $skins as $skin ) {
		if ( 'basic-pretty' === $skin->template_base ) {
			$skin_meta_data = unserialize( $skin->meta_data );
			?>
			 <div class="col-xs-6 col-sm-3 col-md-2 col-width">
			 	<figure class="yp-figure">
				 	<div class="selected-overlay">
						<i class="glyphicon glyphicon-ok"></i>
					</div>
				 	<?php echo $skin->html_preview;?>
					<figcaption class="yp-figcaption">
						<button class="btn btn-primary choose-skin"
							 data-temp="basic-pretty"
							 data-skin-id="<?php echo $skin->id;?>"
							 data-skin-base="<?php echo $skin->base;?>"
							 data-poll-background-color="<?php echo $skin_meta_data['poll']['backgroundColor'];?>"
							 data-poll-border-size="<?php echo $skin_meta_data['poll']['borderSize'];?>"
							 data-poll-border-color="<?php echo $skin_meta_data['poll']['borderColor'];?>"
							 data-poll-border-radius="<?php echo $skin_meta_data['poll']['borderRadius'];?>"
							 data-poll-padding-left-right="<?php echo $skin_meta_data['poll']['paddingLeftRight'];?>"
							 data-poll-padding-top-bottom="<?php echo $skin_meta_data['poll']['paddingTopBottom'];?>"
							 data-questions-text-color="<?php echo $skin_meta_data['questions']['textColor']?>"
							 data-questions-text-size="<?php echo $skin_meta_data['questions']['textSize']?>"
							 data-questions-text-weight="<?php echo $skin_meta_data['questions']['textWeight']?>"
							 data-questions-text-align="<?php echo $skin_meta_data['questions']['textAlign']?>"
							 data-answers-padding-left-right="<?php echo $skin_meta_data['answers']['paddingLeftRight']?>"
							 data-answers-padding-top-bottom="<?php echo $skin_meta_data['answers']['paddingTopBottom']?>"
							 data-answers-text-color="<?php echo $skin_meta_data['answers']['textColor']?>"
							 data-answers-text-size="<?php echo $skin_meta_data['answers']['textSize']?>"
							 data-answers-text-weight="<?php echo $skin_meta_data['answers']['textWeight']?>"
							 data-answers-skin="<?php echo $skin_meta_data['answers']['skin']?>"
							 data-answers-padding-color-scheme="<?php echo $skin_meta_data['answers']['colorScheme']?>"
							 data-buttons-background-color="<?php echo $skin_meta_data['buttons']['backgroundColor']?>"
							 data-buttons-border-size="<?php echo $skin_meta_data['buttons']['borderSize']?>"
							 data-buttons-border-color="<?php echo $skin_meta_data['buttons']['borderColor']?>"
							 data-buttons-border-radius="<?php echo $skin_meta_data['buttons']['borderRadius']?>"
							 data-buttons-padding-left-right="<?php echo $skin_meta_data['buttons']['paddingLeftRight']?>"
							 data-buttons-padding-top-bottom="<?php echo $skin_meta_data['buttons']['paddingTopBottom']?>"
							 data-buttons-text-color="<?php echo $skin_meta_data['buttons']['textColor']?>"
							 data-buttons-text-size="<?php echo $skin_meta_data['buttons']['textSize']?>"
							 data-buttons-text-weight="<?php echo $skin_meta_data['buttons']['textWeight']?>"
							 data-errors-border-left-color-for-success="<?php echo $skin_meta_data['errors']['borderLeftColorForSuccess']?>"
							 data-errors-border-left-color-for-error="<?php echo $skin_meta_data['errors']['borderLeftColorForError']?>"
							 data-errors-border-left-size="<?php echo $skin_meta_data['errors']['borderLeftSize']?>"
							 data-errors-padding-top-bottom="<?php echo $skin_meta_data['errors']['paddingTopBottom']?>"
							 data-errors-border-text-color="<?php echo $skin_meta_data['errors']['textColor']?>"
							 data-errors-border-text-size="<?php echo $skin_meta_data['errors']['textSize']?>"
							 data-errors-border-text-weight="<?php echo $skin_meta_data['errors']['textWeight']?>"
							 data-custom-css="<?php echo $skin_meta_data['custom']['css']?>"
						>
							<?php _e( 'Use as is', 'yop-poll');?>
						</button>
						<button class="btn btn-primary customize-skin"
							data-temp="basic-pretty"
							data-skin-id="<?php echo $skin->id;?>"
							data-skin-base="<?php echo $skin->base;?>"
						>
							<?php _e( 'Customize', 'yop-poll');?>
						</button>
					</figcaption>
				</figure>
			 </div>
			<?php
		}
	}
	?>
</div>
