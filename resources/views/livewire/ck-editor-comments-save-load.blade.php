<div>
    <div style="margin: 20px;"
         wire:ignore
         x-data="{
         users: @js($users),
         userId: '1486',
         mainEditor: null,
         commentEditor: null,
         commentThreads: @js($this->commentThreads),
         commentRepository: null,
         activeThread: null,

        async saveCommentThread() {
        commentsRepository = mainEditor.plugins.get( 'CommentsRepository' );
        console.dir(commentsRepository);
        this.activeThread = commentsRepository.activeCommentThread;
            if(this.activeThread) {
                console.log('active thread');
                await this.updateCommentThread();
                return;
            }
            console.log('active thread 2');

            await this.createCommentThread();

        },

        async updateCommentThread() {
            const commentThread = commentsRepository.activeCommentThread;
            console.log('updaetCommentThread');
        },
        async createCommentThread() {

            var feedbackEditor = ClassicEditors['comment-editor'];

            var comment = feedbackEditor.getData();

            if(!comment || comment == '<p></p>') {
                return;
            }

            var editor = ClassicEditors['answer-editor'];

            editor.focus();

            $nextTick(async () => {
                if(editor.editing.view.hasDomSelection) {

                    //created feedback record data
                    var feedback = await $wire.call('createNewComment');
                    console.log(feedback);

                    var threadId = feedback.threadId;
                    var commentId = feedback.commentId;

                    commentsRepository = editor.plugins.get( 'CommentsRepository' );

                    await editor.execute( 'addCommentThread', { threadId: threadId } );

                    var lastCommentThread = commentsRepository.getCommentThreads()[commentsRepository.getCommentThreads().length-1];
                    lastCommentThread.addComment({threadId: threadId, commentId: commentId, content: comment, authorId: '1486'});

                }


            });

        },
        async deleteCommentThread(threadId) {
            const result = await $wire.call('deleteCommentThread', threadId);
            if(result) {
                return commentsRepository.getCommentThread('thread-1').remove();
            }
            console.log('failed to delete answer feedback');
        }

     }"
         x-init="
    appData = {

        // Users data.
        users: @js($users),

        // The ID of the current user.
        userId: '1486',

        // Comment threads data.
        commentThreads: @js($this->commentThreads)

    };

class CommentsIntegration {
    constructor( editor ) {
        this.editor = editor;
    }

    static get requires() {
        return [ 'CommentsRepository' ];
    }

    init() {
        const usersPlugin = this.editor.plugins.get( 'Users' );
        const commentsRepositoryPlugin = this.editor.plugins.get( 'CommentsRepository' );

        // Load the users data.
        for ( var user of users ) {
            usersPlugin.addUser( user );
        }

        // Set the current user.
        usersPlugin.defineMe( userId );

        // Load the comment threads data.
        for ( var commentThread of commentThreads ) {
            commentsRepositoryPlugin.addCommentThread( commentThread );
        }
    }
}

ClassicEditor.create( document.querySelector( '#editor' ), {
    extraPlugins: [ CommentsIntegration ],
    licenseKey: '9K2tRUPoZobJydX6tm2HusZ/x1NCE/sghAv2zyuhaiEtxnbV9QKrhKjJvsI=',
    toolbar: {
        items: [
            'comment',
        ]
    },
    users: users,
} ).then( editor => {

    ClassicEditors['answer-editor'] = editor;
    mainEditor = editor;
    this.mainEditor = editor;
    window.ed = editor;

    editor.plugins.get( 'CommentsOnly' ).isEnabled = true;

    //Deactivate Sidebar/balloon
{{--    editor.plugins.get('AnnotationsUIs').deactivateAll();--}}

    // After the editor is initialized, add an action to be performed after a button is clicked.
    const commentsRepository = editor.plugins.get( 'CommentsRepository' );

    window.commentsRepository = commentsRepository;




    // Get the data on demand.
    document.querySelector( '#get-data' ).addEventListener( 'click', () => {
        const editorData = editor.data.get();
        const commentThreadsData = commentsRepository.getCommentThreads( {
            skipNotAttached: true,
            skipEmpty: true,
            toJSON: true
        } );
        console.log(commentsRepository.getCommentThreads());
        // Now, use `editorData` and `commentThreadsData` to save the data in your application.
        // For example, you can set them as values of hidden input fields.
        console.log( editorData );
        console.log( editor.getData() );
        console.log( commentThreadsData );
    } );



    editor.on('selectionChange', (evt, data) => {console.log(evt, data)});

    const focusItems = document.querySelectorAll('#sidebar > .button');

    $nextTick(() => {
        for ( var item of focusItems ) {
{{--            mainEditor.ui.focusTracker.add( item );--}}
        }
    })


} )
.catch( error => console.error( error ) );

            "
    >
        <style type="text/css">


            /* hides the annotiation */
            .ck .ck.ck-balloon-panel {
                display: none;
            }



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
            <div id="sidebar">
                <x-input.rich-textarea type="assessment-feedback" editor-id="comment-editor"> </x-input.rich-textarea>

                <x-button.cta {{--wire:click="createNewComment"--}} @click="saveCommentThread">opslaan</x-button.cta>
                <x-button.secondary {{--wire:click="createNewComment"--}} @click="deleteCommentThread('thread-1')">delete</x-button.secondary>

            </div>
        </div>

        <button id="get-data">Get editor data</button>

    </div>

</div>
