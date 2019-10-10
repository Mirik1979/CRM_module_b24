<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 08.10.2019
 * Time: 2:48
 */

namespace Tn\Plan\Domain\Entity;

use JsonSerializable;

class Segment implements JsonSerializable
{
    public function jsonSerialize()
    {
        return
            [
                'id'   => $this->id,
                'name' => $this->name,
            ];
    }
    /**
     * @var int $id
     */
    private $id;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}