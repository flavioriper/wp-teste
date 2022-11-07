/**
 * WPUF Block
 *
 * A block for embedding a wpuf partial content restriction into a post/page.
 */
( function( blocks, i18n, editor, element, components ) {

    var el = element.createElement, // function to create elements
        TextControl = components.TextControl,// text input control
        InspectorControls = editor.InspectorControls, // sidebar controls
        InnerBlocks = editor.InnerBlocks,
        __ = i18n.__;

    // WPUF Partial Content Restriction Block
    blocks.registerBlockType( 'wpuf/content-restriction', {
        title: 'WPUF Partial Content Restriction',
        icon: 'universal-access-alt',
        category: 'common',
 
        attributes: {
            roles: {
                type: 'array',
                default: [' '],
            },
            subscriptions: {
                type: 'array',
                default: [0],
            },
            restrict: {
                type: 'string',
                default: 'roles',
            }
        },

        edit: function( props ) {
            
            var blockingTypeMessage = __( 'No restriction', 'wpuf-pro' )

            if  ( 'roles' === props.attributes.restrict ) {
                blockingTypeMessage = __( 'Blocking by user role', 'wpuf-pro' )
            }

            if ( 'subscriptions' === props.attributes.restrict ) {
                blockingTypeMessage = __( 'Blocking by subscription', 'wpuf-pro' )
            }

            const blcokIcon = el('span', {
                className: 'dashicons dashicons-hidden'   
            }, '')

            const blocSubkTitle = el('p', {
                className: 'wpuf-content-restriction-sub-title'
            }, __( 'When you select Everyone option for a specific page/post, the restriction option will be applied for that specific page/post. The content of that page/post will be accessible for all the users, no matter the user logged in or not', 'wpuf-pro' ) )

            const blockDescription = el('div', {
                className: 'wpuf-block-description'
            },
                el('span', {
                    className: 'wpuf-partial-restrict-block-header'
                }, __( 'Restricted Content: ' + blockingTypeMessage, 'wpuf-pro' ) ),
                blocSubkTitle
            )
            // Set up the user role settings
            var availableRoles = [];
            var newRoles = props.attributes.roles.slice();

            _.each( wpufProBlock.roles, function ( name, key ) {
                availableRoles.push(
                    el( components.CheckboxControl, {
                        key: key,
                        label: name,
                        value: key,
                        onChange: function( value ) {
                            if ( newRoles.indexOf(key) == -1 ) {
                                newRoles.push( key );
                            } else {
                                newRoles.splice(newRoles.indexOf(key), 1)
                            }
                            props.setAttributes({ roles: newRoles });
                        },
                        checked: props.attributes.roles.indexOf(key) != -1 || 'administrator' === key ? true : false
                    })
                )
            });

            // Setup subscriptions settings
            var availableSubscriptions = [];
            var newSubscriptions = props.attributes.subscriptions.slice()

            wpufProBlock.subscriptions.forEach( subscription => {
                availableSubscriptions.push(
                    el( components.CheckboxControl, {
                        key: subscription.ID,
                        label: subscription.post_title,
                        onChange: function( value ) {
                            if ( newSubscriptions.indexOf(subscription.ID) == -1 ) {
                                newSubscriptions.push( subscription.ID );
                            } else {
                                newSubscriptions.splice(newSubscriptions.indexOf(subscription.ID), 1)
                            }
                            props.setAttributes({ subscriptions: newSubscriptions });
                        },
                        checked: props.attributes.subscriptions.indexOf(subscription.ID) != -1 ? true : false
                    })
                )
            });

            // Set restriction type
            var selected_type
            if ( 'roles' === props.attributes.restrict ) {
                selected_type = availableRoles
            }

            if ( 'subscriptions' === props.attributes.restrict ) {
                selected_type = availableSubscriptions
            }

            var restrict_type = el( InspectorControls, {}, 
                el(
                    components.PanelBody,
                    {
                        title: __( 'Display To', 'wpuf-pro' )
                    },
                    el( components.RadioControl,
                        {
                            className: 'wpuf-restrict-type',
                            options : [
                                { label: __( 'Everyone', 'wpuf-pro' ), value: 'everyone' },
                                { label: __( 'Logged in users only', 'wpuf-pro' ), value: 'roles' },
                                { label: __( 'Subscription users only', 'wpuf-pro' ), value: 'subscriptions' },
                            ],
                            onChange: ( value ) => {
                                props.setAttributes( { restrict: value } );
                            },
                            selected: props.attributes.restrict
                        }
                    ),
                    selected_type
                )    
            );

            return [ restrict_type, el(
                'div',
                { className: 'wpuf-content-restriction-block' },
               [ blockDescription, el( InnerBlocks ) ]
            )];
        },
 
        save: function( props ) {
            var subscription = props.attributes.subscriptions,
                roles = props.attributes.roles,
                restrict = ''

                if ('roles' == props.attributes.restrict) {
                    restrict = 'roles="'+ roles.toString() +'"'
                }
                if ('subscriptions' == props.attributes.restrict) {
                    restrict = 'subscriptions="'+ subscription.toString() +'"'
                }
            

            return el(
                'div',
                { className: props.className },
                
                ['[wpuf_partial_restriction '+ restrict +' ]', el( InnerBlocks.Content ), '[/wpuf_partial_restriction]']
            );
        },
    } );


} )(
    window.wp.blocks,
    window.wp.i18n,
    window.wp.editor,
    window.wp.element,
    window.wp.components
);
