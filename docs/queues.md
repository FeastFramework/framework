[Back to Index](index.md)

# Queueable Jobs

FEAST allows easily building queueable jobs to run at a later time. To create a queueable job, create a class
at `Jobs\Queueable` that extends from `Feast\Jobs\QueueableJob`. Jobs by default are placed in the `default` queue and
will try to run a maximum of 3 tries before failing. You can either override the properties directly in your inherited
class or use the methods on the parent class to change them.

## Methods

There are several methods for working with job parameters.

1. `setQueueName` - This method sets the queuename to run on.
2. `setMaxTries` - This method sets the maximum tries before the job is marked as failed.
3. `setJobName` - This method sets the job name.
4. `getQueueName` - This method gets the queuename to run on.
5. `getMaxTries` - This method gets the maximum tries before the job is marked as failed.
6. `getJobName` - This method gets the job name.

## Storing a Job to run later

The `store` method inserts the job into the `Jobs` table on the database. All data needed to run the job MUST be part of
your job class.

## Running a Job

The `run` method on a job class will run the job. Return true on success. If the job throws an exception or the `run`
method returns false, the tries will be incremented, and the job will be retried if the max count is not exceeded.

## Listening for Jobs to run

FEAST includes a Job listener that is designed to be always on. On Linux, it is recommended to use Supervisor to manage
this process. See [feast:job:listen](cli.md#feastjoblisten) for details.

## Running a single Job

Sometimes, a job fails repeatedly or gets stuck. In that situation, FEAST includes a separate command line tool to run a
job directly. To do so, first check the `jobs` table for the `job_id`. Then, run the job
using [feast:job:run-one](cli.md#feastjobrun-one) 