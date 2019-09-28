<?

namespace local\Domain\Factory;

use Bitrix\Main\Type;

class FactoryConverter extends \Bitrix\Main\Text\Converter
{
    public function encode($text, $textType = "")
    {
        if ($text instanceof Type\DateTime)
            return $text->format('Y-m-d H:i:s');
        if ($text instanceof Type\Date)
            return $text->format('Y-m-d H:i:s');
        if ($text instanceof Type\DateTime)
            return $text->toString();
        return $text;
    }

    public function decode($text, $textType = "")
    {
        return $text;
    }

}
