/**
 * Config for tipso
 */
var tipsoConfig = {
    width: '',
    maxWidth: '200',
    useTitle: true,
    delay: 100,
    speed: 500,
    background: '#32373c',
    color: '#eeeeee',
    size: 'small'
}

jQuery(document).ready(function() {
	if (jQuery('.ilj-statistic-table').length) {
		jQuery('.ilj-statistic-table').find('.tip').tipso(tipsoConfig);
	}
});