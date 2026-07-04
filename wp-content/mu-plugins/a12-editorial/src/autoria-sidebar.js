import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { useSelect, useDispatch } from '@wordpress/data';
import { useState, useEffect } from '@wordpress/element';
import { FormTokenField } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

/**
 * Campo de seleção de Pessoas (CPT person) com busca e tokens,
 * integrado ao painel lateral do editor de blocos.
 *
 * @param {string} label   - Label exibido acima do campo
 * @param {string} restKey - Chave do campo no REST da postagem (acf_author_ids | acf_editor_ids)
 */
function PessoaField( { label, restKey } ) {
	const rawIds     = useSelect( ( s ) => s( 'core/editor' ).getEditedPostAttribute( restKey ) );
	const ids        = Array.isArray( rawIds ) ? rawIds : [];
	const { editPost } = useDispatch( 'core/editor' );

	const [ nameToId,    setNameToId    ] = useState( {} );
	const [ suggestions, setSuggestions ] = useState( [] );

	const idsKey = ids.slice().sort().join( ',' );

	// Ao montar ou quando os IDs mudarem, busca os nomes correspondentes
	useEffect( () => {
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
			.then( ( posts ) => {
				const map = {};
				posts.forEach( ( p ) => { map[ p.title.rendered ] = p.id; } );
				setNameToId( map );
			} )
			.catch( () => {} );
	}, [ idsKey ] ); // eslint-disable-line react-hooks/exhaustive-deps

	const selectedNames = ids
		.map( ( id ) => Object.keys( nameToId ).find( ( n ) => nameToId[ n ] === id ) )
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
			.then( ( results ) => {
				const map = { ...nameToId };
				results.forEach( ( p ) => { map[ p.title.rendered ] = p.id; } );
				setNameToId( map );
				setSuggestions( results.map( ( p ) => p.title.rendered ) );
			} )
			.catch( () => {} );
	}

	function handleChange( names ) {
		const newIds = names.map( ( n ) => nameToId[ n ] ).filter( Boolean );
		editPost( { [ restKey ]: newIds } );
	}

	return (
		<>
			<p style={ {
				fontSize:      11,
				textTransform: 'uppercase',
				fontWeight:    600,
				color:         '#1e1e1e',
				margin:        '0 0 6px',
				letterSpacing: '0.04em',
			} }>
				{ label }
			</p>

			<FormTokenField
				value={ selectedNames }
				suggestions={ suggestions }
				onInputChange={ handleInputChange }
				onChange={ handleChange }
				__experimentalExpandOnFocus
				__next40pxDefaultSize
			/>
		</>
	);
}

/**
 * Painel lateral "Autoria e Redação" — aparece apenas em post_type = post.
 */
function AutoriaSidebar() {
	const postType = useSelect( ( s ) => s( 'core/editor' ).getCurrentPostType() );
	if ( postType !== 'post' ) return null;

	return (
		<PluginDocumentSettingPanel
			name="a12-autoria-redacao"
			title="Autoria e Redação"
			icon="admin-users"
		>
			<PessoaField label="AUTORES"   restKey="acf_author_ids" />
			<div style={ { height: 12 } } />
			<PessoaField label="REDATORES" restKey="acf_editor_ids" />
		</PluginDocumentSettingPanel>
	);
}

registerPlugin( 'a12-autoria-sidebar', { render: AutoriaSidebar } );
