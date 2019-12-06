<?php

namespace App\Http\Controllers\Api\v1\Statistic;

use App\Models\Property;
use App\Models\Task;
use App\Models\TimeInterval;
use Auth;
use Filter;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Project;
use DB;
use Validator;
use Carbon\Carbon;
use Modules\Reports\Entities\ProjectReport;

class ProjectReportController extends ReportController
{
    /**
     * @var
     */
    protected $timezone;

    /**
     * ProjectReportController constructor.
     */
    public function __construct()
    {
        $companyTimezoneProperty = Property::getProperty('company', 'TIMEZONE')->first();
        $this->timezone = $companyTimezoneProperty ? $companyTimezoneProperty->getAttribute('value') : 'UTC';

        parent::__construct();
    }

    /**
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'uids' => 'exists:users,id|array',
            'pids' => 'exists:projects,id|array',
            'start_at' => 'required|date',
            'end_at' => 'required|date',
        ];
    }

    /**
     * @return string
     */
    public function getEventUniqueNamePart(): string
    {
        return 'project-report';
    }

    /**
     * @return array
     */
    public static function getControllerRules(): array
    {
        return [
            'report' => 'project-report.list',
            'projects' => 'project-report.projects',
            'task' => 'project-report.list',
            'days' => 'time-duration.list',
            'screenshots' => 'project-report.screenshots'
        ];
    }

    /**
     * [report description]
     *
     * @param  Request  $request
     *
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function report(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            Filter::process(
                $this->getEventUniqueName('validation.report.show'),
                $this->getValidationRules()
            )
        );

        if ($validator->fails()) {
            return response()->json(
                Filter::process(
                    $this->getEventUniqueName('answer.error.report.show'), [
                        'error' => 'Validation fail',
                        'reason' => $validator->errors()
                    ], 400)
            );
        }

        $uids = $request->input('uids', []);
        $pids = $request->input('pids', []);

        $timezone = $this->timezone;
        $timezoneOffset = (new Carbon())->setTimezone($timezone)->format('P');

        $startAt = Carbon::parse($request->input('start_at'), $timezone)
            ->tz('UTC')
            ->toDateTimeString();

        $endAt = Carbon::parse($request->input('end_at'), $timezone)
            ->tz('UTC')
            ->toDateTimeString();

        $projectReports = ProjectReport::with('task.timeIntervals')
            ->select('user_id', 'user_name', 'task_id', 'project_id', 'task_name', 'project_name',
                DB::raw("DATE(CONVERT_TZ(date, '+00:00', '{$timezoneOffset}')) as date"),
                DB::raw('SUM(duration) as duration')
            )
            ->whereIn('user_id', $uids)
            ->whereIn('project_id', $pids)
            ->whereIn('project_id', Project::getUserRelatedProjectIds(Auth::user()))
            ->where('date', '>=', $startAt)
            ->where('date', '<=', $endAt)
            ->groupBy('user_id', 'user_name', 'task_id', 'project_id', 'task_name', 'project_name')
            ->get();

        $projects = [];

        foreach ($projectReports as $projectReport) {
            $project_id = $projectReport->project_id;
            $user_id = $projectReport->user_id;

            if (!isset($projects[$project_id])) {
                $projects[$project_id] = [
                    'id' => $project_id,
                    'name' => $projectReport->project_name,
                    'users' => [],
                    'project_time' => 0,
                ];
            }

            if (!isset($projects[$project_id]['users'][$user_id])) {
                $projects[$project_id]['users'][$user_id] = [
                    'id' => $user_id,
                    'full_name' => $projectReport->user_name,
                    'tasks' => [],
                    'tasks_time' => 0,
                ];
            }

            if (isset($projectReport->task)) {
                $screenshots = $projectReport->task->timeIntervals()->with('screenshot')
                    ->where('start_at', '>=', $startAt)->where('end_at', '<=', $endAt)->get()
                    ->pluck('screenshot')
                    ->groupBy(function ($s) {
                        return Carbon::parse($s['created_at'])->startOfDay()->format('Y-m-d');
                    })
                    ->transform(function ($item, $k) {
                        return $item->groupBy(function ($screen) {
                            return Carbon::parse($screen['created_at'])->startOfHour()->format('h:i');
                        });
                    });
            } else {
                $screenshots = [];
            }

            $projects[$project_id]['users'][$user_id]['tasks'][] = [
                'id' => $projectReport->task_id,
                'project_id' => $projectReport->project_id,
                'user_id' => $projectReport->user_id,
                'task_name' => $projectReport->task_name,
                'duration' => (int) $projectReport->duration,
                'screenshots' => $screenshots,
            ];

            $projects[$project_id]['users'][$user_id]['tasks_time'] += $projectReport->duration;
            $projects[$project_id]['project_time'] += $projectReport->duration;
        }


        foreach ($projects as $project_id => $project) {
            $projects[$project_id]['users'] = array_values($project['users']);
        }

        $projects = array_values($projects);


        return response()->json(
            Filter::process(
                $this->getEventUniqueName('answer.success.report.show'),
                $projects
            )
        );
    }

    /**
     * [events description]
     *
     * @param  Request  $request  [description]
     *
     * @return \Illuminate\Http\JsonResponse [description]
     */
    public function days(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            Filter::process(
                $this->getEventUniqueName('validation.report.show'),
                $this->getValidationRules()
            )
        );

