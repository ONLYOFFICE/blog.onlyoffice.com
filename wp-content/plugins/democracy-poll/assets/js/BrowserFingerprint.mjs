export default class BrowserFingerprint {

	static #promise

	static #components = {
		canvas:               () => BrowserFingerprint.#canvas(),
		screen_size:          () => BrowserFingerprint.#screenSize(),
		webgl:                () => BrowserFingerprint.#webGl(),
		device_pixel_ratio:   () => BrowserFingerprint.#value( () => window.devicePixelRatio ),
		timezone:             () => BrowserFingerprint.#value( () => Intl.DateTimeFormat().resolvedOptions().timeZone ),
		device_memory:        () => BrowserFingerprint.#value( () => navigator.deviceMemory ),
		hardware_concurrency: () => BrowserFingerprint.#value( () => navigator.hardwareConcurrency ),
		languages:            () => BrowserFingerprint.#value( () => navigator.languages.join( ',' ) ),
		max_touch_points:     () => BrowserFingerprint.#value( () => navigator.maxTouchPoints ),
		platform:             () => BrowserFingerprint.#value( () => navigator.platform ),
		color_depth:          () => BrowserFingerprint.#value( () => screen.colorDepth ),
		//pixel_depth:          () => BrowserFingerprint.#value( () => screen.pixelDepth ),
		//user_agent:           () => BrowserFingerprint.#value( () => navigator.userAgent ),
	}

	static get(){
		return BrowserFingerprint.#promise ??= BrowserFingerprint.#create()
	}

	static async #create(){
		try{
			const components = Object.fromEntries(
				Object.entries( BrowserFingerprint.#components )
					.map( ( [ name, getValue ] ) => [ name, getValue() ] )
			)

			return await BrowserFingerprint.#hash( JSON.stringify( components ) )
		}
		catch( error ){
			console.warn( 'Democracy: Browser fingerprint calculation failed', error )
			return ''
		}
	}

	static #value( getter ){
		try{
			return String( getter() ?? '' )
		}
		catch( error ){
			return ''
		}
	}

	static #screenSize(){
		return BrowserFingerprint.#value( () => {
			const shortSide = Math.min( screen.width, screen.height )
			const longSide = Math.max( screen.width, screen.height )

			return `${shortSide}x${longSide}`
		} )
	}

	static #canvas(){
		return BrowserFingerprint.#value( () => {
			const canvas = document.createElement( 'canvas' )
			canvas.width = 240
			canvas.height = 60
			const context = canvas.getContext( '2d' )
			if( ! context ){
				return ''
			}

			context.textBaseline = 'alphabetic'
			context.font = '16px Arial'
			context.fillStyle = '#f60'
			context.fillRect( 10, 10, 100, 30 )
			context.fillStyle = '#069'
			context.fillText( 'Democracy Poll 🗳️', 4, 28 )
			context.globalCompositeOperation = 'multiply'
			context.fillStyle = 'rgba(102, 204, 0, 0.7)'
			context.beginPath()
			context.arc( 170, 25, 20, 0, Math.PI * 2 )
			context.fill()

			return canvas.toDataURL()
		} )
	}

	static #webGl(){
		return BrowserFingerprint.#value( () => {
			const canvas = document.createElement( 'canvas' )
			const gl = canvas.getContext( 'webgl' ) || canvas.getContext( 'experimental-webgl' )
			if( ! gl ){
				return ''
			}

			const debugInfo = gl.getExtension( 'WEBGL_debug_renderer_info' )
			const vendor = debugInfo
				? gl.getParameter( debugInfo.UNMASKED_VENDOR_WEBGL )
				: gl.getParameter( gl.VENDOR )
			const renderer = debugInfo
				? gl.getParameter( debugInfo.UNMASKED_RENDERER_WEBGL )
				: gl.getParameter( gl.RENDERER )

			return `${vendor}|${renderer}`
		} )
	}

	static async #hash( value ){
		if( window.crypto?.subtle && window.TextEncoder ){
			try{
				const bytes = new TextEncoder().encode( value )
				const digest = await crypto.subtle.digest( 'SHA-256', bytes )

				return Array.from( new Uint8Array( digest ), byte => byte.toString( 16 ).padStart( 2, '0' ) ).join( '' )
			}
			catch{}
		}

		let result = ''
		for( let seed = 0; seed < 8; seed++ ){
			let hash = 0x811c9dc5 ^ seed
			for( let index = 0; index < value.length; index++ ){
				hash = Math.imul( hash ^ value.charCodeAt( index ), 0x01000193 )
			}
			hash ^= hash >>> 16
			hash = Math.imul( hash, 0x85ebca6b )
			hash ^= hash >>> 13
			hash = Math.imul( hash, 0xc2b2ae35 )
			hash ^= hash >>> 16
			result += (hash >>> 0).toString( 16 ).padStart( 8, '0' )
		}

		return result
	}

}
