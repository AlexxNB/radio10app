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

    
    $json = getURL('http://musicbrainz.org/ws/2/annotation/?fmt=json&query='.urlencode($artist.' '.$title));
    $data = json_decode($json,true);
    if($data['count'] == 0 ) return false;
    $mbid = $data['annotations'][0]['entity'];
    $json = getURL('https://coverartarchive.org/release/'.$mbid);
    if(!$json) return false;
    $data = json_decode($json,true);

    $img =  $data['images'][0]['thumbnails']['large'];
    $covers[$hash] = $img;
    file_put_contents('covers.json',json_encode($covers));

    return $img;
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