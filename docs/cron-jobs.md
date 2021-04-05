[Back to Index](index.md)

# Scheduled Jobs

FEAST includes the ability to run scheduled jobs without having to manage a system scheduler/cron task (besides the
scheduled runner). This utility provides a very powerful and flexible way to run re-occuring tasks.

To enable the Job runner, add [feast:job:run-cron](cli.md#feastjobrun-cron) to your system's task scheduler to run every
minute.

On Linux, add the following to your crontab, replacing `[project_root]` with the path to your project. (with `crontab -e`)

`* * * * * cd [project_root] && php famine feast:job:run-cron  >> /dev/null 2>&1`

## Creating a Scheduled Job

To create a scheduled job, first extend the `Feast\Jobs\CronJob` class. You may override any of the properties directly
if you do not need flexible scheduling, or you may use the long list of helper methods [below](#scheduling).

Once you have created your job class, you may add it to `scheduled_jobs.php`. Some samples are included commented out to
assist you.

Your job class must contain a `run` method. This method is called when all the scheduling criteria is met.

## Scheduling

### Environmental criteria

1. `environment` - This method sets the environment to run on. If not called or overridden, defaults to production.
2. `withoutOverlapping` - This method prevents the job from running concurrently to the same job. It takes an optional
   timeout parameter. The default timeout for being considered as "running" is 1440 minutes (or 1 full day).
3. `when` - This method takes a criteria expression that evaluates to either true or false. If this evaluates to false,
   the job will not run.
4. `evenInMaintenanceMode` - If this method is not called and `runInMaintenanceMode` is not true, then when the
   Application is in maintenance mode, the job will not run.
5. `timezone` - Specify the timezone for all cron rules to be compared against.
6. `runInBackground` - If this method is called and you are on a Linux/Unix system, the job will run in the background.
   Otherwise, all scheduled jobs for the current minute will run sequentially.

### Time criteria

The methods in the time criteria are mostly self-explanatory, but more details are provided below when needed. The
criteria baseline defaults to every minute. Time criteria methods may be chained for more granularity.

1. `cron` - Takes a cron string as an argument.

#### Minute based

1. `everyMinute`
2. `everyOddMinute`
3. `everyTwoMinutes` - Opposite of everyOddMinute
4. `everyThreeMinutes`
5. `everyFourMinutes`
6. `everyFiveMinutes`
7. `everyTenMinutes`
8. `everyFifteenMinutes`
9. `everyTwentyMinutes`
10. `everyThirtyMinutes`

### Hour based

1. `hourly` - Every hour on the hour (unless the minute has been set already)
2. `hourlyAt` - Takes a minute parameter. Runs every hour at that minute only.
3. `hourlyOnOddHours`
4. `everyTwoHours`
5. `everyThreeHours`
6. `everyFourHours`
7. `everySixHours`
8. `everyEightHours`
9. `everyTwelveHours`

### Day based

1. `daily` - Once a day. Defaults to 0:00 (or 12:00am) unless the hour or minute have been set already.
2. `dailyAt` - Once a day at the specified time (24-hour format. Example: 23:45).
3. `twiceDaily` - Twice a day at the specified hours (on the hour, unless the minute has been explicitly set)

### Week based

1. `weekly` - Once a week on Sunday. Defaults to 0:00 (or 12:00am) unless the hour or minute have been set already.
2. `weeklyOn` - Once a week on the specified day (0 and 7 = Sunday, 6 = Saturday) at an optional specified time (24-hour
   format. Example: 23:45).

### Day of week based

1. `weekdays`
2. `weekends`
3. `sundays`
4. `mondays`
5. `tuesdays`
6. `wednesdays`
7. `thursdays`
8. `fridays`
9. `saturdays`
10. `days` - This method takes an array of numbers 0-7. 0 and 7 are Sunday, 6 = Saturday.

### Month based

1. `monthly` - Once a month on the first of the month. Defaults to 0:00 (or 12:00am) unless the hour or minute have been
   set already.
2. `monthlyOn` - Once a month on the specified day of the month at an optional specified time (24-hour format. Example:
   23:45). Note: if the chosen day does not exist in the current month, it will be skipped.
3. `twiceMonthly` - Twice a month on the specified days at an optional specified time (24-hour format. Example: 23:45).

### Large Format

1. `quarterly` - Once per quarter (Jan, April, Jul, and October) on the 1st of the month at Midnight. If the day of the
   month, hour, or day have been set, the chosen values are kept.
2. `yearly` - Once per year in January. Defaults to the 1st at midnight. If the day of the month, hour, or day have been
   set, the chosen values are kept.
3. `yearlyOn` - Once per year on the specified day of the specified month (at an optional time). Defaults to 0:00 (or
   12:00am) unless the hour or minute have been set already.

### Time filters

1. `between` - Takes a start and end time in `hour:minute` format. The job will not run if the current time is not
   within this range.
2. `unlessBetween` - Takes a start and end time in `hour:minute` format. The job will not run if the current time is
   within this range.
   