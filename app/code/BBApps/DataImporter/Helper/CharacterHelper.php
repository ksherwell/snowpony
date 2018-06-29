<?php
/**
 * BBApps DataImporter
 *
 * @copyright  Copyright (c) 2017 BBApps (https://doublebapps.com/)
 */
namespace BBApps\DataImporter\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class CharacterHelper extends AbstractHelper
{
    /**
     * @param $unicodeString
     *
     * @return mixed
     */
    public static function trimUnicode($unicodeString)
    {
        return trim(preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u', '', $unicodeString));
    }

    /**
     * @param $unicodeString
     *
     * @return mixed
     */
    public static function removeUnicodeWhitespace($unicodeString)
    {
        return preg_replace('/[\pZ\pC]/u', '', $unicodeString);
    }

    /**
     * @param $string
     * @param string $replacement
     * @return mixed
     */
    public static function removeSpecialCharacter($string, $replacement = '')
    {
        return preg_replace('/[^A-Za-z0-9\_]/', $replacement, $string);
    }

    /**
     * @param string $unicodeString
     *
     * @return mixed
     */
    public static function utf8ToSimilarAscii($unicodeString)
    {
        $unicodeString = preg_replace("/[áàâãªä]/u", "a", $unicodeString);
        $unicodeString = preg_replace("/[ÁÀÂÃÄ]/u", "A", $unicodeString);
        $unicodeString = preg_replace("/[ÍÌÎÏ]/u", "I", $unicodeString);
        $unicodeString = preg_replace("/[íìîï]/u", "i", $unicodeString);
        $unicodeString = preg_replace("/[éèêë]/u", "e", $unicodeString);
        $unicodeString = preg_replace("/[ÉÈÊË]/u", "E", $unicodeString);
        $unicodeString = preg_replace("/[óòôõºö]/u", "o", $unicodeString);
        $unicodeString = preg_replace("/[ÓÒÔÕÖ]/u", "O", $unicodeString);
        $unicodeString = preg_replace("/[úùûü]/u", "u", $unicodeString);
        $unicodeString = preg_replace("/[ÚÙÛÜ]/u", "U", $unicodeString);
        $unicodeString = preg_replace("/[’‘‹›‚]/u", "'", $unicodeString);
        $unicodeString = preg_replace("/[“”«»„]/u", '"', $unicodeString);

        $unicodeString = str_replace("–", "-", $unicodeString);
        $unicodeString = str_replace(" ", " ", $unicodeString);
        $unicodeString = str_replace("ç", "c", $unicodeString);
        $unicodeString = str_replace("Ç", "C", $unicodeString);
        $unicodeString = str_replace("ñ", "n", $unicodeString);
        $unicodeString = str_replace("Ñ", "N", $unicodeString);

        return $unicodeString;
    }
}
