(function(wp) {
    let el = wp.element.createElement,
        registerBlockType = wp.blocks.registerBlockType,
        withSelect = wp.data.withSelect,
        BlockControls = wp.editor.BlockControls,
        AlignmentToolbar = wp.editor.AlignmentToolbar,
        InspectorControls = wp.blocks.InspectorControls,
        ServerSideRender = wp.components.ServerSideRender,
        __ = wp.i18n.__,
        Text = wp.components.TextControl,
        aysSelect = wp.components.SelectControl;
    let iconEl = el(
        'svg', {
            width: 24,
            height: 24,
            viewBox: '0 0 20 20',
            style: {
                width: '24px',
                height: '24px'
            }
        },
        el(
            'path', {
                d: "M17.431,2.156h-3.715c-0.228,0-0.413,0.186-0.413,0.413v6.973h-2.89V6.687c0-0.229-0.186-0.413-0.413-0.413H6.285c-0.228,0-0.413,0.184-0.413,0.413v6.388H2.569c-0.227,0-0.413,0.187-0.413,0.413v3.942c0,0.228,0.186,0.413,0.413,0.413h14.862c0.228,0,0.413-0.186,0.413-0.413V2.569C17.844,2.342,17.658,2.156,17.431,2.156 M5.872,17.019h-2.89v-3.117h2.89V17.019zM9.587,17.019h-2.89V7.1h2.89V17.019z M13.303,17.019h-2.89v-6.651h2.89V17.019z M17.019,17.019h-2.891V2.982h2.891V17.019z",
                fill: '#1db3c9'
            }
        )
    );

    let pollMakerMapSelectToProps = function(select) {
        if (select('core/blocks').getBlockType('poll-maker/poll').attributes.idner &&
            (select('core/blocks').getBlockType('poll-maker/poll').attributes.idner != undefined ||
                select('core/blocks').getBlockType('poll-maker/poll').attributes.idner != null)) {
            return {
                polls: select('core/blocks').getBlockType('poll-maker/poll').attributes.idner,
                metaFieldValue: select('core/editor')
                    .getEditedPostAttribute('meta')['sidebar_plugin_meta_block_field']
            };
        } else {
            return {
                polls: __("Something went wrong please reload page")
            };
        }
    }

    let pollMakerMetaBlockField = function(props) {
        if (!props.polls) {
            return __("Loading...");
        }
        if (typeof props.polls != "object") {
            return props.polls;
        }

        if (props.polls.length === 0) {
            return __("There are no polls yet");
        }
        let pollner = [];
        pollner.push({
            label: __("-Select Poll-"),
            value: ''
        });
        for (let i in props.polls) {
            let pollData = {
                value: props.polls[i].id,
                label: props.polls[i].title,
            }
            pollner.push(pollData)
        }
        let aysElement = el(
            aysSelect, {
                className: 'ays_poll_maker_block_select',
                label: 'Select Poll for adding to post content',
                value: props.metaFieldValue,
                onChange: function(content) {
                    props.shortcode = "[ays_poll id=" + content + "]";
                    props.metaFieldValue = parseInt(content);
                    let block = wp.blocks.createBlock('poll-maker/poll', {
                        shortcode: "[ays_poll id=" + content + "]",
                        polls: props.polls,
                        metaFieldValue: parseInt(content)
                    });
                    wp.data.dispatch('core/editor').insertBlocks(block);
                },
                options: pollner
            }
        );
        return el(
            "div", {
                className: 'ays_poll_maker_block_container',
                key: "inspector",
            },
            aysElement
        );
    }
    // let pollMakerMetaBlockFieldWithData = withSelect(pollMakerMapSelectToProps)(pollMakerMetaBlockField);
    /*if (wp.plugins) {
        wp.plugins.registerPlugin('poll-maker-sidebar', {
            render: function() {
                return el(wp.editPost.PluginSidebar, {
                        name: 'poll-maker',
                        icon: iconEl,
                        title: 'Poll Maker',
                    },
                    el('div', {
                            className: 'poll-maker-sidebar-content'
                        },
                        el(pollMakerMetaBlockFieldWithData)
                    )
                );
            }
        });
    }*/
    registerBlockType('poll-maker/poll', {
        title: __('Poll Maker'),
        category: 'common',
        icon: iconEl,
        // transforms: {
        //     from: [{
        //         type: "shortcode",
        //         tag: ["ays_poll"],
        //         attributes: {
        //             module_id: {
        //                 type: "string",
        //                 shortcode: function(e) {
        //                     return e.attributes.metaFieldValue
        //                 }
        //             }
        //         }
        //     }]
        // },
        edit: withSelect(function(select) {
            if (select('core/blocks').getBlockType('poll-maker/poll').attributes.idner &&
                (select('core/blocks').getBlockType('poll-maker/poll').attributes.idner != undefined ||
                    select('core/blocks').getBlockType('poll-maker/poll').attributes.idner != null)) {
                return {
                    polls: select('core/blocks').getBlockType('poll-maker/poll').attributes.idner
                };
            } else {
                return {
                    polls: __("Something went wrong please reload page")
                };
            }
        })(function(props) {
            if (!props.polls) {
                return __("Loading...");
            }
            if (typeof props.polls != "object") {
                return props.polls;
            }

            if (props.polls.length === 0) {
                return __("There are no polls yet");
            }

            var status = 0;
            if(props.attributes.metaFieldValue > 0){            
                status = 1;
            }

            let pollner = [];
            pollner.push({
                label: __("-Select Poll-"),
                value: ''
            });
            for (let i in props.polls) {
                let pollData = {
                    value: props.polls[i].id,
                    label: props.polls[i].title,
                }
                pollner.push(pollData)
            }
            let aysElement = el(
                aysSelect, {
                    className: 'ays_poll_maker_block_select',
                    label: __("Select Poll"),
                    value: props.attributes.metaFieldValue,
                    onChange: function(content) {
                        var c = content;
                        if(isNaN(content)){
                            c = '';
                        }
                        status = 1;
                        wp.data.dispatch('core/editor').updateBlockAttributes(props.clientId, {
                            shortcode: "[ays_poll id=" + c + "]",
                            metaFieldValue: parseInt(c)
                        });
                    },
                    options: pollner
                }
            );
            var aysElement2 = el(
            aysSelect, {
                className: 'ays_poll_maker_block_select',
                label: '',
                value: props.attributes.metaFieldValue,
                onChange: function( content ) {
                    var c = content;
                    if(isNaN(content)){
                        c = '';
                    }
                    wp.data.dispatch( 'core/editor' ).updateBlockAttributes( props.clientId, {
                        shortcode: "[ays_poll id="+c+"]",
                        metaFieldValue: parseInt(c)
                    } );
                    
                    // return 
                },
                options: pollner
            },
            el(ServerSideRender, {
                key: "editable",
                block: "poll-maker/poll",
                attributes:  props.attributes
            })
        );
        var res = el(
            wp.element.Fragment,
            {},
            el(
                BlockControls,
                props
            ),
            el(
                wp.editor.InspectorControls,
                {},
                el(
                    wp.components.PanelBody,
                    {},
                    el(
                        "div",
                        {
                            className: 'ays_poll_maker_block_container',
                            key: "inspector",
                        },
                        aysElement
                    )
                )
            ),
            aysElement2,
            el(ServerSideRender, {
                key: "editable",
                block: "poll-maker/poll",
                attributes:  props.attributes
            })
        );
        var res2 = el(
                wp.element.Fragment, {},
                el(
                    BlockControls,
                    props
                ),
                el(
                    wp.editor.InspectorControls, {},
                    el(
                        wp.components.PanelBody, {},
                        el(
                            "div", {
                                className: 'ays_poll_maker_block_container',
                                key: "inspector",
                            },
                            aysElement
                        )
                    )
                ),
                el(ServerSideRender, {
                    key: "editable",
                    block: "poll-maker/poll",
                    attributes: props.attributes
                })
            );
        
            if(status == 1){
                return res2;
            }else{
                return res;
            }

        }),

        save: function(e) {
            var t = e.attributes,
                n = t.metaFieldValue;
            return n ? '[ays_poll id="'+ n +'"]' : null
        }
    });
})(wp);