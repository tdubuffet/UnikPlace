<?php
/**
 * Created by PhpStorm.
 * User: tdubuffet
 * Date: 23/11/16
 * Time: 14:45
 */

namespace AppBundle\Service;


class Translation
{

    public static function trans($text, $target)
    {
        $uri = 'http://www.transltr.org/api/translate';

        $ch = curl_init($uri);
        $payload = json_encode([
            'text' => $text,
            'to' => $target
        ]);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        if ($result) {

            $values = json_decode($result, true);

            if (isset($values['translationText'])) {
                return $values['translationText'];
            }

            return null;
        }

        return null;
    }

}