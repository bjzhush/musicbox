@include('bootstrap')


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
                        <td>todo_edit</td>
                    </tr>
                @endforeach

                </tbody>
            </table>
        </div>
    </div>
</div>