<?php

namespace Tn\Plan\Domain\FactoryConverter;

class FactoryConverter extends \Bitrix\Main\Text\Converter
{
    public function encode($text, $textType = "")
    {
        $unserializeText = unserialize($text);
        if ($unserializeText) {
            if ($unserializeText["TEXT"])
                return $unserializeText["TEXT"];
            return $unserializeText;
        } else
            return $text;
    }

    public function decode($text, $textType = "")
    {
        return $text;
    }
}