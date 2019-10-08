<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 08.10.2019
 * Time: 3:19
 */

namespace Tn\Plan\Domain\Entity;

use JsonSerializable;

class ManagerPlan implements JsonSerializable
{
    public function jsonSerialize()
    {
        return
            [
                'id'   => $this->id,
                'manager_id' => $this->manager_id,
                'segment_id' => $this->segment_id,
                'year_id' => $this->year_id,
                'revenue' => $this->revenue,
            ];
    }
    /**
     * @var int $id
     */
    private $id;

    /**
     * @var int $manager_id
     */
    private $manager_id;

    /**
     * @var int $segment_id
     */
    private $segment_id;

    /**
     * @var int $year_id
     */
    private $year_id;

    /**
     * @var float $revenue
     */
    private $revenue;

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
     * @return int
     */
    public function getManagerId()
    {
        return $this->manager_id;
    }

    /**
     * @param int $manager_id
     */
    public function setManagerId($manager_id)
    {
        $this->manager_id = $manager_id;
    }

    /**
     * @return int
     */
    public function getSegmentId()
    {
        return $this->segment_id;
    }

    /**
     * @param int $segment_id
     */
    public function setSegmentId($segment_id)
    {
        $this->segment_id = $segment_id;
    }

    /**
     * @return int
     */
    public function getYearId()
    {
        return $this->year_id;
    }

    /**
     * @param int $year_id
     */
    public function setYearId($year_id)
    {
        $this->year_id = $year_id;
    }

    /**
     * @return float
     */
    public function getRevenue()
    {
        return $this->revenue;
    }

    /**
     * @param float $revenue
     */
    public function setRevenue($revenue)
    {
        $this->revenue = $revenue;
    }

}