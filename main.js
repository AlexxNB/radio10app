$(document).ready(function(){
    startAudio();
    getCurentTitle();
})

function startAudio(){
    var myaudio = new Audio('http://ic.radio10.live/stream.mp3');
    myaudio.play();
}

var artist = 'Неизвестный';
var title = 'Неизвестная'
function getCurentTitle(){
    $.getJSON( "now.php", function( resp ) {
        if(artist != resp.artist || title != resp.title) 
        {
            if(resp.error !== undefined) { 
                hideCover();
            }else{
                artist = resp.artist
                title = resp.title
                changeCover(resp.artist,resp.title,resp.cover)
            } 
        }   

        setTimeout(getCurentTitle, 3000)
    });
    
}

function changeCover(artist,title,cover){
    var cover = cover || 'nocover.png'
    $('#info').fadeOut(1000,function(){
        $('#artist').text(artist);
        $('#title').text(title);
        $('#cover img').attr('src',cover);
        $('#info').fadeIn(1000);
    });
}

function hideCover(){
    $('#info').fadeOut(1000);
}