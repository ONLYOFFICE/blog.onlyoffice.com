import PollState from './PollState.mjs'

export default class Notice {

	static set( poll, notice, auto_hide = false ){
		const pollState = PollState.get( poll )
		clearTimeout( pollState.noticeTimeout )
		pollState.noticeStatus = notice?.status || ''
		pollState.noticeHtml = notice?.html || ''

		Notice.#render( poll, auto_hide )
	}

	static #render( poll, auto_hide = false ){
		const pollState = PollState.get( poll )
		poll.querySelector( '.dem_notice_js' )?.remove()
		pollState.noticeElement = null

		if( ! pollState.noticeStatus || ! pollState.noticeHtml ){
			return
		}

		const template = document.querySelector( '.dem_notice_template_js' )
		const notice = template?.content.firstElementChild?.cloneNode( true )
		if( ! notice ){
			return
		}

		notice.dataset.notice_status = pollState.noticeStatus
		notice.querySelector( '.dem_notice_message_js' ).innerHTML = pollState.noticeHtml

		const close = () => {
			notice.remove()
			if( pollState.noticeElement === notice ){
				pollState.noticeStatus = ''
				pollState.noticeHtml = ''
				pollState.noticeElement = null
				pollState.noticeTimeout = null
			}
		}
		notice.querySelector( '.dem_notice_close_js' )?.addEventListener( 'click', close )

		poll.prepend( notice )
		pollState.noticeElement = notice

		if( auto_hide ){
			pollState.noticeTimeout = setTimeout( close, 10000 )
		}
	}

}
