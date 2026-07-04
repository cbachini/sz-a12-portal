/* jshint esversion: 9 */
/**
 * Painel lateral "Autoria e Redação" para o editor de blocos do WordPress.
 * Usa apenas globals wp.* — sem webpack, sem JSX, sem externalizações.
 *
 * @package A12 Editorial
 */
( function () {
	'use strict';

	var registerPlugin          = wp.plugins.registerPlugin;
	var PluginDocumentSettingPanel = wp.editor.PluginDocumentSettingPanel;
	var useSelect               = wp.data.useSelect;
	var useDispatch             = wp.data.useDispatch;
	var useState                = wp.element.useState;
	var useEffect               = wp.element.useEffect;
	var FormTokenField          = wp.components.FormTokenField;
	var apiFetch                = wp.apiFetch;
	var addQueryArgs            = wp.url.addQueryArgs;
	var el                      = wp.element.createElement;
	var Fragment                = wp.element.Fragment;

	/**
	 * Campo de busca + tokens para selecionar Pessoas (CPT person).
	 *
	 * @param {string} props.label   - Label exibido acima do campo
	 * @param {string} props.restKey - Chave REST da postagem (acf_author_ids | acf_editor_ids)
	 */
	function PessoaField( props ) {
		var label   = props.label;
		var restKey = props.restKey;

		var rawIds = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( restKey );
		} );
		var ids = Array.isArray( rawIds ) ? rawIds : [];

		var editPostDispatch = useDispatch( 'core/editor' );
		var editPost = editPostDispatch.editPost || editPostDispatch;

		var nameToIdState    = useState( {} );
		var nameToId         = nameToIdState[0];
		var setNameToId      = nameToIdState[1];

		var suggestionsState = useState( [] );
		var suggestions      = suggestionsState[0];
		var setSuggestions   = suggestionsState[1];

		var idsKey = ids.slice().sort().join( ',' );

		useEffect( function () {
			if ( ! ids.length ) {
				setNameToId( {} );
				return;
			}
			apiFetch( {
				path: addQueryArgs( '/wp/v2/pessoas', {
					include:  ids,
					per_page: 50,
					_fields:  'id,title',
				} ),
			} )
				.then( function ( posts ) {
					var map = {};
					posts.forEach( function ( p ) {
						map[ p.title.rendered ] = p.id;
					} );
					setNameToId( map );
				} )
				.catch( function () {} );
		}, [ idsKey ] ); // eslint-disable-line

		var selectedNames = ids
			.map( function ( id ) {
				return Object.keys( nameToId ).find( function ( n ) {
					return nameToId[ n ] === id;
				} );
			} )
			.filter( Boolean );

		function handleInputChange( query ) {
			if ( ! query || query.length < 2 ) {
				setSuggestions( [] );
				return;
			}
			apiFetch( {
				path: addQueryArgs( '/wp/v2/pessoas', {
					search:   query,
					per_page: 20,
					_fields:  'id,title',
				} ),
			} )
				.then( function ( results ) {
					var map = Object.assign( {}, nameToId );
					results.forEach( function ( p ) {
						map[ p.title.rendered ] = p.id;
					} );
					setNameToId( map );
					setSuggestions( results.map( function ( p ) { return p.title.rendered; } ) );
				} )
				.catch( function () {} );
		}

		function handleChange( names ) {
			var newIds = names
				.map( function ( n ) { return nameToId[ n ]; } )
				.filter( Boolean );
			var patch = {};
			patch[ restKey ] = newIds;
			if ( typeof editPost === 'function' ) {
				editPost( patch );
			}
		}

		return el(
			Fragment,
			null,
			el( 'p', {
				style: {
					fontSize:      '11px',
					textTransform: 'uppercase',
					fontWeight:    '600',
					color:         '#1e1e1e',
					margin:        '0 0 6px',
					letterSpacing: '0.04em',
				},
			}, label ),
			el( FormTokenField, {
				value:                    selectedNames,
				suggestions:              suggestions,
				onInputChange:            handleInputChange,
				onChange:                 handleChange,
				__experimentalExpandOnFocus: true,
				__next40pxDefaultSize:    true,
			} )
		);
	}

	/**
	 * Painel lateral raiz — renderiza apenas em post_type = post.
	 */
	function AutoriaSidebar() {
		var postType = useSelect( function ( select ) {
			return select( 'core/editor' ).getCurrentPostType();
		} );

		console.log( '[A12] AutoriaSidebar render — postType:', postType );

		// Aguarda o editor carregar o tipo de post antes de decidir
		if ( postType && postType !== 'post' ) {
			return null;
		}

		return el(
			PluginDocumentSettingPanel,
			{
				name:  'a12-autoria-redacao',
				title: 'Autoria e Redação',
				icon:  'admin-users',
			},
			el( PessoaField, { label: 'AUTORES',   restKey: 'acf_author_ids' } ),
			el( 'div', { style: { height: '12px' } } ),
			el( PessoaField, { label: 'REDATORES', restKey: 'acf_editor_ids' } )
		);
	}

	console.log( '[A12] Registrando plugin a12-autoria-sidebar' );
	registerPlugin( 'a12-autoria-sidebar', { render: AutoriaSidebar } );
} )();
