
document.addEventListener( 'DOMContentLoaded', function(){

	tinymce.PluginManager.add( 'demTiny', function( editor ){

		editor.addCommand( 'demTinyInsert', function(){

			let pollID = + prompt( tinymce.translate( 'Insert Poll ID' ) )

			if( pollID > 0 ){
				editor.insertContent( '[democracy id=' + pollID + ']' )
			}
		} )

		editor.addButton( 'demTiny', {
			text   : false,
			tooltip: tinymce.translate( 'Insert Poll of Democracy' ),
			icon   : 'dem dashicons-before dashicons-megaphone',
			onclick: function(){
				tinyMCE.activeEditor.execCommand( 'demTinyInsert' )
			}
		} )

	} )

} )
