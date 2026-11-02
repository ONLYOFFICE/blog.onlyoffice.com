import Cookies from 'js-cookie'
import Config from './Config.mjs'
import PollState from './PollState.mjs'
import Loader from './Loader.mjs'
import Utils from './Utils.mjs'
import Notice from './Notice.mjs'
import BrowserFingerprint from './BrowserFingerprint.mjs'

export default class Cache {

	static actionsHandler

	static initAll(){
		const cacheBlocks = document.querySelectorAll( '.dem_cache_screens_js' )
		if( ! cacheBlocks.length ){
			return
		}

		cacheBlocks.forEach( cacheBlock => Cache.initCache( cacheBlock ) )
	}

	static initCache( cacheBlock ){
		const dem = Cache.findMainBlock( cacheBlock )
		if( ! dem ){
			console.warn( 'Democracy: Main dem div not found' )
			return
		}

		const screen = dem.querySelector( Config.screenSel )
		if( ! screen ){
			return
		}

		const demId = PollState.get( dem ).pid
		const answrs = Cache.getPollCookie( demId, Config.cookieDays )
		const notVotedFlag = answrs === 'notVoted' // If we already checked that user hasn't voted, don't request again
		const isAnswrs = answrs && ! notVotedFlag

		// choose which screen to show and how to handle it
		const voteBlock = cacheBlock.querySelector( Config.cacheScreenSel + '.vote' )
		const votedBlock = cacheBlock.querySelector( Config.cacheScreenSel + '.voted' )
		const voteHTML = voteBlock ? voteBlock.innerHTML : ''
		const votedHTML = votedBlock ? votedBlock.innerHTML : ''

		// if poll is closed, only results should be cached. Exit.
		if( ! voteHTML ){
			return
		}

		// apply cached view
		// if results view is available
		const setVoted = isAnswrs && votedHTML
		screen.innerHTML = (setVoted ? votedHTML : voteHTML) + '<!--cache-->'
		screen.classList.remove( 'vote', 'voted' )
		screen.classList.add( setVoted ? 'voted' : 'vote' )

		if( isAnswrs ){
			Cache.setAnswers( screen, answrs )
		}

		Cache.actionsHandler( screen )

		if( notVotedFlag ){
			return // exit if it has already been checked that the user has not voted.
		}

		// Validate the cached browser state against the server identity on first interaction.
		{
			let tmout
			const notcheck__fn = function(){
				clearTimeout( tmout )
			}
			const check__fn = function(){
				tmout = setTimeout( function(){
					// Run once!
					if( dem._vote_check_done ){
						return
					}
					dem._vote_check_done = true

					const forDotsLoader = dem.querySelector( '.dem_link_js' )
					if( forDotsLoader ){
						Loader.setLoader( forDotsLoader )
					}

					Cache.postWithFingerprint( Config.ajaxurl, {
						dem_pid: demId,
						dem_act: 'getVotedIds',
						action : 'dem_ajax'
					}, PollState.get( dem ).allowSameIpVotes && ! Config.isUserLoggedIn )
						.then( response => {
							forDotsLoader && Loader.unsetLoader( screen )

							screen.dataset['expanded'] = 'true'
							const setVoted = response.voted_for && votedHTML
							screen.innerHTML = setVoted ? votedHTML : voteHTML
							screen.classList.remove( 'vote', 'voted' )
							screen.classList.add( setVoted ? 'voted' : 'vote' )
							Cache.setAnswers( screen, response.voted_for )
							Cache.actionsHandler( screen )

							if( response.notice?.status === 'login_required' ){
								screen.querySelector( '.dem_revote_button_js' )?.remove()
							}
							Notice.set( dem, response.notice, true )
						} )
						.catch( error => {
							forDotsLoader && Loader.unsetLoader( screen )
							console.warn( 'Democracy: AJAX request failed', error )
						} )
				}, 700 )
				// 700 for optimization, so that the request is not sent instantly if you just swipe the mouse on the survey...
			}

			// hover
			dem.addEventListener( 'mouseenter', check__fn )
			dem.addEventListener( 'mouseleave', notcheck__fn )
			dem.addEventListener( 'click', check__fn )
		}
	}

