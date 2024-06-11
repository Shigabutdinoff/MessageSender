<?php

namespace Shigabutdinoff\MessageSender;

use Illuminate\Mail\SentMessage;
use Illuminate\Support\Facades\Mail;
use Shigabutdinoff\MessageSender\mts\Client;

class SendMessage
{
    /**
     * Sends a message to a Telegram chat using the given token and chat ID.
     *
     * @param string $token The personal token for accessing the Telegram API
     * @param int|string $chat_id The chat_id where the message will be sent
     * @param string|array|object $text The text or data to be sent as a message
     * @return bool|mixed|string The response from the Telegram API, either as a JSON-decoded object or a string
     */
    public static function telegram(string $token, $chat_id, $text)
    {
        $text = (is_array($text) or is_object($text)) ? json_encode($text, JSON_PRETTY_PRINT) : (json_validate($text) ? json_encode(json_decode($text), JSON_PRETTY_PRINT) : $text);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => sprintf('%1$s/bot%2$s/sendMessage', config('telegram.url'), $token),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode(array(
                "chat_id" => $chat_id,
                "text" => $text
            )),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_validate($response) ? json_decode($response) : $response;
    }

    /**
     * Sends a chat2desk message.
     *
     * @param string $token The authentication token.
     * @param int|string $client_id The client ID.
     * @param mixed $text The message content. Can be a string, array, or object.
     * @return bool|mixed|string Returns the response from the chat2desk API.
     */
    public static function chat2desk(string $token, $client_id, $text)
    {
        $text = (is_array($text) or is_object($text)) ? json_encode($text, JSON_PRETTY_PRINT) : (json_validate($text) ? json_encode(json_decode($text), JSON_PRETTY_PRINT) : $text);
        $params = http_build_query([
            'text' => $text,
            'client_id' => $client_id
        ]);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => sprintf('%1$s/v1/messages?%2$s', config('chat2desk.url'), $params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                sprintf('Authorization: %1$s', $token)
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_validate($response) ? json_decode($response) : $response;
    }

    /**
     * Sends an SMS message using MTS HTTP API.
     *
     * @param string $login The login for accessing the MTS API.
     * @param string $password The password for accessing the MTS API.
     * @param int|string $naming The sender's name or number.
     * @param mixed $text The content of the SMS message. Can be a string, array, or object. If it's an array or object, it will be JSON encoded.
     * @param int $phone The recipient's phone number.
     * @return array[]|mixed|string Returns the status of the sent SMS message or an error code if the message failed to send.
     */
    public static function mts(string $login, string $password, $naming, $text, int $phone)
    {
        $text = (is_array($text) or is_object($text)) ? json_encode($text, JSON_PRETTY_PRINT) : (json_validate($text) ? json_encode(json_decode($text), JSON_PRETTY_PRINT) : $text);

        $client = new Client(sprintf('%1$s/http-api/v1/', config('mts.url')), $login, $password);

        // отправка сообщения
        $id = $client->sendSms($naming, $text, $phone); // отправка смс
        // если $id == 0, то была ошибка запроса

        if ($id != "0") {
            // получение статуса по id сообщения
            $status = $client->getSmsInfo([$id]);
            return $status;
        } else {
            return $id;
        }
    }

    /**
     * Sends an email using the provided parameters.
     *
     * @param string $to The recipient's email address.
     * @param string $subject The subject of the email.
     * @param string $text The content of the email.
     * @return string The debug information for the email sending process.
     */
    public static function mail(string $to, string $subject, string $text)
    {
        return (Mail::raw($text, function ($message) use ($to, $subject) {
            $message->to($to)
                ->subject($subject);
        })->getDebug());
    }
}
