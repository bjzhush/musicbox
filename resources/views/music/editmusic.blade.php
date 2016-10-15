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

        $('#artist_search').hide();

        $('#artist_input').change(function(){
            var inputArtist = $(this).val();
            searchArtist(inputArtist);
            $('#artist_search').show();
        });

        $('#artist_search').on('change', function () {
            var selectedValue = $(this).val();
            if (selectedValue > 0) {
                $('#artist_input').val($(this).find("option:selected").text());
                $('#artist_input').attr('musicid', selectedValue);
            }

        })
            
    });
</script>



<form class="form-horizontal">
    <fieldset>
        <div id="legend" class="">
            <legend class="">编辑音乐</legend>
        </div>


        <div class="control-group">

            <!-- Search input-->
            <div class="controls">
                <label class="control-label">歌手名</label>
                <input type="text" placeholder="搜索或输入" class="input-xlarge search-query" id="artist_input" musicid="0">

                <select name="artist_search" id="artist_search">
                </select>

                <div class="col-xs-12" style="height:50px;"></div>


            </div>

        </div>

        <button class="btn btn-success">保存</button>


    </fieldset>


</form>
