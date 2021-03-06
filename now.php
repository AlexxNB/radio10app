<?php

# 1. Now playing artist and track
$playing = getCurrentPlaying();
if(!$playing) die(json_encode(['error'=>'unknown artist']));

# 2. Is there a cover for track
$cover = getCover($playing['artist'],$playing['title']);

# 3. Answer to client
die(json_encode([
    'artist'=>$playing['artist'],
    'title'=>$playing['title'],
    'cover'=>$cover
]));

################################################################


# Parse curent playing track on Radio10; Returns artist name and track title;
function getCurrentPlaying(){
    $string = getURL('https://radio10.live/json.xsl');
    $a = explode(' - ',$string);
    if(count($a) != 2) return false;
    if(empty($a[0]) or empty($a[1])) return false;
    return ['artist'=>$a[0],'title'=>$a[1]];
}

# Looking cover image at coverartarchive.org; Returns URL of the cover image.
function getCover($artist,$title){
    $hash = md5(">>>$artist<<< >>>$title<<<");
    $covers = json_decode(file_get_contents('covers.json'),true);

    if(isset($covers[$hash])) return $covers[$hash];

    //http://musicbrainz.org/ws/2/release/?fmt=json&query=release:%22We%20will%20Rock%20you%22%20AND%20artist:%22Queen%22
    $json = getURL('http://musicbrainz.org/ws/2/release/?fmt=json&query='.urlencode('release:"'.$title.'" AND artist:"'.str_replace(', ','","',$artist).'"'));
    $data = json_decode($json,true);
    if($data['count'] == 0 ) return false;
    $attempt = 0;
    foreach($data['releases'] as $release){
        if($attempt++ > 10) break;
        if($release['score'] < 95) break;

        if(!$json = getURL('https://coverartarchive.org/release/'.$release['id'])){
            if(!$json = getURL('https://coverartarchive.org/release-group/'.$release['release-group']['id'])) continue;
        }

        $data = json_decode($json,true);
        $img =  $data['images'][0]['thumbnails']['large'];
        $covers[$hash] = $img;
        file_put_contents('covers.json',json_encode($covers));
        return $img;   
    }
    return false;
    
}


# Get request
function getURL($url){
    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "Accept-language: en\r\n" .
            "User-Agent: Radio10 SmartTV App\r\n"
        ]
    ];
    
    $context = stream_context_create($opts);
    return file_get_contents($url,false,$context);
}
?>