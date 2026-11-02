
export default class Config {

	static mainSel = '.democracy_js'
	static screenSel = '.dem_screen_js'
	static cacheScreenSel = '.dem_screen_cache_js'
	static userAnswerSel = '.dem_add_answer_txt_js'

	/** @type {HTMLElement|null} */
	static $loader = null

	/** @type {number|null} */
	static loaderTmr = null

	/** @type {string} */
	static ajaxurl = ''

	/** @type {number} */
	static cookieDays = 0

	/** @type {number} */
	static animSpeed = 0

	/** @type {number} */
	static lineAnimSpeed = 0

	/** @type {boolean} */
	static isUserLoggedIn = false

}
