<?php

namespace nikserg\CRMIssueAPI\models;

class IssueInfo {

    //
    // Статус выполнения
    //
    const STATUS_INWORK = -2;
    const STATUS_DELAYED = -1;
    const STATUS_INIT = 0;
    const STATUS_FAIL = 3;
    const STATUS_SUCCESS = 4;

    public $clientid;
    public $type;
    public $inn;
    public $companyName;
    public $email;
    public $description;
    public $closedescription;
    /**
     * @var array
     */
    public $comments;
    public $create_ts;
    public $change_ts;
    public $state;

    public function isDone()
    {
        return $this->state >= self::STATUS_FAIL;
    }
}