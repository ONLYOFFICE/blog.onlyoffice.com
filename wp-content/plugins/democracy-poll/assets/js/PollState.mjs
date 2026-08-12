export default class PollState {

	/** @type {WeakMap<HTMLElement, PollState>} */
	static #states = new WeakMap()

	/** @type {number} */
	pid = 0

	/** @type {number} */
	maxAnsws = 0

	/** @type {string} */
	answsMaxHeight = ''

	/** @type {boolean} */
	allowSameIpVotes = false

	/** @type {string} */
	noticeStatus = ''

	/** @type {string} */
	noticeHtml = ''

	/** @type {HTMLElement|null} */
	noticeElement = null

	/** @type {number|null} */
	noticeTimeout = null

	/**
	 * @param {HTMLElement} poll
	 */
	constructor( poll ){
		const opts = JSON.parse( poll.dataset.opts || '{}' )

		this.pid = parseInt( opts.pid, 10 ) || 0
		this.maxAnsws = parseInt( opts.max_answs, 10 ) || 0
		this.answsMaxHeight = String( opts.answs_max_height || '' )
		this.allowSameIpVotes = parseInt( opts.allow_same_ip_votes, 10 ) === 1
	}

	/**
	 * @param {HTMLElement} poll
	 * @return {PollState}
	 */
	static get( poll ){
		let state = PollState.#states.get( poll )
		if( ! state ){
			state = new PollState( poll )
			PollState.#states.set( poll, state )
		}

		return state
	}

}
