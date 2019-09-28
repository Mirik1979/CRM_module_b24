<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 14.08.2019
 * Time: 1:47
 */

namespace local\Domain\Entity;

use JsonSerializable;
use Exception;

class CommCompany implements JsonSerializable
{

    /**
     * @return array|mixed
     * @throws Exception
     */
    public function jsonSerialize() {
        $result = array(
            'id' => $this->id,
            'ContactId' => $this->ContactId,
            'ActivityId' => $this->ActivityId,
            'Description' => $this->Description,
            'Title' => $this->Title,
        );
        return $result;
    }

    /**
     * @var int $id
     */
    private $id;

    /**
     * @var int $ContactId
     */
    private $ContactId;

    /**
     * @var int $ActivityId
     */
    private $ActivityId;

    /**
     * @var string $Description
     */
    private $Description;

    /**
     * @var string $Title
     */
    private $Title;

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
    public function getContactId()
    {
        return $this->ContactId;
    }

    /**
     * @param int $ContactId
     */
    public function setContactId($ContactId)
    {
        $this->ContactId = $ContactId;
    }

    /**
     * @return int
     */
    public function getActivityId()
    {
        return $this->ActivityId;
    }

    /**
     * @param int $ActivityId
     */
    public function setActivityId($ActivityId)
    {
        $this->ActivityId = $ActivityId;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription($Description)
    {
        $this->Description = $Description;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->Title;
    }

    /**
     * @param string $Title
     */
    public function setTitle($Title)
    {
        $this->Title = $Title;
    }

}