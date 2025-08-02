<?php

namespace App\Service\Telegram;

use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CustomWebhookHandler extends WebhookHandler
{
    public array $videoInfo = [];
    public function setVideoInfo($videoId)
    {
        $youtube = new \App\Service\YouTube();
        $this->videoInfo = $youtube->extractVideoInfo($videoId);
    }
    public function start()
    {
        $this->chat->markdown(__("Send a link to a youtube video"))->send();
    }

    public function download()
    {
        $format_id = $this->data->get('format_id');
        list($format, $videoId) = explode('_', $format_id);
        $formats = $this->getFormatsByVideoId($videoId);
        if(isset($formats[$format])){

            if($format==="Audio"){
                $this->sendAudio($formats[$format]);
            }
        }


    }

    public function sendAudio($url)
    {
        $youtube = new \App\Service\YouTube();
        $fileName = "{$this->videoInfo['title']}.mp3";
        $youtube->download($fileName, $url);
        Telegraph::audio(public_path($fileName), $fileName)->send();
        File::delete(public_path($fileName));
    }

    public function getFormatsByVideoId($videoId)
    {
        $this->setVideoInfo($videoId);
        $info = $this->videoInfo;
        $formats = [];

        if(isset($info['medias']['audio'][0]['url'])){
            $formats["Audio"] = $info['medias']['audio'][0]['url'];
        }

        if(isset($info['medias']['video']) && is_iterable($info['medias']['video'])){
            foreach ($info['medias']['video'] as $videoItem){
                if(isset($videoItem['url']) && isset($videoItem['height'])){
                    $formats[$videoItem['height']."p"] = $videoItem['url'];
                }
            }
        }
        return array_unique($formats);
    }

    public function handle(Request $request, TelegraphBot $bot): void
    {
        parent::handle($request, $bot);
        $message = $request->input('message');
        if(isset($message['text'])){
            $text = trim($request->input('message')['text']);
            if (preg_match('/^((?:https?:)?\/\/)?((?:www|m)\.)?((?:youtube\.com|youtu.be))(\/(?:[\w\-]+\?v=|embed\/|v\/)?)([\w\-]+)(\S+)?$/', $text) === 1) {
                $youtube = new \App\Service\YouTube();

                $id = $youtube->extractVideoId($text);
                $duration = (int)$youtube->extractVideoInfo($id)['duration'];
                if($duration<600){
                    $buttons = [];
                    $formats = $this->getFormatsByVideoId($id);
                    $this->sendAudio($formats['Audio']);
                }else{
                    Telegraph::message("Send audio with duration less than 10 minute")->send();
                }

                /*$videoTitle = $this->videoInfo['title'];
                foreach ($formats as $format => $url){
                    $buttons[] = Button::make($format)->action('download')->param('format_id', $format."_".$id);
                }
                $buttonsChunks = array_chunk($buttons, 3);
                $keyboard = Keyboard::make();
                foreach ($buttonsChunks as $chunk){
                    $keyboard->row($chunk);
                }

                Telegraph::message(__("You can download video \"").$videoTitle.__("\" in formats"))
                    ->keyboard($keyboard)->send();*/
            }
        }

    }
}
