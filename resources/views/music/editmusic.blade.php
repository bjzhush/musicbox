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

        $('#artist_input').change(function(){
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
                <input type="text" placeholder="搜索或输入" value="{{isset($musicInfo->artist) ? $musicInfo->artist : ''}}" class="input-xlarge search-query" id="artist_input" artistid="{{$musicInfo->artistid}}">

                <select name="artist_search" id="artist_search">
                </select>


            </div>
            <div class="controls">
                暂存 <input type="checkbox" id="isdraft" value="0">

                <div class="col-xs-12" style="height:50px;"></div>

            </div>

        </div>


        <button class="btn btn-success" id="savemusic">保存</button>
        <div id="ajaxtext"></div>


    </fieldset>


</div>
