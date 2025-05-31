<?php

use DefStudio\Telegraph\Facades\Telegraph;
use Illuminate\Support\Facades\Route;
use YouTube\YouTubeDownloader;
use YouTube\Exception\YouTubeException;
Route::get('/', function () {

    if(isset($_GET['url'])){
        $url = $_GET['url'];
        $youtube = new \App\Service\YouTube();
        $id = $youtube->extractVideoId($url);
        $info = $youtube->extractVideoInfo($id);

        $download = $youtube->download('sbu.mp3', $info['medias']['audio'][0]['url']);

        die;
        $youtube = new YouTubeDownloader();


        try {
            $downloadOptions = $youtube->getDownloadLinks($url, 'android_vr');
            $arr = [];
            foreach ($downloadOptions->getAllFormats() as $downloadOption) {
                $arr[(int)$downloadOption->qualityLabel] = $downloadOption->url;
            }
            dd();
            die;
            if ($downloadOptions->getAllFormats()) {
                echo $downloadOptions->getFirstCombinedFormat()->url;
            } else {
                echo 'No links found';
            }

        } catch (YouTubeException $e) {
            echo 'Something went wrong: ' . $e->getMessage();
        }
    }


    die;
    $path_to_ffmpeg = storage_path("ffmpeg-7.0.2-amd64-static");
    `cd $path_to_ffmpeg && ./ffmpeg -i videoplayback.mp4 -f mp3 -ab 192000 -vn music.mp3 > huy.txt`;
    dd();



    die;
    return view('welcome');
});

Route::get('/get-updates', function () {
    $telegraphBot = \DefStudio\Telegraph\Models\TelegraphBot::query()
        ->where('name',"Download music and video from youtube")
        ->firstOrFail();

dd($telegraphBot);
    dd(Telegraph::bot($telegraphBot)->botUpdates()->send());
});
