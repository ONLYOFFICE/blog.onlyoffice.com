<?php 
    $active_tab   = (!empty($_GET['active-tab'])) ? esc_attr($_GET['active-tab']) : 'tab1';
    $this_poll_id = (isset($_GET['filter_answer'])) ? esc_attr($_GET['filter_answer']) : '';
    $poll_cats = $this->results_obj->get_categories();
    $answer_actions = $this->answer_results_obj;
    $polls = $answer_actions->get_polls();
    $last_id = 0;
    $content = '';
    $disable_chart_filter = '';
    $selected_poll_title = array();
    $latest_poll_id = 0;
    if(!empty($polls) && $this_poll_id != "0"){
        foreach($polls as $p_key => $p_value){
            $this_poll_id       = isset($p_value['id']) && $p_value['id'] != "" ? esc_attr($p_value['id']) : ""; 
            $this_poll_title    = isset($p_value['title']) && $p_value['title'] != "" ? esc_attr($p_value['title']) : "";
            $selected_poll_title[$this_poll_id] = $this_poll_title;
            $last_id = $p_key;
        }
        $last_id = isset($this_poll_id) && $this_poll_id != '' ? $this_poll_id : $polls[$last_id]['id'];
        $latest_poll_id = isset($_REQUEST['filter_answer']) && $_REQUEST['filter_answer'] != '' ? absint($_REQUEST['filter_answer']) : intval($last_id);
        $poll_answers = $answer_actions->get_poll_answers($latest_poll_id);

        $all_answer_data = array();
        $votes_sum = 0;
        foreach($poll_answers as $key => $answers){
            $votes = isset($answers['votes']) && $answers['votes'] != '' ? intval($answers['votes']) : 0;
            $votes_sum += $votes;
        }
        foreach($poll_answers as $key => $answers){
            $votes_chart = isset($answers['votes']) && $answers['votes'] != '' ? intval($answers['votes']) : 0;
            $answer_percent = 0;
            if($votes_chart > 0 && $votes_sum > 0){
                $answer_percent = ceil(($votes_chart * 100) / $votes_sum);
            }
            $all_answer_data[0] = array("Total votes" , "Total votes (".$votes_sum.")"); 
            $all_answer_data[$key+1] = array(stripslashes($answers['answer']) . " " . '('.$answer_percent . "%".')', $votes_chart); 
        }
        $poll_js_title = $latest_poll_id > 0 ? $selected_poll_title[$latest_poll_id] : 0;
        if(empty($all_answer_data)){
            $content = '<div><blockquote style="font-size: 17px;border-color:#f3ca03;">'.__("There are no responses yet." , $this->plugin_name).'</blockquote></div>';
        }
        else{
            wp_localize_script("ays-poll-admin-js", 'pollAnswerChartObj', array(
                'answerData' => $all_answer_data,
                'pollTitle'  => $poll_js_title
            ) );
        }
        
    }
    elseif(!empty($polls) && $this_poll_id == "0"){
        $content = '<div><blockquote style="font-size: 17px;border-color:#f3ca03;">'.__("There are no selected polls." , $this->plugin_name).'</blockquote></div>';        
    }
    else{
        $content = '<div><blockquote style="font-size: 17px;border-color:red;">'.__("There are no polls yet." , $this->plugin_name).'</blockquote></div>';
        $disable_chart_filter = "disabled";
    }
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php
        echo esc_html(get_admin_page_title());
    ?>
    </h1>
    <a href="https://ays-pro.com/index.php/wordpress/poll-maker/" target="_blank"><button class="disabled-button" style="float: right; margin-right: 5px; cursor: pointer;" title="<?=__('This property available only in PRO version', $this->plugin_name);?>" ><?=__('Export', $this->plugin_name);?></button></a>
    <div class="nav-tab-wrapper">
        <a href="#tab1" class="nav-tab <?php echo $active_tab == "tab1"  ? "nav-tab-active" : ""; ?>"><?= __('Results', $this->plugin_name); ?></a>
        <a href="#tab2" class="nav-tab <?php echo $active_tab == "tab2"  ? "nav-tab-active" : ""; ?> ays_poll_answer_chart_active"><?= __('Answer Chart', $this->plugin_name); ?></a>
        <a href="#tab3" class="nav-tab"><?= __('Statistics', $this->plugin_name); ?></a>
        <a href="#tab4" class="nav-tab"><?= __('Global Leaderboard', $this->plugin_name); ?></a>
        <a href="#tab5" class="nav-tab"><?= __('All Results', $this->plugin_name); ?></a>
    </div>
    <div id="tab1" class="ays-poll-tab-content <?php echo $active_tab == "tab1"  ? "ays-poll-tab-content-active" : ""; ?>">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder">
                <div id="post-body-content">
                    <div class="meta-box-sortables ui-sortable">
                        <form method="get" id="filter-div" class="alignleft actions bulkactions">                            
                            <label for="bulk-action-selector-top" class="screen-reader-text">Select Filter Type</label>
                            <input type="hidden" name="page" value="poll-maker-ays-results">
                            <select name="orderbypoll" id="bulk-action-selector-top-poll">
                                <option value="0" selected><?=__('No Filtering', $this->plugin_name);?></option>
                                <?php
                                    foreach ($this->results_obj->get_polls() as $poll) {?>
                                    <option value="<?=$poll['id'];?>" <?=(isset($_REQUEST['orderbypoll']) && $_REQUEST['orderbypoll'] == $poll['id']) ? 'selected' : '';?>><?=$poll['title'];?></option>
                                    <?php }
                                ?>
                            </select>
                            <input type="submit" id="doaction-poll" class="button action" value="<?= __('Filter', $this->plugin_name); ?>" style="width: 3.7rem;">
                            <label for="bulk-action-selector-top-cat" class="screen-reader-text">Select Filter Type</label>
                            <select name="orderbycat" id="bulk-action-selector-top-cat">
                                <option value="0" selected><?=__('No Filtering', $this->plugin_name);?></option>
                                <?php
                                    $selected = "";
                                    foreach ($poll_cats as $cat_key => $cat_value) {
                                        $selected  = (isset($_REQUEST['orderbycat']) && $_REQUEST['orderbycat'] == $cat_value['id']) ? 'selected' : '';
                                        $cat_id    = isset($cat_value['id']) && $cat_value['id'] != "" ? esc_attr($cat_value['id']) : "";
                                        $cat_value = isset($cat_value['title']) && $cat_value['title'] != "" ? esc_attr($cat_value['title']) : "";
                                    ?>                                
                                    <option value="<?php echo $cat_id ;?>" <?php echo $selected; ?>><?php echo $cat_value;?></option>
                                    <?php }
                                ?>
                            </select>
                            <input type="submit" id="doactioncat" class="button action" value="<?= __('Filter', $this->plugin_name); ?>" style="width: 3.7rem;">
                            <input type="hidden" name="active-tab" value="tab1">
                        </form>
                        <form method="post">
                            <?php                            
                                $this->results_obj->prepare_items();
                                $search = __("Search" , $this->plugin_name);
                                $this->results_obj->search_box($search, $this->plugin_name);
                                $this->results_obj->display();                                
                                $this->results_obj->mark_as_read();
                            ?>
                        </form>
                    </div>
                </div>
            </div>
            <br class="clear">
        </div>
    </div>

    <div id="tab2" class="ays-poll-tab-content <?php echo $active_tab == "tab2"  ? "ays-poll-tab-content-active" : ""; ?>">
        <div style="padding:10px 0;">                                
            <form method="get">
                <div>
                    <input type="hidden" name="page" value="poll-maker-ays-results">
                    <select name="filter_answer" <?php echo $disable_chart_filter; ?>>
                        <option value="0"><?php echo __("Select Poll", $this->plugin_name)?></option>
                        <?php
                            $selected_poll = "";
                            $opt_cont = "";
                            $selected_poll_title = array();
                                foreach($polls as $obj_key => $obj_value){
                                    $poll_id       = isset($obj_value['id']) && $obj_value['id'] != "" ? esc_attr($obj_value['id']) : ""; 
                                    $selected_poll = ($latest_poll_id == intval($obj_value['id'])) ? "selected" : "";
                                    $poll_title    = isset($obj_value['title']) && $obj_value['title'] != "" ? esc_attr($obj_value['title']) : "";
                                    
                                    $opt_cont     .= "<option value=".$poll_id." ".$selected_poll.">".$poll_title."</option>";
                                }
                                echo $opt_cont;                            
                        ?>
                    </select>
                    <input type="submit" value="Filter" class="button action" <?php echo $disable_chart_filter; ?>>                          
                    <input type="hidden" name="active-tab" value="tab2">
                </div>
                <div id="ays_poll_answer_chart" style = "width: 100%;">
                    <?php echo $content; ?>
                </div>
            </form>
        </div>
    </div>

    <div id="tab3" class="ays-poll-tab-content" style="padding-top: 14px;">
        <div class="col-sm-12" style="padding:20px;">
            <div class="pro_features" style="justify-content:flex-end;">
                <div style="margin-right:20px;">
                    <p style="font-size:20px;">
                        <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                        <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="DEVELOPER feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                    </p>
                </div>
            </div>
            <a href="https://ays-pro.com/index.php/wordpress/poll-maker/" target="_blank" title="<?=__('This property available only in PRO version', $this->plugin_name);?>">
                <img src="<?=plugins_url() . '/poll-maker/admin/images/chart_screen.png';?>" alt="Statistics" style="width:100%" >
            </a>
        </div>
    </div>
    <div id="tab4" class="ays-poll-tab-content">
        <div class="col-sm-12" style="padding:20px; margin-top:15px;">
            <div class="pro_features" style="justify-content:flex-end;">
                <div style="margin-right:20px;">
                    <p style="font-size:20px;">
                        <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                        <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="DEVELOPER feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                    </p>
                </div>
            </div>
            <div class='ays_lb_container'>
                <ul class='ays_lb_ul' style='width: 100%;'>
                    <li class='ays_lb_li'>
                        <div class='ays_lb_pos'>Pos.</div>
                        <div class='ays_lb_user'><?php echo __("Name", $this->plugin_name)?></div>
                        <div class='ays_lb_score'><?php echo __("Attempts", $this->plugin_name)?></div>
                    </li>
                    <li class="ays_lb_li">
                        <div class="ays_lb_pos">1.</div>
                        <div class="ays_lb_user">admin</div>
                        <div class="ays_lb_score">2</div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div id="tab5" class="ays-poll-tab-content">
        <div class="col-sm-12" style="padding:15px; margin-top:15px; height:241px;">
                <div class="pro_features" style="justify-content:flex-end;">
                    <div style="margin-right:20px;">
                        <p style="font-size:20px;">
                            <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                            <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="DEVELOPER feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                        </p>
                    </div>
                </div>
                <table class="wp-list-table widefat fixed striped table-view-list">
                    <thead>
                        <tr>
                            <td id="" class="manage-column column-cb check-column">
                                <label class="screen-reader-text" for="cb-select-all-1">Select All</label>
                                <input id="cb-select-all-1" type="checkbox">
                            </td>
                            <th scope="col" id="" sortable asc">
                                <a href="javascript:void(0)">
                                    <span>Polls</span>
                                    <span class="sorting-indicator"></span>
                                </a>
                            </th>
                            <th scope="col" id="" >
                                <a href="javascript:void(0)">
                                    <span>Answer</span>
                                    <span class="sorting-indicator"></span>
                                </a>
                            </th>
                            <th scope="col" id="" >
                                <a href="javascript:void(0)">
                                    <span>User IP</span>
                                    <span class="sorting-indicator"></span>
                                </a>
                            </th>
                            <th scope="col" id="" >
                                <a href="javascript:void(0)">
                                    <span>WP User</span>
                                    <span class="sorting-indicator"></span>
                                </a>
                            </th>
                            <th scope="col" id="" >
                                <a href="javascript:void(0)">
                                    <span>User Email</span>
                                    <span class="sorting-indicator"></span>
                                </a>
                            </th>
                            <th scope="col" id="" >
                                <a href="javascript:void(0)">
                                    <span>User Name</span>
                                    <span class="sorting-indicator"></span>
                                </a>
                            </th>
                            <th scope="col" id="" class="manage-column column-vote_date sortable asc">
                                <a href="javascript:void(0)">
                                    <span>Vote Datetime</span>
                                    <span class="sorting-indicator"></span>
                                </a>
                            </th>
                            <th scope="col" id="" class="manage-column column-vote_reason sortable asc">
                                <a href="javascript:void(0)">
                                    <span>Vote Reason</span>
                                    <span class="sorting-indicator"></span>
                                </a>
                            </th>
                            <th scope="col" id="" class="manage-column column-unread sortable asc">
                                <a href="javascript:void(0)">
                                    <span>Read Status</span>
                                    <span class="sorting-indicator"></span>
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row" class="check-column">
                                <input type="checkbox" class="" name="" value="3">
                            </th>
                            <td class="column- has-row-actions column-primary" data-colname="" >
                                <a href="javascript:void(0)" data-result="" class="">Default title</a>
                                <input type="hidden" value="1" class="">
                                <div class="row-actions">
                                    <span class="view-details"><a href="javascript:void(0);" data-result="3" >View details</a> | </span>
                                    <span class="delete"><a class="ays_confirm_del" data-message="this report" href="">Delete</a></span>
                                </div>
                            </td>
                            <td class="answer_id column-answer_id">12</td>
                            <td class="user_ip column-user_ip">::1</td>
                            <td class="user_id column-user_id">Guest</td>
                            <td class="user_email column-user_email">usermail@mail.com</td>
                            <td class="user_name column-user_name">User Name</td>
                            <td class="vote_date column-vote_date">17:43:00 21.12.2020</td>
                            <td class="vote_reason column-vote_reason">text</td>
                            <td class="read column-read" >
                                <div class="unread-result-badge" style="margin: 0;"></div>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                    <tr>
                            <td id="" class="manage-column column-cb check-column">
                                <label class="screen-reader-text" for="cb-select-all-1">Select All</label>
                                <input id="" type="checkbox">
                            </td>
                            <th scope="col" id="" class="manage-column column-poll_id column-primary sortable asc">
                                <a href="javascript:void(0)">
                                    <span>Polls</span>
                                    <span class=""></span>
                                </a>
                            </th>
                            <th scope="col" id="" class="manage-column column-answer_id sortable asc">
                                <a href="javascript:void(0)">
                                    <span>Answer</span>
                                    <span class=""></span>
                                </a>
                            </th>
                            <th scope="col" id="" class="manage-column column-user_ip sortable asc">
                                <a href="javascript:void(0)">
                                    <span>User IP</span>
                                    <span class=""></span>
                                </a>
                            </th>
                            <th scope="col" id="" class="manage-column column-user_id sortable asc">
                                <a href="javascript:void(0)">
                                    <span>WP User</span>
                                    <span class=""></span>
                                </a>
                            </th>
                            <th scope="col" id="" class="manage-column column-user_email sortable asc">
                                <a href="javascript:void(0)">
                                    <span>User Email</span>
                                    <span class=""></span>
                                </a>
                            </th>
                            <th scope="col" id="" class="manage-column column-user_name sortable asc">
                                <a href="javascript:void(0)">
                                    <span>User Name</span>
                                    <span class=""></span>
                                </a>
                            </th>
                            <th scope="col" id="" class="manage-column column-vote_date sortable asc">
                                <a href="javascript:void(0)">
                                    <span>Vote Datetime</span>
                                    <span class=""></span>
                                </a>
                            </th>
                            <th scope="col" id="" class="manage-column column-vote_reason sortable asc">
                                <a href="javascript:void(0)">
                                    <span>Vote Reason</span>
                                    <span class=""></span>
                                </a>
                            </th>
                            <th scope="col" id="" class="manage-column column-unread sortable asc">
                                <a href="javascript:void(0)">
                                    <span>Read Status</span>
                                    <span class=""></span>
                                </a>
                            </th>
                        </tr>
                    </tfoot>
            </table>
        </div>  
    </div>
</div>
