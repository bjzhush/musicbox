@include('bootstrap')

<h1>Upload Music</h1>

<form method="post" action="/uploadmusic" enctype="multipart/form-data">
    <div>
        <input type="text" name="comment" placeholder="Comment">
        <input name="mfile" type="file" />
        <input type="submit" value="上传"/>
    </div>

</form>