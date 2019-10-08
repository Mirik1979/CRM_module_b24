<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 07.10.2019
 * Time: 23:35
 */

namespace Tn\Plan\Domain\Entity;

use JsonSerializable;

class Year implements JsonSerializable
{
    public function jsonSerialize()
    {
        return
            [
                'id'   => $this->id,
                'year' => $this->year,
            ];
    }

    /**
     * @var int $id
     */
    private $id;

    /**
     * @var string $year
     */
    private $year;

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
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param string $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

}