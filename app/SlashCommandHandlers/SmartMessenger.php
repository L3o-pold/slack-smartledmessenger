<?php

namespace App\SlashCommandHandlers;

use App;
use Illuminate\Support\Facades\Log;
use Spatie\SlashCommand\Handlers\BaseHandler;
use Spatie\SlashCommand\Request;
use Spatie\SlashCommand\Response;

class SmartMessenger extends BaseHandler {

    const SMART_LED_MESSENGER_API_URL = 'https://www.smartledmessenger.com/push.ashx';

    /**
     * If this function returns true, the handle method will get called.
     *
     * @param \Spatie\SlashCommand\Request $request
     *
     * @return bool
     */
    public function canHandle(Request $request): bool {
        return true;
    }

    /**
     * Handle the given request. Remember that Slack expects a response
     * within three seconds after the slash command was issued. If
     * there is more time needed, dispatch a job.
     *
     * @param \Spatie\SlashCommand\Request $request
     *
     * @return \Spatie\SlashCommand\Response
     */
    public function handle(Request $request): Response {
        try {
            file_get_contents(
                self::SMART_LED_MESSENGER_API_URL.'?key='.config('app.smart_led_messenger_api_key').'&message='.urlencode($request->text)
            );
        } catch (\Exception $e) {
            Log::debug($e);

            return $this->respondToSlack("Euuuh erreur erreur erreur!");
        }

        return $this->respondToSlack("Ok mec!");
    }
}