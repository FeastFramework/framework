<?php

declare(strict_types=1);

namespace Mapper;

use Feast\BaseMapper;
use Feast\Exception\ServerFailureException;
use Feast\ServiceContainer\NotFoundException;
use Model\Job;

class JobMapper extends BaseMapper
{
    protected const OBJECT_NAME = Job::class;
    protected const PRIMARY_KEY = 'job_id';
    public const TABLE_NAME = 'jobs';

    /**
     * @param int|string $value
     * @param bool $validate
     * @return ?Job
     * @throws ServerFailureException
     * @throws NotFoundException
     */
    public function findByPrimaryKey(int|string $value, bool $validate = false): ?Job
    {
        $record = parent::findByPrimaryKey($value, $validate);
        if ($record instanceof Job) {
            return $record;
        }

        return null;
    }

    public function findOnePendingByQueues(array $queues): ?Job
    {
        $query = $this->getQueryBase()->where('status = ?', 'pending')->where(
            'tries < max_tries and queue_name IN (' . str_repeat('?,', count($queues) - 1) . '?)',
            $queues
        );
        $return = $this->fetchOne($query);
        if ($return === null || $return instanceof Job) {
            return $return;
        }
        return null;
    }

    public function markJobPendingIfAble(Job $job): bool
    {
        $query = $this->connection->update(self::TABLE_NAME, ['status' => \Feast\Jobs\QueueableJob::JOB_STATUS_RUNNING])
            ->where(
                'job_id = ? and status IN (?,?)',
                [
                    $job->job_id,
                    \Feast\Jobs\QueueableJob::JOB_STATUS_PENDING,
                    \Feast\Jobs\QueueableJob::JOB_STATUS_FAILED
                ]
            );
        $result = $query->execute();
        return $result->rowCount() !== 0;
    }
}
