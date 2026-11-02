import Config from './Config.mjs'
import PollState from './PollState.mjs'

export default class Utils {

	// Determine the height when the element uses height:auto
	static detectRealHeight( el ){
		if( ! el.parentElement ){
			return el.getBoundingClientRect().height
		}

		const clone = el.cloneNode( true )
		clone.querySelectorAll( 'input, select, textarea, button' ).forEach( el => el.removeAttribute( 'name' ) ) // make not clickable
		Object.assign( clone.style, {
			height    : 'auto', maxHeight: 'none',
			position  : 'absolute', left: '-9999px', top: '0', width: window.getComputedStyle( el ).width,
			visibility: 'hidden', pointerEvents: 'none'
		} )

		el.before( clone )

		const cloneStyle = window.getComputedStyle( clone )
		const realHeight = (cloneStyle.boxSizing === 'border-box')
			? parseFloat( cloneStyle.height )
			: clone.getBoundingClientRect().height

		clone.remove()

		return realHeight
	}

	// Set height explicitly
	static setHeight( el, doAnimation = false ){
		const newH = Utils.detectRealHeight( el )

		if( doAnimation ){
			const duration = Config.animSpeed || 0
			Utils.animateHeight( el, newH, duration )
		}
		else{
			el.style.height = newH + 'px'
		}
	}

	// jQuery :visible equivalent.
	static isVisible( el ){
		return !! (el.offsetWidth || el.offsetHeight || el.getClientRects().length)
	}

	/**
	 * @param {HTMLElement} screen
	 */
	static setAnswsMaxHeight( screen ){
		const poll = screen.closest( Config.mainSel )
		const maxHeight = PollState.get( poll ).answsMaxHeight
		if( ! maxHeight ){
			return
		}

		const el = screen.querySelector( '.dem_answers_list_js' )
		const maxHeightPx = Utils.heightToPixels( maxHeight, el )

		el.style.maxHeight = 'none'
		el.style.overflowY = 'visible'

		const elStyle = window.getComputedStyle( el )
		const elHeight = (elStyle.boxSizing === 'border-box')
			? parseFloat( elStyle.height )
			: el.getBoundingClientRect().height

		// collapse if above max height and diff > 100px; hiding 100px isn't worth it
		const diff = elHeight - maxHeightPx
		if( diff > 100 ){
			el.style.position = 'relative'

			const overlay = Utils.newEl( '<span class="dem__collapser dem_collapser_js"><span class="arr"></span></span>' )
			el.append( overlay )

			const fn__expand = () => {
				overlay.classList.add( 'expanded' )
				overlay.classList.remove( 'collapsed' )
			}
			const fn__collaps = () => {
				overlay.classList.add( 'collapsed' )
				overlay.classList.remove( 'expanded' )
			}
			let timeout

			// don't collapse if it was expanded
			const isExpanded = screen.dataset['expanded'] === 'true'
			if( isExpanded ){
				fn__expand()
			}
			else{
				fn__collaps()
				el.style.height = maxHeight
				el.style.overflowY = 'hidden'
			}

			// trigger click on hover so user doesn't need to click to expand
			overlay.addEventListener( 'mouseenter', function(){
				if( screen.dataset['expanded'] !== 'true' ){
					timeout = setTimeout( () => overlay.dispatchEvent( new Event( 'click' ) ), 1000 )
				}
			} )
			overlay.addEventListener( 'mouseleave', function(){
				clearTimeout( timeout )
			} )

			overlay.addEventListener( 'click', function(){
				clearTimeout( timeout )

				// collapse
				if( screen.dataset['expanded'] === 'true' ){
					fn__collaps()

					delete screen.dataset['expanded']
					screen.style.height = 'auto'
					el.style.overflowY = 'hidden'
					Utils.animateHeight( el, maxHeightPx, Config.animSpeed, () => {
						screen.style.height = Utils.detectRealHeight( screen ) + 'px'
					} )
				}
				// expand
				else{
					fn__expand()

					// measure height without collapsing
					const newH = Utils.detectRealHeight( el ) + 7 // extra space for "add your answer"

					screen.dataset['expanded'] = 'true'
					screen.style.height = 'auto'
					Utils.animateHeight( el, newH, Config.animSpeed, () => {
						screen.style.height = Utils.detectRealHeight( screen ) + 'px'
						el.style.overflowY = 'visible'
					} )
				}
			} )
		}
	}

	static heightToPixels(cssValue, forEl) {
		const value = parseFloat(cssValue)
		const unit = cssValue.replace(value, '').trim()

		switch (unit) {
			case 'px':  return value
			case 'rem': return value * parseFloat(getComputedStyle(document.documentElement).fontSize)
			case 'em':  return value * parseFloat(getComputedStyle(forEl).fontSize)
			case 'vh':  return value / 100 * window.innerHeight
			case 'vw':  return value / 100 * window.innerWidth
			case '%':   return value / 100 * forEl.getBoundingClientRect().height
			default:    return parseFloat(cssValue) // fallback
		}
	}

	static #maxAnswLimitBound = false

	// max answers limit - limit for multi-answer selection
	static maxAnswLimitInit(){
		if( Utils.#maxAnswLimitBound ){
			return
		}
		Utils.#maxAnswLimitBound = true

		const eventHanlder = ( event ) => {
			const target = event.target
			if( ! (target instanceof HTMLInputElement)
				|| ( event.type === 'change' && target.type !== 'checkbox' )
				|| ( event.type === 'input' && ! target.matches( Config.userAnswerSel ) )
			){
				return
			}

			const screen = target.closest( Config.screenSel )
			screen && Utils.updateMaxAnswLimit( screen )
		}

		document.addEventListener( 'change', eventHanlder )
		document.addEventListener( 'input', eventHanlder )
	}