	// set user's answers in results/vote block
	static setAnswers( screen, answrs ){
		const aids = answrs.split( /,/ ).filter( aid => aid !== '' )

		// results view
		if( screen.classList.contains( 'voted' ) ){
			const dema = screen.querySelector( '.dem_answers_list_js' )
			const votedtxt = dema ? dema.dataset.voted_txt : ''

			aids.forEach( aid => {
				const nodes = Cache.queryAidNodes( screen, aid )
				nodes.forEach( node => {
					node.classList.add( 'dem-voted-this' )

					const title = node.getAttribute( 'title' ) || ''
					if( votedtxt ){
						node.setAttribute( 'title', votedtxt + title )
					}
				} )
			} )

			// remove "Vote" button
			screen.querySelectorAll( '.dem_vote_link_js' ).forEach( node => node.remove() )
		}
		// voting view
		else{
			const answerNodes = Array.from( screen.querySelectorAll( '[data-aid]' ) )
			const btnVoted = screen.querySelector( '.dem_voted_button_js' )

			// set answers
			aids.forEach( aid => {
				const nodes = Cache.queryAidNodes( screen, aid )
				nodes.forEach( node => {
					node.querySelectorAll( 'input' ).forEach( input => {
						input.checked = true
					} )
				} )
			} )

			// disable all
			answerNodes.forEach( node => {
				node.querySelectorAll( 'input' ).forEach( input => {
					input.disabled = true
				} )
			} )

			// remove voting button
			screen.querySelectorAll( '.dem_vote_button_js' ).forEach( node => node.remove() )
			//screen.querySelectorAll( '[data-dem-act="vote"]' ).forEach( node => node.remove() )

			// if "already voted" button exists, revote is disabled
			if( btnVoted ){
				Utils.showElement( btnVoted )
			}
			// show revote button
			else{
				screen.querySelectorAll( '.dem_revote_button_wrap_js' ).forEach( Utils.showElement )
			}
		}
	}

	static findMainBlock( cacheBlock ){
		let prev = cacheBlock.previousElementSibling
		while( prev ){
			if( prev.matches( Config.mainSel ) ){
				return prev
			}
			prev = prev.previousElementSibling
		}

		return cacheBlock.closest( Config.mainSel )
	}

	static getPollCookie( pollId, cookieDays ){
		const raw = Cookies.get( 'demPoll' )
		if( ! raw ){
			return null
		}

		const targetPid = String( pollId )
		const voteTTL = Math.trunc( Number( cookieDays ) * 86400 )
		const now = Date.now() / 1000
		let value = null

		for( const record of raw.split( '|' ) ){
			const match = record.match( /^(\d+):(0|[1-9]\d*(?:_[1-9]\d*)*)-([0-9a-z]+)$/ )
			if( ! match || match[1] !== targetPid ){
				continue
			}

			const aids = match[2]
			const timestamp = parseInt( match[3], 36 )
			const ttl = (aids === '0') ? 43200 : voteTTL

			if( timestamp && ttl > 0 && timestamp + ttl > now ){
				value = aids === '0' ? 'notVoted' : aids.replaceAll( '_', ',' )
			}
		}

		return value
	}

	static queryAidNodes( screen, aid ){
		const safeAid = (window.CSS && CSS.escape) ? CSS.escape( aid ) : aid
		return Array.from( screen.querySelectorAll( '[data-aid="' + safeAid + '"]' ) )
	}

	static post( url, data ){
		const body = new URLSearchParams()
		Object.keys( data ).forEach( key => body.append( key, data[ key ] ) )

		return fetch( url, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
			},
			body
		} )
			.then( response => {
				if( ! response.ok ){
					throw new Error( 'Bad network response' )
				}
				return response.json()
			} )
	}

	static postWithFingerprint( url, data, useFingerprint ){
		if( ! useFingerprint ){
			return Cache.post( url, data )
		}

		return BrowserFingerprint.get().then( fingerprint => {
			return Cache.post( url, { ...data, fingerprint } )
		} )
	}

}
