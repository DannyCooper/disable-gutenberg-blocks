window._wpLoadBlockEditor.then( function() {
	const blocks = dgb_blocks;
	Object.keys( blocks ).forEach( function( key ) {
		const blockName = blocks[ key ];
		if ( blockName !== 'core/paragraph' && blockName && 0 !== blockName.length ) {
			wp.blocks.unregisterBlockType( blockName );
		}
	} );
} );
