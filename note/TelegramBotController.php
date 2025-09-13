<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use WeStacks\TeleBot\TeleBot;

class TelegramBotController extends Controller
{
    // +++++++++++++++++++++++++++++++++++++++
    private $bot;

    private $message_text;

    private $chat_id;

    private $fixed_chat_id = 1916864529; // <-- Set your fixed chat ID here

    // +++++++++++++++++++++++++++++++++++++++
    public function __construct()
    {
        $this->bot = new TeleBot(config('telegram.bot_token'));
    }

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    public function index()
    {
        return view('welcome');
    }

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    public function telegram_webhook(Request $request)
    {
        // +++++++++++++++++++++++++++++++++++++++++
        // Webhook
        // +++++++++++++++++++++++++++++++++++++++++
        $data = json_decode($request->getContent());
        if ($data && isset($data->message)) {
            $this->chat_id = $data->message->chat->id;
            $this->message_text = $data->message->text ?? '';
        }
    }

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    public function sendMessage(Request $request)
    {
        $chat_id = $request->input('chat_id', $this->fixed_chat_id);
        try {
            $message = $this->bot->sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Welcome To Code-180 Youtube Channel',
                'reply_markup' => [
                    'inline_keyboard' => [[[
                        'text' => '@code-180',
                        'url' => 'https://www.youtube.com/@code-180/videos',
                    ]]],
                ],
            ]);
        } catch (\Exception $e) {
            $message = 'Message: '.$e->getMessage();
        }

        return Response::json($message);
    }

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    public function sendPhoto(Request $request)
    {
        $chat_id = $request->input('chat_id', $this->fixed_chat_id);
        try {
            $message = $this->bot->sendPhoto([
                'chat_id' => $chat_id,
                'photo' => [
                    'file' => fopen(asset('public/upload/img.jpg'), 'r'),
                    'filename' => 'demoImg.jpg',
                ],
            ]);
        } catch (\Exception $e) {
            $message = 'Message: '.$e->getMessage();
        }

        return Response::json($message);
    }

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    public function sendAudio(Request $request)
    {
        $chat_id = $request->input('chat_id', $this->fixed_chat_id);
        try {
            $message = $this->bot->sendAudio([
                'chat_id' => $chat_id,
                'audio' => fopen(asset('public/upload/demo.mp3'), 'r'),
                'caption' => 'Demo Audio File',
            ]);
        } catch (\Exception $e) {
            $message = 'Message: '.$e->getMessage();
        }

        return Response::json($message);
    }

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    public function sendVideo(Request $request)
    {
        $chat_id = $request->input('chat_id', $this->fixed_chat_id);
        try {
            $message = $this->bot->sendVideo([
                'chat_id' => $chat_id,
                'video' => fopen(asset('public/upload/Password.mp4'), 'r'),
            ]);
        } catch (\Exception $e) {
            $message = 'Message: '.$e->getMessage();
        }

        return Response::json($message);
    }

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    public function sendVoice(Request $request)
    {
        $chat_id = $request->input('chat_id', $this->fixed_chat_id);
        try {
            $message = $this->bot->sendVoice([
                'chat_id' => $chat_id,
                'voice' => fopen(asset('public/upload/demo.mp3'), 'r'),
            ]);
        } catch (\Exception $e) {
            $message = 'Message: '.$e->getMessage();
        }

        return Response::json($message);
    }

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    public function sendDocument(Request $request)
    {
        $chat_id = $request->input('chat_id', $this->fixed_chat_id);
        try {
            $message = $this->bot->sendDocument([
                'chat_id' => $chat_id,
                'document' => fopen(asset('public/upload/Test_Doc.pdf'), 'r'),
            ]);
        } catch (\Exception $e) {
            $message = 'Message: '.$e->getMessage();
        }

        return Response::json($message);
    }

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    public function sendLocation(Request $request)
    {
        $chat_id = $request->input('chat_id', $this->fixed_chat_id);
        try {
            $message = $this->bot->sendLocation([
                'chat_id' => $chat_id,
                'latitude' => 19.6840852,
                'longitude' => 60.972437,
            ]);
        } catch (\Exception $e) {
            $message = 'Message: '.$e->getMessage();
        }

        return Response::json($message);
    }

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    public function sendVenue(Request $request)
    {
        $chat_id = $request->input('chat_id', $this->fixed_chat_id);
        try {
            $message = $this->bot->sendVenue([
                'chat_id' => $chat_id,
                'latitude' => 19.6840852,
                'longitude' => 60.972437,
                'title' => 'The New Word Of Code',
                'address' => 'Address For The Place',
            ]);
        } catch (\Exception $e) {
            $message = 'Message: '.$e->getMessage();
        }

        return Response::json($message);
    }

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    public function sendContact(Request $request)
    {
        $chat_id = $request->input('chat_id', $this->fixed_chat_id);
        try {
            $message = $this->bot->sendContact([
                'chat_id' => $chat_id,
                'photo' => 'https://picsum.photos/640',
                'phone_number' => '1234567890',
                'first_name' => 'Code-180',
            ]);
        } catch (\Exception $e) {
            $message = 'Message: '.$e->getMessage();
        }

        return Response::json($message);
    }

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    public function sendPoll(Request $request)
    {
        $chat_id = $request->input('chat_id', $this->fixed_chat_id);
        try {
            $message = $this->bot->sendPoll([
                'chat_id' => $chat_id,
                'question' => 'What is best coding language for 2023',
                'options' => ['python', 'javascript', 'typescript', 'php', 'java'],
            ]);
        } catch (\Exception $e) {
            $message = 'Message: '.$e->getMessage();
        }

        return Response::json($message);
    }

    public function getWebhookInfo()
    {
        try {
            $response = file_get_contents(
                config('telegram.api_url').config('telegram.bot_token').config('telegram.endpoints.get_webhook_info')
            );

            return Response::json(json_decode($response));
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 500);
        }
    }

    public function setWebhook()
    {
        try {
            $url = config('telegram.webhook_url');
            $response = file_get_contents(
                config('telegram.api_url').config('telegram.bot_token').
                config('telegram.endpoints.set_webhook').'?url='.$url
            );

            return Response::json(json_decode($response));
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteWebhook()
    {
        try {
            $response = file_get_contents(
                config('telegram.api_url').config('telegram.bot_token').
                config('telegram.endpoints.delete_webhook')
            );

            return Response::json(json_decode($response));
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 500);
        }
    }

    public function testPanel()
    {
        return view('telegram.test');
    }
}
