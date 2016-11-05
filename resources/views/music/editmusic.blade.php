@include('bootstrap')
<script>
    $(document).ready(function() {


        function searchArtist(artist) {
            $.ajax({
                url:'/searchartist',
                type:'POST',
                data: 'key=' + artist,
                dataType: 'json',
                success: function( json ) {
                    $('#artist_search').find('option').remove();
                    $.each(json, function(index, text){
                        $('#artist_search').append($('<option>', {
                            value: index,
                            text : text
                        }));
                    });
                    $('#artist_search').selectpicker('refresh');
                }
            });
        };
        
        $('#savemusic').on('click', function () {
            $('#ajaxtext').empty();

            var data = {};
            data['music_id'] = $('#musicid').val();
            data['artist_id'] = $('#artist_input').attr('artistid');
            data['artist_name'] = $('#artist_input').val();
            data['is_draft'] = $('#isdraft').is(":checked") ? 2 : 1;
            data['tags'] = $('#tags').val();

            $.ajax({
                type: 'POST',
                url: '/editmusic',
                data: data,
                dataType:'json',
                success: function (msg) {
                    if (msg.code == 200) {
                        $('#ajaxtext').html('<font color="green">保存成功</font>');
                        return;
                    } else {
                        $('#ajaxtext').html('<font color="red">保存失败'+ msg.msg +'</font>');
                        return;
                    }
                }
            });

        })

        $('#artist_search').hide();

        $('#artist_input').on("input", function(){
            var inputArtist = $(this).val();
            searchArtist(inputArtist);
            $('#artist_search').show();
        });

        $('#artist_search').on('change', function () {
            var selectedValue = $(this).val();
            $('#artist_input').attr('artistid', selectedValue);
            if (selectedValue > 0) {
                $('#artist_input').val($(this).find("option:selected").text());
            }
        })

        $('#newtag').on("input",function(e){

            $('#mysqlTags').empty();

            $.ajax({
                type: 'POST',
                url: '/searchtag',
                data: 'tag='+$('#newtag').val(),
                dataType: 'json',
                success: function( json ) {
                    $('#mysqlTags').find('option').remove();
                    $.each(json, function(index, text){
                        $('#mysqlTags').append($('<option>', {
                            value: index,
                            text : text
                        }));
                    });
                    $('#mysqlTags').selectpicker('refresh');
                }


            });
        });


        $('#mysqlTags').on('change', function () {
            var currentTag = $('#mysqlTags').find(':selected').text();
            if (currentTag !== '请选择') {
                var crtTags = $('#tags').val().split(" ");
                var isRepeat = false;
                $.each(crtTags, function (index, value) {
                    if (currentTag == value) {
                       isRepeat = true;
                    }
                })
                if (!isRepeat) {
                    $('#tags').val($('#tags').val() + ' ' + currentTag);
                }
            }
        });


    });


</script>



<div class="form-horizontal">
    <fieldset>
        <div id="legend" class="">
            <legend class="">编辑音乐</legend>
        </div>
        <input type="hidden" id="musicid" value="{{$musicInfo->id}}">

        <div class="control-group">

            <!-- Search input-->
            <div class="controls">
                <label class="control-label">歌手名</label>
                <input type="text" placeholder="搜索或输入" value="{{isset($musicInfo->artist) ? $musicInfo->artist : ''}}" class="form-control" id="artist_input" artistid="{{$musicInfo->artistid}}">

                <select name="artist_search" id="artist_search" class="selectpicker">
                </select>
            </div>

            <label class="control-label">标签搜索</label>
            <div class="form-inline">
                <input type="text" placeholder="标签" id="newtag" class="bs-searchbox">
                <select name="mysqlTags" id="mysqlTags" class="selectpicker">
                </select>
            </div>

            <label class="control-label">标签列表</label>
            <div class="form-inline">
                <input type="text" placeholder="标签" id="tags" class="form-control" value="{{$tags}}">
            </div>

        </div>




        <button class="btn btn-success" id="savemusic">保存</button>
        <div id="ajaxtext"></div>


    </fieldset>


</div>
