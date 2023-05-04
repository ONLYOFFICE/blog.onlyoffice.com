<div class="widget">
	<h4 class="widget-title"><?php _e('Follow us', 'teamlab-blog-2-0'); ?></h4>
	<ul class="social-icons-list">
		<li class="icons-item"><a id="subscribelink2" class="wdgt-subscribe"></a></li>
		<?php if ($current_language == WEB_ROOT_URL.'/'.'zh') { ?>
			<li class="icons-item">
				<a class="wdgt-wechat" title="WeChat"></a>
				<div class="popup_qr_code wechat_qr_code">
						<p>关注我们</p>
						<p>了解ONLYOFFICE最新信息</p>
				</div>
			</li>
		<?php } ?>
		<?php if ($current_language == WEB_ROOT_URL.'/'.'ja') { ?>
			<li class="icons-item">
				<a class="wdgt-line" title="LINE"></a>
				<div class="popup_qr_code line_qr_code"></div>
			</li>
		<?php } ?>
		<?php if ($current_language !== WEB_ROOT_URL.'/'.'zh') { ?>
			<li class="icons-item"><a class="wdgt-facebook" target="_blank" href="https://www.facebook.com/ONLYOFFICE-833032526736775/"></a></li>
		<?php } ?>
		<li class="icons-item"><a class="wdgt-twitter" target="_blank" href="https://twitter.com/ONLY_OFFICE"></a></li>
		<li class="icons-item"><a class="wdgt-linkedin" target="_blank" href="https://www.linkedin.com/groups/ONLYOFFICE-6726380"></a></li>
		<li class="icons-item"><a class="wdgt-youtube" target="_blank" href="https://www.youtube.com/user/onlyofficeTV"></a></li>
		<li class="icons-item"><a class="wdgt-blog" target="_blank" href="https://www.onlyoffice.com/blog?_ga=2.105967360.2025187154.1673859547-2135282031.1669802332"></a></li>
		<li class="icons-item"><a class="wdgt-medium" target="_blank" href="https://medium.com/onlyoffice"></a></li>
		<?php if ($current_language !== WEB_ROOT_URL.'/'.'zh') { ?>
			<li class="icons-item"><a class="wdgt-instagram" target="_blank" href="https://www.instagram.com/the_onlyoffice/"></a></li>
		<?php } ?>
		<li class="icons-item"><a class="wdgt-github" target="_blank" href="https://github.com/ONLYOFFICE/"></a></li>
		<li class="icons-item"><a class="wdgt-fosstodon" target="_blank" href="https://fosstodon.org/@ONLYOFFICE"></a></li>
		<li class="icons-item"><a class="wdgt-tiktok" target="_blank" href="https://www.tiktok.com/@only_office"></a></li>
		<?php if ($current_language == WEB_ROOT_URL.'/'.'zh') { ?>
			<li class="icons-item"><a class="wdgt-kuaishou" target="_blank" href="https://v.kuaishou.com/HEotRv"></a></li>
			<li class="icons-item"><a class="wdgt-xiaohongshu" target="_blank" href="https://www.xiaohongshu.com/user/profile/627e271800000000210253ec"></a></li>
			<li class="icons-item"><a class="wdgt-csdn" target="_blank" href="https://blog.csdn.net/m0_68274698"></a></li>
			<li class="icons-item"><a class="wdgt-toutiao" target="_blank" href="https://www.toutiao.com/c/user/token/MS4wLjABAAAAituLIinbu_T7phDvBDiqiVsev4z3kjH95MZsEpnq7Lv2MnXBh-Sp9tuAHzFnI-Tk/"></a></li>
		<?php } ?>
	</ul>
</div>