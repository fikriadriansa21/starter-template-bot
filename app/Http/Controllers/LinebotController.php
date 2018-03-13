<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LINE\LINEBot;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Event\PostbackEvent;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use Illuminate\Foundation\Inspiring;

class LinebotController extends Controller
{
    
    public function webhook(Request $req){
        $httpClient = new CurlHTTPClient(config('services.botline.access'));
        $bot = new LINEBot($httpClient, [
            'channelSecret' => config('services.botline.secret')
        ]);
        $signature = $req->header(HTTPHeader::LINE_SIGNATURE);
        if (empty($signature)) {
            abort(401);
        }
        try {
            $events = $bot->parseEventRequest($req->getContent(), $signature);
        } catch (\Exception $e) {
            logger()->error((string) $e);
            abort(200);
        }

        foreach ($events as $event) {
            $replyMessage = handle($event);
            $bot->replyMessage($event->getReplyToken(), $replyMessage);
        }
        return response('OK', 200);
    }
}