<?php

function getAllFromRest($totalPostsInCat) {
    $callsToMake = ceil($totalPostsInCat/100);

    $ch = array();
    for($i = 0; $i < $callsToMake; $i++){
        $page = $i + 1;
        $ch[$i] = curl_init(get_bloginfo('url').'/wp-json/wp/v2/posts?categories=4&per_page=100&page='.$page);
        curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch[$i], CURLOPT_SSL_VERIFYPEER,  0);
    }

    $mh = curl_multi_init();
    for($i = 0; $i < $callsToMake; $i++){
        curl_multi_add_handle($mh, $ch[$i]);
    }

    $running = null;
    do {
        curl_multi_exec($mh, $running);
    } while ($running);

    for($i = 0; $i < $callsToMake; $i++){
        if (!curl_errno($ch[$i])) {
            $info = curl_getinfo($ch[$i]);
            //error_log(print_r($info,true));
        }
        curl_multi_remove_handle($mh, $ch[$i]);
    }

    curl_multi_close($mh);

    $responses = array();
    for($x = 0; $x < $callsToMake; $x++){
        $responses[$x] = json_decode(curl_multi_getcontent($ch[$x]));
    }

    $final = array();
    for($i = 0; $i < count($responses); $i++){
        for($x=0;$x<count($responses[$i]);$x++){
            array_push($final,$responses[$i][$x] );
        }
    }
    return $final;
}


