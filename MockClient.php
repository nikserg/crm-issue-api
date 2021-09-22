<?php

namespace nikserg\CRMIssueAPI;



use nikserg\CRMIssueAPI\models\IssueInfo;
use nikserg\CRMIssueAPI\models\IssueTask;

/**
 * Class MockClient
 *
 * @package nikserg\CRMIssueAPI
 */
class MockClient extends Client
{
    public function create($type, $customerFormId, $comment)
    {
        return 1;
    }

    public function getInfo($id)
    {
        $model = new IssueInfo();
        $model->inn = '1111111111';
        $model->state = IssueInfo::STATUS_SUCCESS;
        $model->type = IssueTask::TYPE_CHECK_PROLONGATION;
        $model->email = '1@1.1';
        $model->clientid = 1;
        $model->change_ts = time();
        $model->closedescription = '';
        $model->comments = [];
        $model->companyName = 'Рога и копыта';
        $model->create_ts = time();
        $model->description = '';

        return $model;
    }
}
