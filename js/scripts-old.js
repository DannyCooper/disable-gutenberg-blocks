window._wpLoadGutenbergEditor.then( function() {
	const blocks = dgb_blocks;
	Object.keys( blocks ).forEach( function( key ) {
		const blockName = blocks[ key ];
		if ( blockName !== 'core/paragraph' &&
				blockName &&
				0 !== blockName.length &&
				undefined !== wp.blocks.getBlockType( blockName ) ) {
			wp.blocks.unregisterBlockType( blockName );
		}
	} );
} );
