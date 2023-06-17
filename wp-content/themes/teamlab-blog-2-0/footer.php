<?php

/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Teamlab_Blog_2.0
 */

global $current_language;
global $sitepress;
$lang = $sitepress->get_current_language();
?>
</div><!-- #content -->
</div><!-- #pageContent -->
</div><!-- #MainContainer -->
<footer>
    <div class="footercolor">
        <div class="narrowfooter">
            <div class="bottomlines">
                <ul class="footer_menu <?php echo $lang ?>">
                    <div group-menu>
<!--                        <li class="footer-border">-->
<!--                            <a id="footer_menu_developers" class="footer-button">-->
<!--                                <h6>--><?php //_e('Developers', 'teamlab-blog-2-0'); ?><!--</h6>-->
<!--                            </a>-->
<!--                            <div id="navitem_footer_developers">-->
<!--                                <ul>-->
<!--                                    <li>-->
<!--                                        <p><a href="--><?php //echo $current_language ?><!--/developer-edition.aspx">--><?php //_e('Developer Edition', 'teamlab-blog-2-0'); ?><!--</a></p>-->
<!--                                    </li>-->
<!--                                    <li>-->
<!--                                        <p><a href="--><?php //echo $current_language ?><!--/document-builder.aspx">--><?php //_e('Document Builder', 'teamlab-blog-2-0'); ?><!--</a></p>-->
<!--                                    </li>-->
<!--                                    <li>-->
<!--                                        <p><a href="--><?php //echo $current_language ?><!--/groups.aspx">--><?php //_e('Groups', 'teamlab-blog-2-0'); ?><!--</a></p>-->
<!--                                    </li>-->
<!--                                    <li>-->
<!--                                        <p><a target="_blank" rel="noreferrer noopener" id="navitem_api" href="https://api.onlyoffice.com/">--><?php //_e('API', 'teamlab-blog-2-0'); ?><!--</a></p>-->
<!--                                    </li>-->
<!--                                    <li>-->
<!--                                        <p><a target="_blank" rel="noreferrer noopener" href="https://github.com/ONLYOFFICE/">--><?php //_e('Code on GitHub', 'teamlab-blog-2-0'); ?><!--</a></p>-->
<!--                                    </li>-->
<!--                                </ul>-->
<!--                            </div>-->
<!--                        </li>-->
<!--                        <li class="footer-border">-->
<!--                            <a id="footer_menu_solutions" class="footer-button">-->
<!--                                <h6>--><?php //_e('Security', 'teamlab-blog-2-0'); ?><!--</h6>-->
<!--                            </a>-->
<!--                            <div id="navitem_footer_solutions">-->
<!--                                <ul>-->
<!--                                    <li>-->
<!--                                        <p><a href="--><?php //echo $current_language ?><!--/security.aspx#access_control">--><?php //_e('Authentication and access control', 'teamlab-blog-2-0'); ?><!--</a></p>-->
<!--                                    </li>-->
<!--                                    <li>-->
<!--                                        <p><a href="--><?php //echo $current_language ?><!--/security.aspx#data_protection">--><?php //_e('Data protection', 'teamlab-blog-2-0'); ?><!--</a></p>-->
<!--                                    </li>-->
<!--                                    <li>-->
<!--                                        <p><a href="--><?php //echo $current_language ?><!--/security.aspx#data_encryption">--><?php //_e('Data encryption', 'teamlab-blog-2-0'); ?><!--</a></p>-->
<!--                                    </li>-->
<!--                                    <li>-->
<!--                                        <p><a href="--><?php //echo $current_language ?><!--/private-rooms.aspx">--><?php //_e('Private rooms', 'teamlab-blog-2-0'); ?><!--</a></p>-->
<!--                                    </li>-->
<!--                                    <li>-->
<!--                                        <p><a href="https://www.onlyoffice.com/blog/2018/05/how-onlyoffice-complies-with-gdpr/">--><?php //_e('GDPR compliance', 'teamlab-blog-2-0'); ?><!--</a></p>-->
<!--                                    </li>-->
<!--                                    <li>-->
<!--                                        <p><a href="https://www.onlyoffice.com/blog/2020/10/how-onlyoffice-complies-with-hipaa/">--><?php //_e('HIPAA compliance', 'teamlab-blog-2-0'); ?><!--</a></p>-->
<!--                                    </li>-->
<!--                                </ul>-->
<!--                            </div>-->
<!--                        </li>-->
                        <li class="footer-border no_tablet_view">
                            <a id="footer_menu_solutions_bsz" class="footer-button">
                                <h6><?php _e('Solutions', 'teamlab-blog-2-0'); ?></h6>
                            </a>
                            <div id="navitem_footer_by_size">
                                <ul>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/for-small-business.aspx"><?php _e('SMBs', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/for-enterprises.aspx"><?php _e('Enterprises', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/home-use.aspx"><?php _e('Home use', 'teamlab-blog-2-0'); ?></a></p>
                                    <li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/for-developers.aspx"><?php _e('Developers', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/for-hosting-providers.aspx"><?php _e('Hosting providers', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/for-government.aspx"><?php _e('Government', 'teamlab-blog-2-0'); ?></a></p>
                                    <li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/healthcare.aspx"><?php _e('Healthcare', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/for-research.aspx"><?php _e('Research', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/education.aspx"><?php _e('Education', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/nonprofit-organizations.aspx"><?php _e('Nonprofits', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="footer-border tablet_view_only">
                            <a id="footer_menu_support" class="footer-button">
                                <h6><?php _e('Support', 'teamlab-blog-2-0'); ?></h6>
                            </a>
                            <div id="navitem_footer_support">
                                <ul>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/support-contact-form.aspx"><?php _e('Support contact form', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a target="_blank" rel="noreferrer noopener" href="https://forum.onlyoffice.com/"><?php _e('Forum', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/demo-order.aspx"><?php _e('Order demo', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/webinars.aspx"><?php _e('Webinars', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/training-courses.aspx"><?php _e('Training courses', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </div>
                    <div group-menu>
                        <li class="footer-border">
                            <a id="footer_menu_perform" class="footer-button">
                                <h6><?php _e('Features', 'teamlab-blog-2-0'); ?></h6>
                            </a>
                            <div id="navitem_footer_perform">
                                <ul>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/document-editor.aspx"><?php _e('Document Editor', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/spreadsheet-editor.aspx"><?php _e('Spreadsheet Editor', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/presentation-editor.aspx"><?php _e('Presentation Editor', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/form-creator.aspx"><?php _e('Form creator', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/pdf-reader.aspx"><?php _e('PDF reader & converter', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/security.aspx"><?php _e('Security', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/app-directory"><?php _e('App Directory', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </div>
                    <div group-menu>
                        <li class="footer-border tablet_view_only">
                            <a id="footer_menu_solutions_bsz" class="footer-button">
                                <h6><?php _e('Solutions', 'teamlab-blog-2-0'); ?></h6>
                            </a>
                            <div id="navitem_footer_by_size">
                                <ul>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/for-small-business.aspx"><?php _e('SMBs', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/for-enterprises.aspx"><?php _e('Enterprises', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/home-use.aspx"><?php _e('Home use', 'teamlab-blog-2-0'); ?></a></p>
                                    <li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/for-developers.aspx"><?php _e('Developers', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/for-hosting-providers.aspx"><?php _e('Hosting providers', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/for-government.aspx"><?php _e('Government', 'teamlab-blog-2-0'); ?></a></p>
                                    <li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/healthcare.aspx"><?php _e('Healthcare', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/for-research.aspx"><?php _e('Research', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/education.aspx"><?php _e('Education', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/nonprofit-organizations.aspx"><?php _e('Nonprofits', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="footer-border no_tablet_view">
                            <a id="footer_menu_support" class="footer-button">
                                <h6><?php _e('Support', 'teamlab-blog-2-0'); ?></h6>
                            </a>
                            <div id="navitem_footer_support">
                                <ul>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/support-contact-form.aspx"><?php _e('Support contact form', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a target="_blank" rel="noreferrer noopener" href="https://forum.onlyoffice.com/"><?php _e('Forum', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/demo-order.aspx"><?php _e('Order demo', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/webinars.aspx"><?php _e('Webinars', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/training-courses.aspx"><?php _e('Training courses', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="footer-border">
                            <a id="footer_menu_GetInfo" class="footer-button">
                                <h6><?php _e('Resources', 'teamlab-blog-2-0'); ?></h6>
                            </a>
                            <div id="navitem_footer_GetInfo">
                                <ul>
                                    <li>
                                        <p><a target="_blank" rel="noreferrer noopener" href="https://helpcenter.onlyoffice.com/index.aspx"><?php _e('Help Center', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <?php if (!($current_language == WEB_ROOT_URL.'/'.'zh')) { ?>
                                        <li>
                                            <p><a href="<?php echo $current_language ?>/document-editor-comparison.aspx"><?php _e('Compare to other suites', 'teamlab-blog-2-0'); ?></a></p>
                                        </li>
                                    <?php } ?>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/contribute.aspx"><?php _e('Contribute', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/legalterms.aspx"><?php _e('Legal notice', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </div>
                        <div group-menu>
                        <?php if (!($current_language == WEB_ROOT_URL.'/'.'zh')) { ?>
                            <li class="footer-border">
                                <a id="footer_menu_comparison" class="footer-button">
                                    <h6><?php _e('Comparison', 'teamlab-blog-2-0'); ?></h6>
                                </a>
                                <div id="navitem_footer_comparison">
                                    <ul>
                                        <li>
                                            <p><a href="<?php echo $current_language ?>/best-microsoft-office-alternative.aspx"><?php _e('ONLYOFFICE Docs vs MS Office Online', 'teamlab-blog-2-0'); ?></a></p>
                                        </li>
                                        <li>
                                            <p><a href="<?php echo $current_language ?>/best-google-docs-alternative.aspx"><?php _e('ONLYOFFICE Docs vs Google Docs', 'teamlab-blog-2-0'); ?></a></p>
                                        </li>
                                        <li>
                                            <p><a href="<?php echo $current_language ?>/best-zoho-docs-alternative.aspx"><?php _e('ONLYOFFICE Docs vs Zoho Docs', 'teamlab-blog-2-0'); ?></a></p>
                                        <li>
                                        <li>
                                            <p><a href="<?php echo $current_language ?>/best-libreoffice-alternative.aspx"><?php _e('ONLYOFFICE Docs vs LibreOffice', 'teamlab-blog-2-0'); ?></a></p>
                                        </li>
                                        <li>
                                            <p><a href="<?php echo $current_language ?>/best-wps-alternative.aspx"><?php _e('ONLYOFFICE Docs vs WPS', 'teamlab-blog-2-0'); ?></a></p>
                                        </li>
                                        <li>
                                            <p><a href="<?php echo $current_language ?>/best-adobe-alternative.aspx"><?php _e('ONLYOFFICE Docs vs Adobe Acrobat', 'teamlab-blog-2-0'); ?></a></p>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        <?php } ?>
                        <li class="footer-border">
                            <a id="footer_menu_contact" class="footer-button">
                                <h6><?php _e('Contact Us', 'teamlab-blog-2-0'); ?></h6>
                            </a>
                            <div id="navitem_footer_contact">
                                <ul>
                                    <li>
                                        <?php _e('Sales questions', 'teamlab-blog-2-0'); ?>
                                        <a class="emailus" href="mailto:sales@onlyoffice.com">sales@onlyoffice.com</a>

                                    </li>
                                    <li>
                                        <?php _e('Partner inquiries', 'teamlab-blog-2-0'); ?>
                                        <a class="emailus" href="mailto:partners@onlyoffice.com">partners@onlyoffice.com</a>
                                    </li>
                                    <li>
                                        <?php _e('Press inquiries', 'teamlab-blog-2-0'); ?>
                                        <a class="emailus" href="mailto:press@onlyoffice.com">press@onlyoffice.com</a>
                                    </li>
                                    <li>
                                        <p><a href="<?php echo $current_language ?>/call-back-form.aspx"><?php _e('Request a call', 'teamlab-blog-2-0'); ?></a></p>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </div>
                </ul>
                <div class="footer_menu_item fmi_social <?php echo $lang ?>">
                    <div class="SocialLinks">
                        <span>
                            <?php _e('Follow us on:', 'teamlab-blog-2-0'); ?>
                        </span>
                        <ul class="ListSocLink">
                            <li>
                                <a class="subscribe-mail" title="Subscribe to our newsletters" id="subscribelink">
                                    <label title="Subscribe to our newsletters" class="social_grey_subscribe"></label>
                                </a>
                            </li>
                            <?php if ($current_language !== WEB_ROOT_URL.'/'.'zh') { ?>
                            <li><a target="_blank" href="https://www.facebook.com/pages/OnlyOffice/833032526736775" onmouseup="PageTrack('GoTo_facebook');" rel="noreferrer noopener" title="Follow us on Facebook" class="faceBook">
                                    <label class="social_grey_fb" title="Facebook"></label>
                                </a></li>
                            <?php } ?>
                            <li><a rel="nofollow" target="_blank" onmouseup="PageTrack('GoTo_twitter');" href="https://twitter.com/ONLY_OFFICE" title="Follow us on Twitter" class="twitter" rel="noreferrer noopener">
                                    <label class="social_grey_twi" title="Twitter"></label>
                                </a></li>
                            <li><a rel="nofollow" target="_blank" onmouseup="PageTrack('GoTo_linkedin');" href="https://www.linkedin.com/groups/ONLYOFFICE-6726380" title="Follow us on LinkedIn" class="linkedin" rel="noreferrer noopener">
                                    <label class="social_grey_in" title="LinkedIn"></label>
                                </a></li>
                            <li><a rel="nofollow" target="_blank" onmouseup="PageTrack('GoTo_youtube');" href="https://www.youtube.com/user/onlyofficeTV" title="Follow us on YouTube" class="youtube" rel="noreferrer noopener">
                                    <label class="social_grey_tube" title="YouTube"></label>
                                </a></li>
                            <li><a rel="nofollow" target="_blank" onmouseup="PageTrack('GoTo_blog');" href="https://www.onlyoffice.com/blog" title="Read our blog" class="blog" rel="noreferrer noopener">
                                    <label title="Blog" class="social_grey_blog"></label>
                                </a></li>
                            <li><a rel="nofollow" target="_blank" onmouseup="PageTrack('GoTo_medium');" href="https://medium.com/onlyoffice" title="Follow us on Medium" class="medium" rel="noreferrer noopener">
                                    <label title="Medium" class="social_grey_medium"></label>
                                </a></li>
                            <?php if ($current_language !== WEB_ROOT_URL.'/'.'zh') { ?>
                            <li><a rel="nofollow" target="_blank" onmouseup="PageTrack('GoTo_instagram');" class="medium" title="Follow us on Instagram" href="https://www.instagram.com/the_onlyoffice/" rel="noreferrer noopener">
                                    <label title="Instagram" class="social_grey_instagram"></label>
                                </a></li>
                            <?php } ?>
                            <li><a class="github" title="Follow us on GitHub" href="https://github.com/ONLYOFFICE/" onmouseup="PageTrack('GoTo_medium');" target="_blank" rel="noreferrer noopener">
                                    <label title="GitHub" class="social_grey_github"></label>
                                </a></li>
                            <li><a class="fosstodon" title="Follow us on Fosstodon" href="https://fosstodon.org/@ONLYOFFICE" onmouseup="PageTrack('GoTo_medium');" target="_blank" rel="noreferrer noopener">
                                    <label title="Fosstodon" class="social_grey_fosstodon"></label>
                                </a></li>
                            <?php if ($current_language == WEB_ROOT_URL.'/'.'zh') { ?>
                                <li class="wechat"><a class="wechat" title="WeChat" onmouseup="PageTrack('GoTo_medium');">
                                        <label title="WeChat" class="social_grey_wechat"></label>
                                        <div class="wechat_qr_code">
                                            <p>关注我们</p>
                                            <p>了解ONLYOFFICE最新信息</p>
                                        </div>
                                    </a></li>
                            <?php } ?>
                            <li><a class="tiktok" title="Follow us on TikTok" href="https://www.tiktok.com/@only_office" onmouseup="PageTrack('GoTo_medium');" target="_blank" rel="noreferrer noopener">
                                    <label title="TikTok" class="social_grey_tiktok"></label>
                                </a></li>
                            <?php if ($current_language == WEB_ROOT_URL.'/'.'ja') { ?>
                                <li class="line">
                                    <a class="line" title="LINE" onmouseup="PageTrack('GoTo_medium');" target="_blank" rel="noreferrer noopener">
                                        <label title="LINE" class="social_grey_line"></label>
                                        <div class="line_qr_code">
                                            <p></p>
                                            <p></p>
                                        </div>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if ($current_language == WEB_ROOT_URL.'/'.'zh') { ?>
                                <li><a class="kuaishou" title="在Kuaishou上关注我们" href="https://v.kuaishou.com/HEotRv" onmouseup="PageTrack('GoTo_medium');" target="_blank" rel="noreferrer noopener">
                                    <label title="Kuaishou" class="social_grey_kuaishou"></label>
                                </a></li>
                                <li><a class="xiaohongshu" title="在Xiaohongshu上关注我们" href="https://www.xiaohongshu.com/user/profile/627e271800000000210253ec" onmouseup="PageTrack('GoTo_medium');" target="_blank" rel="noreferrer noopener">
                                    <label title="Xiaohongshu" class="social_grey_xiaohongshu"></label>
                                </a></li>
                                <li><a class="csdn" title="在CSDN上关注我们" href="https://blog.csdn.net/m0_68274698" onmouseup="PageTrack('GoTo_medium');" target="_blank" rel="noreferrer noopener">
                                    <label title="CSDN" class="social_grey_csdn"></label>
                                </a></li>
                                <li><a class="toutiao" title="在Toutiao上关注我们" href="https://www.toutiao.com/c/user/token/MS4wLjABAAAAituLIinbu_T7phDvBDiqiVsev4z3kjH95MZsEpnq7Lv2MnXBh-Sp9tuAHzFnI-Tk/" onmouseup="PageTrack('GoTo_medium');" target="_blank" rel="noreferrer noopener">
                                    <label title="Toutiao" class="social_grey_toutiao"></label>
                                </a></li>
                            <?php } ?>
                        </ul>
                    </div>
                    <div class="copyReserved">
                        <span>&copy; <?php _e('Ascensio System SIA 2009', 'teamlab-blog-2-0'); ?>-<?php echo date("Y"); ?><?php _e('.', 'teamlab-blog-2-0'); ?></span>
                        <span><?php _e('All rights reserved', 'teamlab-blog-2-0'); ?></span>
                    </div>
                </div>
            </div>

        </div>
    </div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>

</html>