<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 08.10.2019
 * Time: 2:11
 */

namespace Tn\Plan\Domain\Entity;

use JsonSerializable;

class Manager implements JsonSerializable
{

    public function jsonSerialize()
    {
        return
            [
                'id'   => $this->id,
                'name' => $this->name,
                'last_name' => $this->last_name,
                'second_name' => $this->second_name,
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
     * @var string $last_name
     */
    private $last_name;

    /**
     * @var string $second_name
     */
    private $second_name;

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

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @param string $last_name
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
    }

    /**
     * @return string
     */
    public function getSecondName()
    {
        return $this->second_name;
    }

    /**
     * @param string $second_name
     */
    public function setSecondName($second_name)
    {
        $this->second_name = $second_name;
    }

}