<?php 

$add_new_url = sprintf('?page=%s&action=%s', 'poll-maker-ays', 'add');
$poll_page_url = sprintf('?page=%s', 'poll-maker-ays');

 ?>

<div class="wrap">
    <div class="ays-poll-maker-htu-header">
        <h1 class="ays-poll-maker-wrapper ays_heart_beat">
            <?php echo __(esc_html(get_admin_page_title()),$this->plugin_name); ?> <i class="ays_fa ays_poll_fa_heart_o animated"></i>
        </h1>
    </div>

    <div class="ays-poll-faq-main">
        <h2><?php echo __("How to create a simple poll in 4 steps with the help of the Poll Maker plugin.", $this->plugin_name ); ?></h2>
        <fieldset style="border:1px solid #ccc; padding:10px;width:fit-content; margin:0 auto;">
            <div class="ays-poll-ol-container">
                <ol>
                    <li><?php echo __( "Go to the", $this->plugin_name ) . ' <strong><a href="'. $poll_page_url .'" target="_blank">'. __( "Poll" , $this->plugin_name ) .'</a></strong> ' .  __( "page", $this->plugin_name ); ?>,</li>
                    <li><?php echo __( "Create a new poll by clicking on the", $this->plugin_name ) . ' <strong><a href="'. $add_new_url .'" target="_blank">'. __( "Add New" , $this->plugin_name ) .'</a></strong> ' .  __( "button", $this->plugin_name ); ?>,</li>
                    <li><?php echo __( "Fill out the information.", $this->plugin_name ); ?></li>
                    <li><?php echo __( "Copy the", $this->plugin_name ) . ' <strong>'. __( "shortcode" , $this->plugin_name ) .'</strong> ' .  __( "of the poll and paste it into any post․", $this->plugin_name ); ?></li>
                </ol>
            </div>
            <div class="ays-poll-p-container">
                <p><?php echo __("Congrats! You have already created your first poll." , $this->plugin_name); ?></p>
            </div>
        </fieldset>
        <br>
        <div class="ays-poll-asked-questions">
            <h4><?php echo __("FAQs" , $this->plugin_name); ?></h4>
            <div class="ays-poll-asked-question">
                <div class="ays-poll-asked-question__header">
                    <div class="ays-poll-asked-question__title">
                        <h4><strong><?php echo __("How do I change the design of the poll?" , $this->plugin_name); ?></strong></h4>
                    </div>
                    <div class="ays-poll-asked-question__arrow"><i class="fa fa-chevron-down"></i></div>
                </div>
                <div class="ays-poll-asked-question__body">                      
                    <p>
                        <?php echo __( "To do that, please go to the", $this->plugin_name ) . 
                        ' <strong>'. __( "Styles" , $this->plugin_name ) .'</strong> ' .
                        __( "tab of the given poll. The plugin suggests you 7 awesome ready-to-use themes.  After choosing your preferred theme, you can customize it with 15+ style options to create attractive polls that people love to vote on, including", $this->plugin_name ) . 
                        ' <strong>'. __( "main color, background image, background gradient, box-shadow, answers hover" , $this->plugin_name ) .'</strong> ' . 
                        __("and etc. Moreover, you can use the" , $this->plugin_name) . 
                        ' <strong>'. __( "Custom CSS" , $this->plugin_name ) .'</strong> ' .
                        __( "written field to fully match your preferred design for your website and brand." , $this->plugin_name ); ?>
                    </p>
                </div>
            </div>
            <div class="ays-poll-asked-question">
                <div class="ays-poll-asked-question__header">
                    <div class="ays-poll-asked-question__title">
                        <h4><strong><?php echo __( "Can I organize anonymous polls?" , $this->plugin_name ); ?></strong></h4>
                    </div>
                    <div class="ays-poll-asked-question__arrow"><i class="fa fa-chevron-down"></i></div>
                </div>
                <div class="ays-poll-asked-question__body">                      
                    <p>
                        <?php echo '<strong>'. __( "Yes!" , $this->plugin_name ) .'</strong> ' .
                        __( "Please go to the Settings tab of the given poll, and find the ", $this->plugin_name ) . 
                        ' <strong>'. __( "Allow anonymity" , $this->plugin_name ) .'</strong> ' . 
                        __("option there. Enable it, and it will allow participants to respond to your polls without ever revealing their identities, even if they are registered on your website. After enabling the option, the wp _user and User IP will not be stored in the database. A giant step toward democracy!" , $this->plugin_name); ?>
                    </p>
                </div>
            </div>
            <div class="ays-poll-asked-question">
                <div class="ays-poll-asked-question__header">
                    <div class="ays-poll-asked-question__title">
                        <h4><strong><?php echo __( "How do I limit access to the poll?", $this->plugin_name ); ?></strong></h4>
                    </div>
                    <div class="ays-poll-asked-question__arrow"><i class="fa fa-chevron-down"></i></div>
                </div>
                <div class="ays-poll-asked-question__body">                      
                    <p>
                        <?php echo __( "To do that, please go to the", $this->plugin_name ) . 
                        ' <strong>'. __( "Limitation" , $this->plugin_name ) .'</strong> ' .
                        __( "tab of the given poll. The plugin suggests two methods to prevent repeat voting from the same person. Those are", $this->plugin_name ) . 
                        ' <strong>'. __( "Limit the user to rate only once by IP" , $this->plugin_name ) .'</strong> ' . 
                        __("or" , $this->plugin_name) . 
                        ' <strong>'. __( "Limit the user to rate only once by User ID." , $this->plugin_name ) .'</strong> ' . 
                        __("The other awesome functionality that the plugin suggests is" , $this->plugin_name) . 
                        ' <strong>'. __( "Only for logged in users" , $this->plugin_name ) .'</strong> ' .
                        __("to enable access to the poll those, who have logged in. This option will allow you to precisely target your respondents, and not receive unnecessary votes from others, who have not logged in. Moreover, with the help of the" , $this->plugin_name) . 
                        ' <strong>'. __( "Only selected user role" , $this->plugin_name ) .'</strong> ' .
                        __( "option, you can select your preferred user role for example administrator, editor, subscriber, customer and etc." , $this->plugin_name ); ?>
                    </p>
                </div>
            </div>
            <div class="ays-poll-asked-question">
                <div class="ays-poll-asked-question__header">
                    <div class="ays-poll-asked-question__title">
                        <h4><strong><?php echo __( "Can I know more about my respondents?", $this->plugin_name ); ?></strong></h4>
                    </div>
                    <div class="ays-poll-asked-question__arrow"><i class="fa fa-chevron-down"></i></div>
                </div>
                <div class="ays-poll-asked-question__body">                      
                    <p>
                        <?php echo '<strong>'. __( "You are in a right place!" , $this->plugin_name ) .'</strong> ' .
                        __( "You just need to enable the", $this->plugin_name ) . 
                        ' <strong>'. __( "Information Form" , $this->plugin_name ) .'</strong> ' . 
                        __("from the" , $this->plugin_name) . 
                        ' <strong>'. __( "User Data" , $this->plugin_name ) .'</strong> ' .
                        __("tab of the given poll, create your preferred" , $this->plugin_name) . 
                        ' <strong>'. __( "custom fields" , $this->plugin_name ) .'</strong> ' .
                        __( "in the" , $this->plugin_name ) .
                        ' <strong>'. __( "Custom Fields" , $this->plugin_name ) .'</strong> ' .
                        __("page from the plugin left navbar, and come up with a clear picture of who your poll participants are, where they live, what their lifestyle and personality are like, etc." , $this->plugin_name); ?>
                    </p>
                </div>
            </div>
            <div class="ays-poll-asked-question">
                <div class="ays-poll-asked-question__header">
                    <div class="ays-poll-asked-question__title">
                        <h4><strong><?php echo __( "Can I get notified every time a vote is submitted?", $this->plugin_name ); ?></strong></h4>
                    </div>
                    <div class="ays-poll-asked-question__arrow"><i class="fa fa-chevron-down"></i></div>
                </div>
                <div class="ays-poll-asked-question__body">                      
                    <p>
                        <?php echo '<strong>'. __( "You can!" , $this->plugin_name ) .'</strong> ' .
                        __( "To enable it, please go to the", $this->plugin_name ) . 
                        ' <strong>'. __( "Email" , $this->plugin_name ) .'</strong> ' . 
                        __("tab of the given poll. There you will find the" , $this->plugin_name) . 
                        ' <strong>'. __( "Results notification by email" , $this->plugin_name ) .'</strong> ' .
                        __("option. After enabling the option, the admin(or your provided email) will receive an email notification about votes at each time." , $this->plugin_name); ?>
                    </p>
                </div>
            </div>
            <div class="ays-poll-asked-question">
                <div class="ays-poll-asked-question__header">
                    <div class="ays-poll-asked-question__title">
                        <h4><strong><?php echo __( "Will I lose the data after the upgrade?", $this->plugin_name ); ?></strong></h4>
                    </div>
                    <div class="ays-poll-asked-question__arrow"><i class="fa fa-chevron-down"></i></div>
                </div>
                <div class="ays-poll-asked-question__body">                      
                    <p>
                        <?php echo '<strong>'. __( "Nope!" , $this->plugin_name ) .'</strong> ' .
                        __( "All your content and assigned settings of the plugin will remain unchanged even after switching to the Pro version. You don’t need to redo what you have already built with the free version. For the detailed instruction, please take a look at our", $this->plugin_name ) . 
                        ' <a href="https://ays-pro.com/wordpress-poll-maker-user-manual#frag_poll_upgrade" target="_blank">'. __( "upgrade guide" , $this->plugin_name ) .'</a>. '?>
                    </p>
                </div>
            </div>
        </div>
        <p class="ays-poll-faq-footer">
            <?php echo __( "For more advanced needs, please take a look at our" , $this->plugin_name ); ?> 
            <a href="https://ays-pro.com/wordpress-poll-maker-user-manual" target="_blank"><?php echo __( "Poll Maker plugin User Manual." , $this->plugin_name ); ?></a>
            <br>
            <?php echo __( "If none of these guides help you, ask your question by contacting our" , $this->plugin_name ); ?>
            <a href="https://ays-pro.com/contact" target="_blank"><?php echo __( "support specialists." , $this->plugin_name ); ?></a> 
            <?php echo __( "and get a reply within a day." , $this->plugin_name ); ?>
        </p>
    </div>
</div>

<script>
    var acc = document.getElementsByClassName("ays-poll-asked-question__header");
    var i;

    for (i = 0; i < acc.length; i++) {
      acc[i].addEventListener("click", function() {
        
        var panel = this.nextElementSibling;
        
        
        if (panel.style.maxHeight) {
          panel.style.maxHeight = null;
          this.children[1].children[0].style.transform="rotate(0deg)";
        } else {
          panel.style.maxHeight = panel.scrollHeight + "px";
          this.children[1].children[0].style.transform="rotate(180deg)";
        } 
      });
    }
</script>


