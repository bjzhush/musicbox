@include('nav')

<script type='text/javascript'>
    $(document).ready(function(){
        // show play time count
        var playCount = parseInt(localStorage.getItem('playCount'));
        if (playCount != playCount) {
            playCount = 1;
        }
        playCount += 1;
        localStorage.setItem('playCount', playCount);
        $('#playCount').html(playCount);
    });

    $(document).keydown(function(e){
        var key =  e.which;
        if(key == 32){
            var song = $('#media').get(0);
            if(song.paused)
            {
                song.play();
            }
            else
            {
                song.pause();
            }
        } else if (key == 78) {
            window.location = './listen';
        } else if (key == 38) {
            document.getElementById('media').volume = document.getElementById('media').volume+0.1;
        } else if (key == 40) {
            document.getElementById('media').volume = document.getElementById('media').volume-0.1;
        } else if (key == 37) {
            document.getElementById('media').currentTime = document.getElementById('media').currentTime-5;
        } else if (key == 39) {
            document.getElementById('media').currentTime = document.getElementById('media').currentTime+5;
        } else if (key == 39) {
        }
    });

    function repeatorreload()
    {
        if ($('#myonoffswitch').is(':checked') == true) {
            document.getElementById('media').currentTime = 0;
            document.getElementById('media').play();
        } else {
            document.location.reload();
        }
    }
</script>

    <br><br> <audio onended="repeatorreload()" id="media" controls="controls" autoplay="autoplay">
        <source src="{{$randomRow->listenUrl}}" type="audio/mpeg" />
        Your browser does not support the audio element.
    </audio>

    <h1>现在播放的是：<font color="#ff0000">{{$randomRow->uploadname}}</font></h1>
