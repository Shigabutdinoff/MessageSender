<?php

namespace Shigabutdinoff\MessageSender;

class SendMessage
{
    /**
     * Sends a message to a Telegram chat using the given token and chat ID.
     *
     * @param string $token The personal token for accessing the Telegram API
     * @param int $chat_id The chat_id where the message will be sent
     * @param string|array|object $text The text or data to be sent as a message
     * @return bool|mixed|string The response from the Telegram API, either as a JSON-decoded object or a string
     */
    public static function telegram(string $token, int $chat_id, $text)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => sprintf('https://api.Telegram.org/bot%1$s/sendMessage', $token),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode(array(
                "chat_id" => $chat_id,
                "text" => (is_array($text) or is_object($text)) ? json_encode($text, JSON_PRETTY_PRINT) : $text
            )),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_validate($response) ? json_decode($response) : $response;
    }
}
