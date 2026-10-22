/**
 * Brizz Tickets block — editor side. Plain JS on the wp.* globals (no build
 * step, nothing to install to change a label). The block is dynamic: what
 * the visitor sees is rendered by PHP through the same renderer as the
 * shortcode, so this file only collects attributes and shows an honest
 * placeholder — the real cards need the loader, which does not run inside
 * the editor.
 */
( function ( wp ) {
	if ( ! wp || ! wp.blocks || ! wp.element || ! wp.blockEditor || ! wp.components ) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var __ = wp.i18n.__;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var PanelBody = wp.components.PanelBody;
	var SelectControl = wp.components.SelectControl;
	var TextControl = wp.components.TextControl;

	function shortId( uuid ) {
		return uuid ? uuid.slice( 0, 8 ) + '…' : '';
	}

	function describe( a ) {
		if ( a.source === 'event' ) {
			return a.uuid
				? __( 'One event', 'brizz-tickets' ) + ' · ' + shortId( a.uuid )
				: __( 'One event — add its id in the sidebar', 'brizz-tickets' );
		}
		if ( a.source === 'events' ) {
			return a.uuids
				? __( 'Several events', 'brizz-tickets' )
				: __( 'Several events — add their ids in the sidebar', 'brizz-tickets' );
		}
		if ( a.source === 'organiser' ) {
			return a.uuid
				? __( 'All your upcoming events', 'brizz-tickets' ) + ' · ' + shortId( a.uuid )
				: __( 'All your upcoming events — add your organiser id in the sidebar', 'brizz-tickets' );
		}
		return __( 'What Settings → Brizz Tickets says (all your upcoming events, or the one event you set there)', 'brizz-tickets' );
	}

	wp.blocks.registerBlockType( 'brizz/tickets', {
		edit: function ( props ) {
			var a = props.attributes;
			var set = props.setAttributes;
			var blockProps = useBlockProps( { className: 'brizz-tickets-editor' } );
			var needsOne = a.source === 'event' || a.source === 'organiser';

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'What to show', 'brizz-tickets' ), initialOpen: true },
						el( SelectControl, {
							label: __( 'Source', 'brizz-tickets' ),
							value: a.source,
							options: [
								{ label: __( 'From settings (recommended)', 'brizz-tickets' ), value: 'default' },
								{ label: __( 'All my upcoming events', 'brizz-tickets' ), value: 'organiser' },
								{ label: __( 'One event', 'brizz-tickets' ), value: 'event' },
								{ label: __( 'Several events', 'brizz-tickets' ), value: 'events' },
							],
							onChange: function ( v ) { set( { source: v } ); },
						} ),
						needsOne && el( TextControl, {
							label: a.source === 'event' ? __( 'Event id', 'brizz-tickets' ) : __( 'Organiser id', 'brizz-tickets' ),
							help: __( 'From brizz.me → Embed on your site: the value of data-event-uuid or data-organiser in the snippet.', 'brizz-tickets' ),
							value: a.uuid,
							onChange: function ( v ) { set( { uuid: v.trim() } ); },
						} ),
						a.source === 'events' && el( TextControl, {
							label: __( 'Event ids, comma-separated', 'brizz-tickets' ),
							help: __( 'Up to ten.', 'brizz-tickets' ),
							value: a.uuids,
							onChange: function ( v ) { set( { uuids: v } ); },
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Layout', 'brizz-tickets' ), initialOpen: false },
						el( SelectControl, {
							label: __( 'Layout', 'brizz-tickets' ),
							value: a.mode,
							options: [
								{ label: __( 'Default from settings', 'brizz-tickets' ), value: '' },
								{ label: __( 'Card', 'brizz-tickets' ), value: 'card' },
								{ label: __( 'Card grid', 'brizz-tickets' ), value: 'card-grid' },
								{ label: __( 'Button', 'brizz-tickets' ), value: 'button' },
							],
							onChange: function ( v ) { set( { mode: v } ); },
						} ),
						a.mode === 'button' && el( TextControl, {
							label: __( 'Button text', 'brizz-tickets' ),
							placeholder: __( 'Get tickets', 'brizz-tickets' ),
							value: a.label,
							onChange: function ( v ) { set( { label: v } ); },
						} )
					)
				),
				el(
					'div',
					blockProps,
					el( 'strong', { className: 'brizz-tickets-editor__title' }, __( 'Brizz Tickets', 'brizz-tickets' ) ),
					el( 'div', { className: 'brizz-tickets-editor__what' }, describe( a ) ),
					el( 'div', { className: 'brizz-tickets-editor__hint' }, __( 'The cards and the checkout appear on the published page.', 'brizz-tickets' ) )
				)
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp );
