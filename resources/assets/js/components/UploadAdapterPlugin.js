import MyUploadAdapter from "./UploadAdapter";

export default ( editor ) => {
    editor.plugins.get( 'FileRepository' ).createUploadAdapter = ( loader ) => {
        return new MyUploadAdapter( loader );
    };
}
