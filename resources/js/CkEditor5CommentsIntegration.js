class CommentsIntegration {
    static staticUserId = null;
    static staticUsers = null;
    static staticCommentThreads = null;

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
        for ( var user of CommentsIntegration.staticUsers ) {
            usersPlugin.addUser( user );
        }

        // Set the current user.
        usersPlugin.defineMe( CommentsIntegration.staticUserId );

        // Load the comment threads data.
        for ( var commentThread of CommentsIntegration.staticCommentThreads ) {
            commentsRepositoryPlugin.addCommentThread( commentThread );
        }
    }
}

export default CommentsIntegration;