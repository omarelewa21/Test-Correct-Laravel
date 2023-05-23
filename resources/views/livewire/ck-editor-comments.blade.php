{{--<div class="flex-col flex w-full">--}}
{{--    <style type="text/css">--}}
{{--        #container {--}}
{{--            /* To create the column layout. */--}}
{{--            display: flex;--}}

{{--            /* To make the container relative to its children. */--}}
{{--            position: relative;--}}
{{--        }--}}

{{--        #container .ck.ck-editor {--}}
{{--            /* To stretch the editor to max 700px--}}
{{--                (just to look nice for this example but it can be any size). */--}}
{{--            width: 100%;--}}
{{--            max-width: 700px;--}}
{{--        }--}}

{{--        #sidebar {--}}
{{--            /* Set some size for the sidebar (it can be any). */--}}
{{--            min-width: 300px;--}}

{{--            /* Add some distance. */--}}
{{--            padding: 0 10px;--}}
{{--        }--}}
{{--    </style>--}}

{{--    --}}{{-- Be like water. --}}
{{--    <div class="w-full flex min-h-[250px]">--}}
{{--        {{ $answer }}--}}
{{--    </div>--}}
{{--    <div id="container">--}}
{{--        <div id="editor"></div>--}}
{{--        <div id="sidebar"></div>--}}
{{--    </div>--}}

{{--    <div class="flex w-full flex-col">--}}

{{--    </div>--}}

{{--    <div class="w-full">--}}
{{--        <div id="container">--}}
{{--            <div id="editor">--}}
{{--                <x-input.rich-textarea class="w-full" editorId="comment-test-editor-id" wire:model="answer" type="comment-test"></x-input.rich-textarea>--}}
{{--            </div>--}}
{{--            <div id="sidebar"></div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
<div style="margin: 20px;"
     wire:ignore
x-init="
        appData = {
            // Users data.
            users: @js($users),

            // The ID of the current user.
            userId: '1486',

        };

        class CommentsAdapter {
            constructor( editor ) {
                this.editor = editor;
            }

            static get requires() {
                return [ 'CommentsRepository' ];
            }

            init() {
                console.log(appData.users);
                const usersPlugin = this.editor.plugins.get( 'Users' );
                const commentsRepositoryPlugin = this.editor.plugins.get( 'CommentsRepository' );

                // Load the users data.
                for ( const user of appData.users ) {
                    usersPlugin.addUser( user );
                }

                // Set the current user.
                usersPlugin.defineMe( appData.userId );

                // Set the adapter on the `CommentsRepository#adapter` property.
                commentsRepositoryPlugin.adapter = {
                    addComment( data ) {
                        console.log( 'Comment added', data );


                        $wire.call('addComment', data);

                        // Write a request to your database here. The returned `Promise`
                        // should be resolved when the request has finished.
                        // When the promise resolves with the comment data object, it
                        // will update the editor comment using the provided data.
                        return Promise.resolve( {
                            createdAt: new Date()       // Should be set on the server side.
                        } );
                    },

                    updateComment( data ) {
                        console.log( 'Comment updated', data );

                        $wire.call('updateComment', data);


                        // Write a request to your database here. The returned `Promise`
                        // should be resolved when the request has finished.
                        return Promise.resolve();
                    },

                    removeComment( data ) {
                        console.log( 'Comment removed', data );

                        $wire.call('removeComment', data);


                        // Write a request to your database here. The returned `Promise`
                        // should be resolved when the request has finished.
                        return Promise.resolve();
                    },

                    addCommentThread( data ) {
                        console.log( 'Comment thread added', data );

                        $wire.call('addCommentThread', data);

                        // Write a request to your database here. The returned `Promise`
                        // should be resolved when the request has finished.
                        return Promise.resolve( {
                            threadId: data.threadId,
                            comments: data.comments.map( ( comment ) => ( { commentId: comment.commentId, createdAt: new Date() } ) ) // Should be set on the server side.
                        } );
                    },

                    async getCommentThread( data ) {
                        console.log( 'Getting comment thread', data );

                        let temp = await $wire.call('getCommentThread', data);

                        console.log(temp);
                        return temp;
                    },

                    updateCommentThread( data ) {
                        console.log( 'Comment thread updated', data );

                        $wire.call('updateCommentThread', data);

                        // Write a request to your database here. The returned `Promise`
                        // should be resolved when the request has finished.
                        return Promise.resolve();
                    },

                    resolveCommentThread( data ) {
                        console.log( 'Comment thread resolved', data );

                        $wire.call('resolveCommentThread', data);

                        // Write a request to your database here. The returned `Promise`
                        // should be resolved when the request has finished.
                        return Promise.resolve( {
                            resolvedAt: new Date(), // Should be set on the server side.
                            resolvedBy: usersPlugin.me.id // Should be set on the server side.
                        } );
                    },

                    reopenCommentThread( data ) {
                        console.log( 'Comment thread reopened', data );

                        $wire.call('resolveCommentThread', data);

                        // Write a request to your database here. The returned `Promise`
                        // should be resolved when the request has finished.
                        return Promise.resolve();
                    },


                    removeCommentThread( data ) {
                        console.log( 'Comment thread removed', data );

                        $wire.call('removeCommentThread', data);

                        // Write a request to your database here. The returned `Promise`
                        // should be resolved when the request has finished.
                        return Promise.resolve();
                    }
                };
            }
        }

        ClassicEditor
            .create( document.querySelector( '#editor' ), {
                {{--initialData: @js($answer),--}}
                extraPlugins: [ CommentsAdapter ],
                licenseKey: '9K2tRUPoZobJydX6tm2HusZ/x1NCE/sghAv2zyuhaiEtxnbV9QKrhKjJvsI=',
                sidebar: {
                    container: document.querySelector('#sidebar')
                },
                commentsOnly: true, //disables editing of the text
                blockToolbar: {items: [
                    'paragraph', 'heading1', 'heading2', 'heading3',
                    '|',
                    'bulletedList', 'numberedList',
                    '|',
                    'blockQuote', 'uploadImage'
                ]},
                toolbar: {
                    items: [
                        'undo', 'redo',
                        '|', 'comment', 'commentsArchive',
                        '|', 'heading',
                        '|', 'bold', 'italic',
                        '|', 'bulletedList', 'numberedList'
                    ]
                }
            } ).then(editor => {
            	const annotationsUIs = editor.plugins.get( 'AnnotationsUIs' );


                console.dir(editor);

                console.log('then');
                const commentsRepository = editor.plugins.get( 'CommentsRepository' );
                window.testEditor = editor

                commentsRepository.on( `updateComment`, ( evt, data ) => {
                    console.log( evt, data );
                } );


                editor.ui.view.listenTo( document.querySelector( '#get-data' ), 'click', ( _, evt ) => {
						const editorData = editor.data.get();

						console.log( 'Editor data:' );
						console.log( editorData );

                        console.log(editor.plugins.get( 'CommentsRepository' ).getCommentThreads());

						evt.preventDefault();
					} );
          })
            .catch( error => console.error( error ) );
            "
>
    <style type="text/css">
        #container {
            /* To create the column layout. */
            display: flex;

            /* To make the container relative to its children. */
            position: relative;
        }

        #container .ck.ck-editor {
            /* To stretch the editor to max 700px
                (just to look nice for this example but it can be any size). */
            width: 100%;
            max-width: 700px;
        }

        #sidebar {
            /* Set some size for the sidebar (it can be any). */
            min-width: 300px;

            /* Add some distance. */
            padding: 0 10px;
        }

        .ck-content .ck-comment-marker {
            background: rgba(0, 77, 245, 0.2);
        }
        .ck-content .ck-comment-marker--active {
            background: rgba(0, 77, 245, 0.7);
        }
    </style>

    <div id="container">
        <textarea id="editor">{{ $answer }}</textarea>
        <div id="sidebar"></div>
    </div>
    <button id="get-data"> get data</button>
{{--    <script src="../build/ckeditor.js"></script>--}}
{{--    <script>--}}
{{--        // Application data will be available under a global variable `appData`.--}}
{{--        const appData = {--}}
{{--            // Users data.--}}
{{--            users: @js($users),--}}

{{--            // The ID of the current user.--}}
{{--            userId: '1486',--}}

{{--        };--}}

{{--        class CommentsAdapter {--}}
{{--            constructor( editor ) {--}}
{{--                this.editor = editor;--}}
{{--            }--}}

{{--            static get requires() {--}}
{{--                return [ 'CommentsRepository' ];--}}
{{--            }--}}

{{--            init() {--}}
{{--                console.log(appData.users);--}}
{{--                const usersPlugin = this.editor.plugins.get( 'Users' );--}}
{{--                const commentsRepositoryPlugin = this.editor.plugins.get( 'CommentsRepository' );--}}

{{--                // Load the users data.--}}
{{--                for ( const user of appData.users ) {--}}
{{--                    usersPlugin.addUser( user );--}}
{{--                }--}}

{{--                // Set the current user.--}}
{{--                usersPlugin.defineMe( appData.userId );--}}

{{--                // Set the adapter on the `CommentsRepository#adapter` property.--}}
{{--                commentsRepositoryPlugin.adapter = {--}}
{{--                    addComment( data ) {--}}
{{--                        console.log( 'Comment added', data );--}}

{{--                        --}}
{{--                        // Write a request to your database here. The returned `Promise`--}}
{{--                        // should be resolved when the request has finished.--}}
{{--                        // When the promise resolves with the comment data object, it--}}
{{--                        // will update the editor comment using the provided data.--}}
{{--                        return Promise.resolve( {--}}
{{--                            createdAt: new Date()       // Should be set on the server side.--}}
{{--                        } );--}}
{{--                    },--}}

{{--                    updateComment( data ) {--}}
{{--                        console.log( 'Comment updated', data );--}}

{{--                        // Write a request to your database here. The returned `Promise`--}}
{{--                        // should be resolved when the request has finished.--}}
{{--                        return Promise.resolve();--}}
{{--                    },--}}

{{--                    removeComment( data ) {--}}
{{--                        console.log( 'Comment removed', data );--}}

{{--                        // Write a request to your database here. The returned `Promise`--}}
{{--                        // should be resolved when the request has finished.--}}
{{--                        return Promise.resolve();--}}
{{--                    },--}}

{{--                    addCommentThread( data ) {--}}
{{--                        console.log( 'Comment thread added', data );--}}

{{--                        // Write a request to your database here. The returned `Promise`--}}
{{--                        // should be resolved when the request has finished.--}}
{{--                        return Promise.resolve( {--}}
{{--                            threadId: data.threadId,--}}
{{--                            comments: data.comments.map( ( comment ) => ( { commentId: comment.commentId, createdAt: new Date() } ) ) // Should be set on the server side.--}}
{{--                        } );--}}
{{--                    },--}}

{{--                    getCommentThread( data ) {--}}
{{--                        console.log( 'Getting comment thread', data );--}}

{{--                        // Write a request to your database here. The returned `Promise`--}}
{{--                        // should resolve with the comment thread data.--}}
{{--                        return Promise.resolve( {--}}
{{--                            threadId: data.threadId,--}}
{{--                            comments: [--}}
{{--                                {--}}
{{--                                    commentId: 'comment-1',--}}
{{--                                    authorId: '1485',--}}
{{--                                    content: '<p>Are we sure we want to use a made-up disorder name?</p>',--}}
{{--                                    createdAt: new Date(),--}}
{{--                                    attributes: {}--}}
{{--                                }--}}
{{--                            ],--}}
{{--                            // It defines the value on which the comment has been created initially.--}}
{{--                            // If it is empty it will be set based on the comment marker.--}}
{{--                            context: {--}}
{{--                                type: 'text',--}}
{{--                                value: 'Bilingual Personality Disorder'--}}
{{--                            },--}}
{{--                            resolvedAt: null,--}}
{{--                            resolvedBy: null,--}}
{{--                            attributes: {},--}}
{{--                            isFromAdapter: true--}}
{{--                        } );--}}
{{--                    },--}}

{{--                    updateCommentThread( data ) {--}}
{{--                        console.log( 'Comment thread updated', data );--}}

{{--                        // Write a request to your database here. The returned `Promise`--}}
{{--                        // should be resolved when the request has finished.--}}
{{--                        return Promise.resolve();--}}
{{--                    },--}}

{{--                    resolveCommentThread( data ) {--}}
{{--                        console.log( 'Comment thread resolved', data );--}}

{{--                        // Write a request to your database here. The returned `Promise`--}}
{{--                        // should be resolved when the request has finished.--}}
{{--                        return Promise.resolve( {--}}
{{--                            resolvedAt: new Date(), // Should be set on the server side.--}}
{{--                            resolvedBy: usersPlugin.me.id // Should be set on the server side.--}}
{{--                        } );--}}
{{--                    },--}}

{{--                    reopenCommentThread( data ) {--}}
{{--                        console.log( 'Comment thread reopened', data );--}}

{{--                        // Write a request to your database here. The returned `Promise`--}}
{{--                        // should be resolved when the request has finished.--}}
{{--                        return Promise.resolve();--}}
{{--                    },--}}


{{--                    removeCommentThread( data ) {--}}
{{--                        console.log( 'Comment thread removed', data );--}}

{{--                        // Write a request to your database here. The returned `Promise`--}}
{{--                        // should be resolved when the request has finished.--}}
{{--                        return Promise.resolve();--}}
{{--                    }--}}
{{--                };--}}
{{--            }--}}
{{--        }--}}

{{--        ClassicEditor--}}
{{--            .create( document.querySelector( '#editor' ), {--}}
{{--                --}}{{--initialData: @js($answer),--}}
{{--                extraPlugins: [ CommentsAdapter ],--}}
{{--                licenseKey: '9K2tRUPoZobJydX6tm2HusZ/x1NCE/sghAv2zyuhaiEtxnbV9QKrhKjJvsI=',--}}
{{--                sidebar: {--}}
{{--                    container: document.querySelector( '#sidebar' )--}}
{{--                },--}}
{{--                comments: {--}}
{{--                    editorConfig: {--}}
{{--                        // The list of plugins that will be included in the comments editors.--}}
{{--                        extraPlugins: [ 'Bold' ]--}}
{{--                    }--}}
{{--                },--}}
{{--                commentsOnly: true, //disables editing of the text--}}
{{--                toolbar: {--}}
{{--                    items: [--}}
{{--                        'undo', 'redo',--}}
{{--                        '|', 'comment', 'commentsArchive',--}}
{{--                        '|', 'heading',--}}
{{--                        '|', 'bold', 'italic',--}}
{{--                        '|', 'bulletedList', 'numberedList'--}}
{{--                    ]--}}
{{--                }--}}
{{--            } ).then(editor => window.testEditor = editor)--}}
{{--            .catch( error => console.error( error ) );--}}
{{--    </script>--}}
</div>