        if ($validator->fails()) {
            return response()->json(
                Filter::process(
                    $this->getEventUniqueName('answer.error.report.show'), [
                    'error' => 'Validation fail',
                    'reason' => $validator->errors()
                ], 400)
            );
        }

        $uids = $request->input('uids', []);

        $timezone = $this->timezone;
        $timezoneOffset = (new Carbon())->setTimezone($timezone)->format('P');

        $startAt = Carbon::parse($request->input('start_at'), $timezone)
            ->tz('UTC')
            ->toDateTimeString();

        $endAt = Carbon::parse($request->input('end_at'), $timezone)
            ->tz('UTC')
            ->toDateTimeString();

        $days = ProjectReport::query()
            ->select('user_id', 'date',
                DB::raw("DATE(CONVERT_TZ(date, '+00:00', '{$timezoneOffset}')) as date"),
                DB::raw('SUM(duration) as duration')
            )
            ->whereIn('project_id', Project::getUserRelatedProjectIds($user))
            ->where('date', '>=', $startAt)
            ->where('date', '<', $endAt)
            ->groupBy('user_id', 'date');

        if (!empty($uids)) {
            $days->whereIn('user_id', $uids);
        }

        $days = $days->get();

        return response()->json(
            Filter::process(
                $this->getEventUniqueName('answer.success.report.show'),
                $days
            )
        );
    }

    /**
     * @param  Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function projects(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            $this->getValidationRules()
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    'error' => 'Validation fail',
                    'reason' => $validator->errors()
                ], 400
            );
        }

        $uids = $request->input('uids', []);
        // Get projects, where specified users is attached.
        $users_attached_project_ids = Project::whereHas('users', function ($query) use ($uids) {
            $query->whereIn('id', $uids);
        })->pluck('id');

        // Get projects, where specified users have intervals.
        $users_related_project_ids = Project::whereHas('tasks.timeIntervals', function ($query) use ($uids) {
            $query->whereIn('user_id', $uids);
        })->pluck('id');

        $project_ids = collect([$users_attached_project_ids, $users_related_project_ids])->collapse()->unique();

        // Get projects, directly attached to the current user.
        $attached_project_ids = Project::whereHas('users', function ($query) use ($uids) {
            $query->where('id', Auth::user()->id);
        })->pluck('id');

        // Filter projects by directly attached to the current user, if have attached.
        if ($attached_project_ids->count() > 0) {
            $project_ids = $project_ids->intersect($attached_project_ids);
        }

        // Load projects.
        $projects = Project::query()->whereIn('id', $project_ids)->get(['id', 'name']);

        return response()->json(
            Filter::process(
                $this->getEventUniqueName('answer.success.report.show'),
                $projects
            )
        );
    }

    /**
     * Returns durations per date for a task.
     *
     * @param           $id
     * @param  Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function task($id, Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            Filter::process(
                $this->getEventUniqueName('validation.report.show'),
                $this->getValidationRules()
            )
        );

        if ($validator->fails()) {
            return response()->json(
                Filter::process(
                    $this->getEventUniqueName('answer.error.report.show'), [
                    'error' => 'Validation fail',
                    'reason' => $validator->errors()
                ], 400)
            );
        }

        $uid = $request->uid;

        $timezone = $this->timezone;
        $timezoneOffset = (new Carbon())->setTimezone($timezone)->format('P'); # Format +00:00

        $startAt = Carbon::parse($request->input('start_at'), $timezone)
            ->tz('UTC')
            ->toDateTimeString();

        $endAt = Carbon::parse($request->input('end_at'), $timezone)
            ->tz('UTC')
            ->toDateTimeString();

        $report = ProjectReport::query()
            ->select(
                DB::raw("DATE(CONVERT_TZ(date, '+00:00', '{$timezoneOffset}')) as date"),
                DB::raw('SUM(duration) as duration')
            )
            ->where('task_id', $id)
            ->where('user_id', $uid)
            ->where('date', '>=', $startAt)
            ->where('date', '<', $endAt)
            ->get(['date', 'duration']);

        return response()->json(
            Filter::process(
                $this->getEventUniqueName('answer.success.report.show'),
                $report
            )
        );
    }

    /**
     * @param  Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function screenshots(Request $request)
    {
        $taskID = $request->input('task_id');
        $date = $request->input('date');

        $startDate = Carbon::parse($date);
        $endDate = clone $startDate;
        $endDate = $endDate->addDay();

        $result = TimeInterval::where('task_id', '=', $taskID)
            ->where('start_at', '>=', $startDate->toDateTimeString())
            ->where('start_at', '<', $endDate->toDateTimeString())
            ->with('screenshots')
            ->get()
            ->pluck('screenshots');

        return response()->json($result);
    }

}
