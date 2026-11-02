import Utils from './Utils.mjs'
import Config from './Config.mjs'

export default class Loader {

	static setLoader( target ){
		if( Config.$loader ){
			const loaderClone = Config.$loader.cloneNode( true )
			loaderClone.style.display = 'table'

			const screen = target.closest( Config.screenSel )
			if( screen ){
				screen.append( loaderClone )
			}
		}
		else{
			Config.loaderTmr = setTimeout( () => Utils.loadingDots( target ), 50 )
		}
	}

	static unsetLoader( target ){
		if( Config.$loader ){
			const poll = target.closest( Config.mainSel )
			poll.querySelectorAll( '.dem_loader_js' ).forEach( node => node.remove() )
		}
		else{
			clearTimeout( Config.loaderTmr )
		}
	}

}