	static updateMaxAnswLimit( screen ){
		const {
			maxAnsws,
			checkedCount,
			userAnswerInput,
			hasUserAnswer,
			isMaxReached
		} = Utils.maxAnswLimitData( screen )
		if( ! maxAnsws ){
			return
		}

		const checkboxes = screen.querySelectorAll( 'input[type="checkbox"]' )
		if( ! checkboxes.length ){
			return
		}

		checkboxes.forEach( checkbox => {
			const shouldDisable = isMaxReached && ! checkbox.checked
			checkbox.disabled = shouldDisable
			checkbox.closest( '.dem_answer_item_js' )?.classList.toggle( 'dem-disabled', shouldDisable )
		} )

		const userAnswerItem = screen.querySelector( '.dem_add_answer_item_js' )
		if( userAnswerInput ){
			const shouldDisableInput = ! hasUserAnswer && checkedCount >= maxAnsws
			userAnswerInput.disabled = shouldDisableInput
			userAnswerItem?.classList.toggle( 'dem-disabled', shouldDisableInput )

			return
		}

		if( userAnswerItem ){
			const shouldDisableLink = checkedCount >= maxAnsws
			userAnswerItem.classList.toggle( 'dem-disabled', shouldDisableLink )

			const link = userAnswerItem.querySelector( '.dem_add_answer_link_js' )
			if( link ){
				if( shouldDisableLink ){
					link.setAttribute( 'aria-disabled', 'true' )
					link.setAttribute( 'tabindex', '-1' )
				}
				else{
					link.removeAttribute( 'aria-disabled' )
					link.removeAttribute( 'tabindex' )
				}
			}
		}
	}

	static maxAnswLimitData( screen ){
		const poll = screen.closest( Config.mainSel )
		const maxAnsws = PollState.get( poll ).maxAnsws
		if( ! maxAnsws ){
			return {}
		}

		const checkedCount = screen.querySelectorAll( 'input[type="checkbox"]:checked' ).length
		const userAnswerInput = screen.querySelector( Config.userAnswerSel )
		const hasUserAnswer = !! userAnswerInput?.value.trim()
		const isMaxReached = (checkedCount + (hasUserAnswer ? 1 : 0)) >= maxAnsws

		return {
			maxAnsws,
			checkedCount,
			userAnswerInput,
			hasUserAnswer,
			isMaxReached,
		}
	}

	static demShake( el ){
		const position = window.getComputedStyle( el ).position
		if( ! position || position === 'static' ){
			el.style.position = 'relative'
		}

		const keyframes = [
			{ left: '0px' },
			{ left: '-10px', offset: 0.2 },
			{ left: '10px', offset: 0.40 },
			{ left: '-10px', offset: 0.60 },
			{ left: '10px', offset: 0.80 },
			{ left: '0px', offset: 1 }
		]
		const timing = { duration: 500, iterations: 1, easing: 'linear' }
		el.animate( keyframes, timing )
	}

	// dots loading animation: ...
	static loadingDots( el ){
		const isInput = (el.tagName.toLowerCase() === 'input')
		const str = isInput ? el.value : el.innerHTML

		if( str.slice( -3 ) === '...' ){
			el[isInput ? 'value' : 'innerHTML'] = str.slice( 0, -3 )
		}
		else{
			el[isInput ? 'value' : 'innerHTML'] += '.'
		}

		Config.loaderTmr = setTimeout( () => Utils.loadingDots( el ), 200 )
	}

	static resetHeight( el ){
		Utils.clear_hAnim( el )
		el.getAnimations().forEach( animation => animation.cancel() )
		el.style.height = 'auto'
	}

	static animateHeight( el, toHeight, duration, onFinish ){
		const fromHeight = el.getBoundingClientRect().height
		Utils.clear_hAnim( el )

		// no animation
		if( ! duration ){
			el.style.height = toHeight + `px`
			onFinish && onFinish()
			return
		}

		// animation
		el._hAnim = el.animate(
			[{ height: `${ fromHeight }px` }, { height: `${ toHeight }px` }],
			{ duration, easing: 'ease-out', fill: 'forwards' }
		)
		el._hAnim.onfinish = () => {
			el.style.height = toHeight + `px`
			Utils.clear_hAnim( el )
			onFinish && onFinish()
		}
		el._hAnim.oncancel = () => {
			delete el._hAnim
		}
	}

	static clear_hAnim( el ){
		if( el._hAnim ){
			el._hAnim.oncancel = null
			el._hAnim.cancel()
			delete el._hAnim
		}
	}

	/**
	 * Creates HTML element from passed string.
	 *
	 * Example: create_html_element( `<div class="kama-lightbox"/>` )
	 *
	 * @param {string} str
	 * @return {HTMLElement}
	 */
	static newEl( str ){
		let div = document.createElement( 'div' )
		div.innerHTML = str.trim()

		return div.firstChild
	}

	static fadeIn( el, duration = 300 ){
		Utils.showElement( el )

		if( ! el.animate || ! duration ){
			el.style.opacity = null
			return
		}

		el.style.opacity = '0'

		const anim = el.animate(
			[{ opacity: 0 }, { opacity: 1 }],
			{ duration, easing: 'linear' }
		)
		anim.onfinish = () => el.style.opacity = null
		anim.oncancel = () => el.style.opacity = null
	}

	static showElement( el ){
		el.hidden = false
		if( el.style.display === 'none' ){
			el.style.display = null
		}
		else if( getComputedStyle( el ).display === 'none' ){
			el.style.display = 'block'
		}
	}

	static hideElement( el ){
		el.style.display = 'none'
	}

}
