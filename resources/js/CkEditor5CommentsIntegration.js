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

        const config = this.editor.config.get('commentsIntegration');

        // Load the users data.
        for ( var user of config.users ) {
            usersPlugin.addUser( user );
        }

        // Set the current user.
        usersPlugin.defineMe( config.userId );

        // Load the comment threads data.
        for ( var commentThread of config.commentThreads ) {
            commentsRepositoryPlugin.addCommentThread( commentThread );
        }
    }
}

export default CommentsIntegration;