@include('bootstrap')

<div class="container-fluid">
    <div class="row-fluid">
        <div class="span12">
            <h4> {!! $msg !!}</h4>
            <h3> 音乐上传 </h3>
            <form method="post" action="/uploadmusic" enctype="multipart/form-data">
                <fieldset>
                    <label>备注：</label>
                    <input type="text" name="comment" />
                    <span class="help-block">选择并上传文件(支持多文件)</span>
                    <span></span>
                    <input type="file" name="mfile[]" multiple="multiple"/>
                    <span></span>
                    <button class="btn" type="submit">提交</button>
                </fieldset>
            </form>
        </div>
    </div>
</div>