window.onload = function() {
	wp.blockLibrary.registerCoreBlocks();
	const blocks = wp.blocks.getBlockTypes();
	const disabledBlocks = dgb_object.disabledBlocks;
	const nonce = dgb_object.nonce;

	jQuery( '.block-count' ).text( jQuery( blocks ).size() );

	blocks.sort( function( a, b ) {
		const textA = a.name.toUpperCase();
		const textB = b.name.toUpperCase();
		return ( textA < textB ) ? -1 : ( textA > textB ) ? 1 : 0;
	} );

	blocks.forEach( function( block ) {
		const id = block.name ? block.name : '';
		const name = block.title ? block.title : '';
		const description = block.description ? block.description : '';
		const category = block.category ? block.category : '';

		let html = '';

		let isDisabledBlock = false;

		Object.keys( disabledBlocks ).forEach( function( key ) {
			if ( disabledBlocks[ key ] === id ) {
				isDisabledBlock = true;
			}
		} );

		if ( isDisabledBlock ) {
			html += '<tr class="disabled">';
		} else {
			html += '<tr>';
		}

		html += '<th scope="row" class="check-column">';
		html += '<input type="checkbox" name="bulk-change[]" value="' + id + '"></th>';
		html += '<td class="name column-name has-row-actions column-primary" data-colname="Name">';
		html += '<strong>' + name + '</strong>';

		html += '<div class="row-actions">';

		if ( isDisabledBlock ) {
			html += '<span class="enable"><a href="?page=disable-blocks&amp;action=enable&amp;block=' + id + '&amp;_wpnonce=' + nonce + '">' + dgb_strings.enable + '</a></span>';
		} else {
			html += '<span class="disable"><a href="?page=disable-blocks&amp;action=disable&amp;block=' + id + '&amp;_wpnonce=' + nonce + '">' + dgb_strings.disable + '</a></span>';
		}

		html += '</div>';
		html += '</td>';
		html += '<td class="id column-id" data-colname="ID">' + id + '</td>';
		html += '<td class="description column-description" data-colname="Description">' + description + '</td>';
		html += '<td class="category column-category" data-colname="Category">' + category + '</td>';
		html += '</tr>';

		const table = jQuery( '.wp-list-table' );
		table.append( html );
	} );
};
