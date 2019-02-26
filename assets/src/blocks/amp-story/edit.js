/* global ReactDOM */

/**
 * External dependencies
 */
import uuid from 'uuid/v4';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	InnerBlocks,
	PanelColorSettings,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/editor';
import { Component, Fragment } from '@wordpress/element';
import {
	PanelBody,
	Button,
	BaseControl,
	FocalPointPicker,
} from '@wordpress/components';
import { withSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import BlockNavigation from './block-navigation';
import { ALLOWED_BLOCKS } from '../../helpers';
import { ALLOWED_MEDIA_TYPES, IMAGE_BACKGROUND_TYPE, VIDEO_BACKGROUND_TYPE } from './constants';

const TEMPLATE = [
	[ 'core/paragraph' ],
];

class EditPage extends Component {
	constructor( props ) {
		// Call parent constructor.
		super( props );

		if ( ! props.attributes.id ) {
			this.props.setAttributes( { id: uuid() } );
		}

		this.onSelectMedia = this.onSelectMedia.bind( this );
	}

	maybeAddBlockNavigation() {
		// If no blocks are selected or if it's the current page, change the view.
		if ( this.props.showBlockNavigation ) {
			const editLayout = document.getElementsByClassName( 'edit-post-layout' );
			if ( editLayout.length ) {
				const blockNav = document.getElementById( 'amp-root-navigation' );
				if ( ! blockNav ) {
					const navWrapper = document.createElement( 'div' );
					navWrapper.id = 'amp-root-navigation';
					editLayout[ 0 ].appendChild( navWrapper );
				}
				ReactDOM.render(
					<div key="layerManager" className="editor-selectors">
						<BlockNavigation />
					</div>,
					document.getElementById( 'amp-root-navigation' )
				);
			}
		}
	}

	componentDidMount() {
		this.maybeAddBlockNavigation();
	}

	componentDidUpdate() {
		// @todo Check if there is a better way to do this without calling it on both componentDidMount and componentDidUpdate.
		this.maybeAddBlockNavigation();
	}

	onSelectMedia( media ) {
		if ( ! media || ! media.url ) {
			this.props.setAttributes( { mediaUrl: undefined, mediaId: undefined, mediaType: undefined } );
			return;
		}

		let mediaType;

		// For media selections originated from a file upload.
		if ( media.media_type ) {
			if ( media.media_type === VIDEO_BACKGROUND_TYPE ) {
				mediaType = VIDEO_BACKGROUND_TYPE;
			} else {
				mediaType = IMAGE_BACKGROUND_TYPE;
			}
		} else {
			// For media selections originated from existing files in the media library.
			if (
				media.type !== IMAGE_BACKGROUND_TYPE &&
				media.type !== VIDEO_BACKGROUND_TYPE
			) {
				return;
			}

			mediaType = media.type;
		}

		this.props.setAttributes( {
			mediaUrl: media.url,
			mediaId: media.id,
			mediaType: mediaType,
		} );
	}

	render() {
		const props = this.props;
		const { attributes, media } = props;

		const { backgroundColor, mediaId, mediaType, mediaUrl, focalPoint } = attributes;

		const instructions = <p>{ __( 'To edit the background image or video, you need permission to upload media.', 'amp' ) }</p>;

		const style = {
			backgroundColor: backgroundColor,
			backgroundImage: IMAGE_BACKGROUND_TYPE === mediaType && mediaUrl ? `url(${ mediaUrl })` : undefined,
			backgroundPosition: IMAGE_BACKGROUND_TYPE === mediaType && focalPoint ? `${ focalPoint.x * 100 }% ${ focalPoint.y * 100 }%` : 'cover',
			backgroundRepeat: 'no-repeat',
		};

		return (
			<Fragment>
				<InspectorControls key="controls">
					<PanelColorSettings
						title={ __( 'Color Settings', 'amp' ) }
						initialOpen={ false }
						colorSettings={ [
							{
								value: backgroundColor,
								onChange: ( value ) => this.props.setAttributes( { backgroundColor: value } ),
								label: __( 'Background Color', 'amp' ),
							},
						] }
					/>
					<PanelBody title={ __( 'Background Media', 'amp' ) }>
						<Fragment>
							<BaseControl>
								<MediaUploadCheck fallback={ instructions }>
									<MediaUpload
										onSelect={ this.onSelectMedia }
										allowedTypes={ ALLOWED_MEDIA_TYPES }
										value={ mediaId }
										render={ ( { open } ) => (
											<Button isDefault isLarge onClick={ open } className={ 'editor-amp-story-page-background' }>
												{ mediaUrl ? __( 'Edit Media', 'amp' ) : __( 'Upload Media', 'amp' ) }
											</Button>
										) }
									/>
								</MediaUploadCheck>
								{ !! mediaId &&
								<MediaUploadCheck>
									<Button onClick={ () => this.props.setAttributes( { mediaUrl: undefined, mediaId: undefined, mediaType: undefined } ) } isLink isDestructive>
										{ VIDEO_BACKGROUND_TYPE === mediaType ? __( 'Remove video', 'amp' ) : __( 'Remove image', 'amp' ) }
									</Button>
								</MediaUploadCheck>
								}
							</BaseControl>
							{ mediaUrl && (
								<Fragment>
									{ /* Note: FocalPointPicker is only available in Gutenberg 5.1+ */ }
									{ IMAGE_BACKGROUND_TYPE === mediaType && FocalPointPicker && (
										<FocalPointPicker
											label={ __( 'Focal Point Picker', 'amp' ) }
											url={ mediaUrl }
											value={ focalPoint }
											onChange={ ( value ) => this.props.setAttributes( { focalPoint: value } ) }
										/>
									) }
								</Fragment>
							) }
						</Fragment>
					</PanelBody>
				</InspectorControls>
				<div key="contents" style={ style } className={ 'editor-amp-story-page-wrap' }>
					{ /* todo: show poster image as background-image instead */ }
					{ VIDEO_BACKGROUND_TYPE === mediaType && media && (
						<video autoPlay muted loop className={ 'editor-amp-story-page-video' }>
							<source src={ mediaUrl } type={ media.mime_type } />
						</video>
					) }
					<InnerBlocks template={ TEMPLATE } allowedBlocks={ ALLOWED_BLOCKS } />
				</div>
			</Fragment>
		);
	}
}

export default withSelect( ( select, props ) => {
	const { mediaId } = props.attributes;
	const { getMedia } = select( 'core' );
	const {
		hasSelectedInnerBlock,
		getSelectedBlockClientId,
	} = select( 'core/editor' );

	let showBlockNavigation = false;

	if ( ! getSelectedBlockClientId() || props.clientId === getSelectedBlockClientId() || hasSelectedInnerBlock( props.clientId, true ) ) {
		showBlockNavigation = true;
	}

	return {
		media: mediaId ? getMedia( mediaId ) : null,
		showBlockNavigation,
	};
} )( EditPage );
