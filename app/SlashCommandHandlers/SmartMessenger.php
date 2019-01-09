<?php

namespace App\SlashCommandHandlers;

use App;
use Illuminate\Support\Facades\Log;
use Spatie\SlashCommand\Handlers\BaseHandler;
use Spatie\SlashCommand\Request;
use Spatie\SlashCommand\Response;

/**
 * @package SmartMessenger
 * @author  Léopold Jacquot {@link https://www.leopoldjacquot.com}
 *
 *          message [--intensity=15] [--speed=50] [--static=0]
 *
 *          Distant API doesn't handle params like brightness or speed unlucky...
 */
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
            $options = $this->getOptions($request->text);

            if (!is_array($options)) {
                return $options;
            }

            file_get_contents(self::SMART_LED_MESSENGER_API_URL . '?' . http_build_query($options));
        } catch (\Exception $e) {
            Log::error($e);

            return $this->respondToSlack("Oh! An error occured.");
        }

        return $this->respondToSlack("Message sent and will be displayed soon!");
    }

    /**
     * @param string $text
     *
     * @return array|Response
     */
    private function getOptions(string $text) {

        $intensity = 0;
        $speed = 10;
        $static = 0;
        $params = explode(' ', $text);

        foreach ($params as $key => $param) {
            if (strpos($param, '--intensity=') !== false) {
                $intensity = (int) str_replace('--intensity=', '', $param);

                if (!in_array($intensity, range(0, 15))) {
                    return $this->respondToSlack("Intensity parameter must be between 0 and 15!");
                }

                unset($params[$key]);
            } else if (strpos($param, '--speed=') !== false) {
                $speed = (int) str_replace('--speed=', '', $param);

                if (!in_array($speed, range(10, 50))) {
                    return $this->respondToSlack("Speed parameter must be between 10 and 50!");
                }

                unset($params[$key]);
            } else if (strpos($param, '--static=') !== false) {
                $static = (int) str_replace('--static=', '', $param);

                if (!in_array($static, range(0, 1))) {
                    return $this->respondToSlack("Static parameter must be 0 or 1!");
                }

                unset($params[$key]);
            }
        }

        $options = [
            'key'       => config('app.smart_led_messenger_api_key'),
            'intensity' => $intensity,
            'speed'     => $speed,
            'static'    => $static,
            'message'   => implode(' ', $params),
        ];

        if (empty($options['message'])) {
            return $this->respondToSlack("Message should not be empty!");
        }

        return $options;
    }
}