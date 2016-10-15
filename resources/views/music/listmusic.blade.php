@include('bootstrap')
<script>
    $(function(){
        $(".editmusic").click(function(){
            var muscid = $(this).attr('musicid');
            var frameSrc = "/editmusic?musicid="+muscid;
            $("#NoPermissioniframe").attr("src", frameSrc);
            $('#NoPermissionModal').modal({ show: true, backdrop: 'static' });
        });
    });
</script>


<div class="container-fluid">
    <div class="row-fluid">
        <div class="span12">
            <table class="table">
                <thead>
                <tr>
                    <th> 上传文件名 </th>
                    <th> 上传备注 </th>
                    <th> 状态 </th>
                    <th> 上传时间 </th>
                    <th> 听 </th>
                    <th> 编辑 </th>
                </tr>
                </thead>
                <tbody>

                @foreach ($musics as $k => $music)
                    <tr class="{{$k%2 ==1 ? "" : "warning"}}">
                        <td>{{$music->uploadname}}</td>
                        <td>{{$music->uploadcomment}}</td>
                        <td>{!! $music->marked == 0 ? "<font color='red'>待处理</font>" : "<font color='green'>已处理</font>" !!}</td>
                        <td>{{$music->created_at}}</td>
                        <td>todo_listen</td>
                        <td><div class="editmusic" musicid="{{$music->id}}">编辑</div> </td>
                    </tr>
                @endforeach

                </tbody>
            </table>
        </div>
    </div>
</div>




<div class="modal fade" id="NoPermissionModal">
    <div class="modal-dialog" >
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="NoPermissionModalLabel">系统消息</h4>
            </div>
            <div class="modal-body">
                <iframe id="NoPermissioniframe" width="100%" height="50%" frameborder="0"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default " data-dismiss="modal">    关  闭    </button>
            </div>
        </div>
    </div>
</div>