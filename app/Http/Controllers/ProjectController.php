<?php

namespace App\Http\Controllers;

use App\Scopes\ActiveScope;
use Carbon\Carbon;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Helper\Files;
use App\Helper\Reply;
use App\Models\Pinned;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\SubTask;
use App\Models\Currency;
use App\Models\TaskUser;
use App\Models\ProjectFile;
use Illuminate\Http\Request;
use App\Models\ProjectMember;
use App\Imports\ProjectImport;
use App\Jobs\ImportProjectJob;
use App\Models\MessageSetting;
use App\Models\ProjectActivity;
use App\Models\ProjectCategory;
use App\Models\ProjectTemplate;
use App\Models\TaskboardColumn;
use App\Traits\ProjectProgress;
use App\DataTables\TasksDataTable;
use App\Models\DiscussionCategory;
use Illuminate\Support\Facades\DB;
use App\Models\ProjectTimeLogBreak;
use Illuminate\Support\Facades\Bus;
use Maatwebsite\Excel\Facades\Excel;
use App\DataTables\ExpensesDataTable;
use App\DataTables\InvoicesDataTable;
use App\DataTables\PaymentsDataTable;
use App\DataTables\ProjectsDataTable;
use App\DataTables\TimeLogsDataTable;
use App\DataTables\DiscussionDataTable;
use Illuminate\Support\Facades\Artisan;
use Maatwebsite\Excel\HeadingRowImport;
use App\DataTables\ProjectNotesDataTable;
use App\Http\Requests\Project\StoreProject;
use App\DataTables\ArchiveProjectsDataTable;
use App\DataTables\ArchiveTasksDataTable;
use App\Http\Requests\Project\UpdateProject;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use App\Http\Requests\Admin\Employee\ImportRequest;
use App\Http\Requests\Admin\Employee\ImportProcessRequest;
use App\Models\Module;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\PermissionType;
use App\Models\ProjectNote;
use App\Models\ProjectSetting;
use App\Models\ProjectStatusSetting;
use App\Models\Role;
use App\Models\UserPermission;

class ProjectController extends AccountBaseController
{

    use ProjectProgress;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.projects';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('projects', $this->user->modules));

            return $next($request);
        });
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ProjectsDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_projects');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        if (!request()->ajax()) {

            if (in_array('client', user_roles())) {
                $this->clients = User::client();
            }
            else {
                $this->clients = User::allClients();
                $this->allEmployees = User::allEmployees(null, true, ($viewPermission == 'all' ? 'all' : null));
            }

            $this->categories = ProjectCategory::all();
            $this->departments = Team::all();
            $this->projectStatus = ProjectStatusSetting::where('status', 'active')->get();
        }

        return $dataTable->render('projects.index', $this->data);

    }

    /**
     * XXXXXXXXXXX
     *
     * @return array
     */
    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);

            return Reply::success(__('messages.deleteSuccess'));
        case 'archive':
            $this->archiveRecords($request);

            return Reply::success(__('messages.projectArchiveSuccessfully'));
        case 'change-status':
            $this->changeStatus($request);

            return Reply::success(__('messages.statusUpdatedSuccessfully'));
        default:
            return Reply::error(__('messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_projects') != 'all');

        Project::withTrashed()->whereIn('id', explode(',', $request->row_ids))->forceDelete();

        $items = explode(',', $request->row_ids);

        foreach ($items as $item) {
            // Delete project files
            Files::deleteDirectory(ProjectFile::FILE_PATH . '/' . $item);
        }
    }

    protected function archiveRecords($request)
    {
        abort_403(user()->permission('edit_projects') != 'all');

        Project::whereIn('id', explode(',', $request->row_ids))->delete();
    }

    public function archiveDestroy($id)
    {
        Project::destroy($id);

        return Reply::success(__('messages.projectArchiveSuccessfully'));
    }

    protected function changeStatus($request)
    {
        abort_403(user()->permission('edit_projects') != 'all');

        Project::whereIn('id', explode(',', $request->row_ids))->update(['status' => $request->status]);
    }

    public function updateStatus(Request $request, $id)
    {
        Project::findOrFail($id)
            ->update([
                'status' => $request->status,
            ]);

        return Reply::success(__('messages.statusUpdatedSuccessfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $project = Project::withTrashed()->findOrFail($id);
        $this->deletePermission = user()->permission('delete_projects');
        abort_403(!($this->deletePermission == 'all' || ($this->deletePermission == 'added' && $project->added_by == user()->id)));

        // Delete project files
        Files::deleteDirectory(ProjectFile::FILE_PATH . '/' . $id);
        $project->forceDelete();

        return Reply::success(__('messages.projectDeleted'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->addPermission = user()->permission('add_projects');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->pageTitle = __('app.add') . ' ' . __('app.project');
        $this->clients = User::allClients(null, true, ($this->addPermission == 'all' ? 'all' : null));
        $this->categories = ProjectCategory::all();
        $this->templates = ProjectTemplate::all();
        $this->currencies = Currency::all();
        $this->teams = Team::all();
        $this->employees = User::allEmployees(null, true, ($this->addPermission == 'all' ? 'all' : null));
        $this->redirectUrl = request()->redirectUrl;

        $this->projectTemplate = request('template') ? ProjectTemplate::with('projectMembers')->findOrFail(request('template')) : null;

        if ($this->projectTemplate) {
            $this->projectTemplateMembers = $this->projectTemplate->projectMembers ? $this->projectTemplate->projectMembers->pluck('id')->toArray() : null;
        }

        $project = new Project();

        if (!empty($project->getCustomFieldGroupsWithFields())) {
            $this->fields = $project->getCustomFieldGroupsWithFields()->fields;
        }

        if (in_array('client', user_roles())) {
            $this->client = User::withoutGlobalScope(ActiveScope::class)->findOrFail(user()->id);

        }
        else {
            $this->client = isset(request()->default_client) ? User::withoutGlobalScope(ActiveScope::class)->findOrFail(request()->default_client) : null;
        }

        if (request()->ajax()) {
            $html = view('projects.ajax.create', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'projects.ajax.create';

        return view('projects.create', $this->data);

    }

    /**
     * @param StoreProject $request
     * @return array|mixed
     * @throws \Throwable
     */
    public function store(StoreProject $request)
    {
        $this->addPermission = user()->permission('add_projects');

        abort_403(!in_array($this->addPermission, ['all', 'added']));

        DB::beginTransaction();

        try {

            $startDate = Carbon::createFromFormat($this->company->date_format, $request->start_date)->format('Y-m-d');
            $deadline = !$request->has('without_deadline') ? Carbon::createFromFormat($this->company->date_format, $request->deadline)->format('Y-m-d') : null;


            $project = new Project();
            $project->project_name = $request->project_name;
            $project->project_short_code = $request->project_code;

            $project->project_summary = trim_editor($request->project_summary);

            $project->start_date = $startDate;
            $project->deadline = $deadline;

            if ($request->category_id != '') {
                $project->category_id = $request->category_id;
            }

            $project->client_id = $request->client_id;
            $request->client_view_task = $request->client_view_task ? 'enable' : 'disable';
            $project->allow_client_notification = ($request->client_view_task) && ($request->client_task_notification) ? 'enable' : 'disable';
            $request->manual_timelog = $request->manual_timelog ? 'enable' : 'disable';

            if ($request->team_id > 0) {
                $project->team_id = $request->team_id;
            }

            $project->project_budget = $request->project_budget;
            $project->currency_id = $request->currency_id != '' ? $request->currency_id : company()->currency_id;
            $project->hours_allocated = $request->hours_allocated;

            $defaultsStatus = ProjectStatusSetting::where('default_status', 1)->get();

            foreach ($defaultsStatus as $default) {
                $project->status = $default->status_name;
            }

            if ($request->public) {
                $project->public = $request->public ? 1 : 0;
            }

            $project->miro_board_id = $request->miro_board_id;
            $project->client_access = $request->has('client_access') && $request->client_access ? 1 : 0;
            $project->enable_miroboard = $request->has('miroboard_checkbox') && $request->miroboard_checkbox ? 1 : 0;
            $project->notes = trim_editor($request->notes);

            $project->save();

            if ($request->notes != '') {
                $project->notes()->create([
                    'title' => 'Note',
                    'details' => $request->notes,
                    'client_id' => $request->client_id,
                ]);
            }

            $this->logSearchEntry($project->id, $project->project_name, 'projects.show', 'project');
            $this->logProjectActivity($project->id, 'messages.addedAsNewProject');

            if ($request->template_id) {
                $template = ProjectTemplate::with('projectMembers')->findOrFail($request->template_id);

                foreach ($template->tasks as $task) {

                    $projectTask = new Task();
                    $projectTask->project_id = $project->id;
                    $projectTask->heading = $task->heading;
                    $projectTask->task_category_id = $task->project_template_task_category_id;
                    $projectTask->description = trim_editor($task->description);
                    $projectTask->start_date = $startDate;
                    $projectTask->due_date = $deadline;
                    $projectTask->is_private = 0;
                    $projectTask->save();

                    foreach ($task->usersMany as $value) {
                        TaskUser::create(
                            [
                                'user_id' => $value->id,
                                'task_id' => $projectTask->id
                            ]
                        );
                    }

                    foreach ($task->subtasks as $value) {
                        $projectTask->subtasks()->create(['title' => $value->title]);
                    }
                }
            }

            // To add custom fields data
            if ($request->get('custom_fields_data')) {
                $project->updateCustomFieldData($request->get('custom_fields_data'));
            }

            // Commit Transaction
            DB::commit();


            $redirectUrl = urldecode($request->redirect_url);

            if ($redirectUrl == '') {
                $redirectUrl = route('projects.index');
            }

            return Reply::dataOnly(['projectID' => $project->id, 'redirectUrl' => $redirectUrl]);

        } catch (\Swift_TransportException $e) {
            // Rollback Transaction
            DB::rollback();

            return Reply::error('Please configure SMTP details to add project. Visit Settings -> notification setting to set smtp' . $e->getMessage(), 'smtp_error');
        } catch (\Exception $e) {
            // Rollback Transaction
            DB::rollback();

            return Reply::error('Some error occurred when inserting the data. Please try again or contact support' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $this->project = Project::with('client', 'members', 'members.user', 'members.user.session', 'members.user.employeeDetail.designation', 'milestones', 'milestones.currency')
            ->withTrashed()
            ->findOrFail($id)
            ->withCustomFields();

        $memberIds = $this->project->members->pluck('user_id')->toArray();

        $this->editPermission = user()->permission('edit_projects');
        $this->editProjectMembersPermission = user()->permission('edit_project_members');

        abort_403(!(
            $this->editPermission == 'all'
            || ($this->editPermission == 'added' && user()->id == $this->project->added_by)
            || ($this->editPermission == 'owned' && user()->id == $this->project->client_id && in_array('client', user_roles()))
            || ($this->editPermission == 'owned' && in_array(user()->id, $memberIds) && in_array('employee', user_roles()))
            || ($this->editPermission == 'both' && (user()->id == $this->project->client_id || user()->id == $this->project->added_by))
            || ($this->editPermission == 'both' && in_array(user()->id, $memberIds) && in_array('employee', user_roles()))
        ));

        $this->pageTitle = __('app.update') . ' ' . __('app.project');

        if (!empty($this->project->getCustomFieldGroupsWithFields())) {
            $this->fields = $this->project->getCustomFieldGroupsWithFields()->fields;
        }

        $this->clients = User::allClients(null, true, ($this->editPermission == 'all' ? 'all' : null));
        $this->categories = ProjectCategory::all();
        $this->currencies = Currency::all();
        $this->teams = Team::all();
        $this->projectStatus = ProjectStatusSetting::where('status', 'active')->get();

        $this->employees = '';

        if ($this->editPermission == 'all' || $this->editProjectMembersPermission == 'all') {
            $this->employees = User::allEmployees(null, null, ($this->editPermission == 'all' ? 'all' : null));
        }

        if (request()->ajax()) {
            $html = view('projects.ajax.edit', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }


        abort_403(user()->permission('edit_projects') == 'added' && $this->project->added_by != user()->id);
        $this->view = 'projects.ajax.edit';

        return view('projects.create', $this->data);

    }

    /**
     * @param UpdateProject $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(UpdateProject $request, $id)
    {
        $project = Project::findOrFail($id);
        $project->project_name = $request->project_name;
        $project->project_short_code = $request->project_code;

        $project->project_summary = trim_editor($request->project_summary);

        $project->start_date = Carbon::createFromFormat($this->company->date_format, $request->start_date)->format('Y-m-d');

        if (!$request->has('without_deadline')) {
            $project->deadline = Carbon::createFromFormat($this->company->date_format, $request->deadline)->format('Y-m-d');
        }
        else {
            $project->deadline = null;
        }

        if ($request->notes != '') {
            $project->notes = trim_editor($request->notes);
        }

        if ($request->category_id != '') {
            $project->category_id = $request->category_id;
        }

        if ($request->client_view_task) {
            $project->client_view_task = 'enable';
        }
        else {
            $project->client_view_task = 'disable';
        }

        if ($request->client_task_notification) {
            $project->allow_client_notification = 'enable';
        }
        else {
            $project->allow_client_notification = 'disable';
        }

        if ($request->manual_timelog) {
            $project->manual_timelog = 'enable';
        }
        else {
            $project->manual_timelog = 'disable';
        }

        $project->team_id = null;

        if ($request->team_id > 0) {
            $project->team_id = $request->team_id;
        }

        $project->client_id = ($request->client_id == 'null' || $request->client_id == '') ? null : $request->client_id;

        if ($request->calculate_task_progress) {
            $project->calculate_task_progress = 'true';
            $project->completion_percent = $this->calculateProjectProgress($id, 'true');
        }
        else {
            $project->calculate_task_progress = 'false';
            $project->completion_percent = $request->completion_percent;
        }

        $project->project_budget = $request->project_budget;
        $project->currency_id = $request->currency_id != '' ? $request->currency_id : company()->currency_id;
        $project->hours_allocated = $request->hours_allocated;
        $project->status = $request->status;

        $project->miro_board_id = $request->miro_board_id;

        if ($request->has('miroboard_checkbox')) {
            $project->client_access = $request->has('client_access') && $request->client_access ? 1 : 0;
        }
        else {
            $project->client_access = 0;
        }

        $project->enable_miroboard = $request->has('miroboard_checkbox') && $request->miroboard_checkbox ? 1 : 0;

        if ($request->public) {
            $project->public = 1;
        }

        if ($request->private) {
            $project->public = 0;
        }


        if (!$request->private && !$request->public && $request->member_id) {
            $project->projectMembers()->sync($request->member_id);
        }

        $project->save();


        // To add custom fields data
        if ($request->get('custom_fields_data')) {
            $project->updateCustomFieldData($request->get('custom_fields_data'));
        }

        $this->logProjectActivity($project->id, 'modules.projects.projectUpdated');

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('projects.index');
        }

        return Reply::successWithData(__('messages.projectUpdated'), ['projectID' => $project->id, 'redirectUrl' => $redirectUrl]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->viewPermission = user()->permission('view_projects');
        $viewFilePermission = user()->permission('view_project_files');
        $this->viewMiroboardPermission = user()->permission('view_miroboard');
        $viewMilestonePermission = user()->permission('view_project_milestones');
        $this->viewPaymentPermission = user()->permission('view_project_payments');
        $this->viewProjectTimelogPermission = user()->permission('view_project_timelogs');
        $this->viewExpensePermission = user()->permission('view_project_expenses');
        $this->viewRatingPermission = user()->permission('view_project_rating');
        $this->viewBurndownChartPermission = user()->permission('view_project_burndown_chart');
        $this->viewProjectMemberPermission = user()->permission('view_project_members');

        $this->project = Project::with(['client', 'members', 'members.user', 'members.user.session', 'members.user.employeeDetail.designation', 'milestones' => function ($q) use ($viewMilestonePermission) {
            if ($viewMilestonePermission == 'added') {
                $q->where('added_by', user()->id);
            }
        },
            'milestones.currency', 'files' => function ($q) use ($viewFilePermission) {
                if ($viewFilePermission == 'added') {
                    $q->where('added_by', user()->id);
                }
            }])
            ->withTrashed()
            ->findOrFail($id)
            ->withCustomFields();

        $this->projectStatusColor = ProjectStatusSetting::where('status_name', $this->project->status)->first();

        $memberIds = $this->project->members->pluck('user_id')->toArray();
        abort_403(!(
            $this->viewPermission == 'all'
            || $this->project->public
            || $this->viewProjectMemberPermission == 'all'
            || ($this->viewPermission == 'added' && user()->id == $this->project->added_by)
            || ($this->viewPermission == 'owned' && user()->id == $this->project->client_id && in_array('client', user_roles()))
            || ($this->viewPermission == 'owned' && in_array(user()->id, $memberIds) && in_array('employee', user_roles()))
            || ($this->viewPermission == 'both' && (user()->id == $this->project->client_id || user()->id == $this->project->added_by))
            || ($this->viewPermission == 'both' && (in_array(user()->id, $memberIds) || user()->id == $this->project->added_by) && in_array('employee', user_roles()))
        ));

        $this->pageTitle = ucfirst($this->project->project_name);

        if (!empty($this->project->getCustomFieldGroupsWithFields())) {
            $this->fields = $this->project->getCustomFieldGroupsWithFields()->fields;
        }

        $this->messageSetting = MessageSetting::first();
        $this->projectStatus = ProjectStatusSetting::where('status', 'active')->get();

        $tab = request('tab');

        switch ($tab) {
        case 'members':
            abort_403(!(
                $this->viewProjectMemberPermission == 'all'
            ));
            $this->view = 'projects.ajax.members';
            break;
        case 'milestones':
            $this->view = 'projects.ajax.milestones';
            break;
        case 'taskboard':
            session()->forget('pusher_settings');
            $this->view = 'projects.ajax.taskboard';
            break;
        case 'tasks':
            $this->taskBoardStatus = TaskboardColumn::all();

            return (!$this->project->trashed()) ? $this->tasks($this->project->project_admin == user()->id) : $this->archivedTasks($this->project->project_admin == user()->id);
        case 'gantt':
            $this->taskBoardStatus = TaskboardColumn::all();
            $this->view = 'projects.ajax.gantt';
            break;
        case 'invoices':
            return $this->invoices();
        case 'files':
            $this->view = 'projects.ajax.files';
            break;
        case 'timelogs':
            return $this->timelogs($this->project->project_admin == user()->id);
        case 'expenses':
            return $this->expenses();
        case 'miroboard';
            abort_403(!in_array($this->viewMiroboardPermission, ['all']) || !$this->project->enable_miroboard &&
                ((!in_array('client', user_roles()) && !$this->project->client_access && $this->project->client_id != user()->id)));
            $this->view = 'projects.ajax.miroboard';
            break;
        case 'payments':
            return $this->payments();
        case 'discussion':
            $this->discussionCategories = DiscussionCategory::orderBy('order', 'asc')->get();

            return $this->discussions($this->project->project_admin == user()->id);
        case 'notes':
            return $this->notes($this->project->project_admin == user()->id);
        case 'rating':
            return $this->rating($this->project->project_admin == user()->id);
        case 'burndown-chart':
            $this->fromDate = now($this->company->timezone)->startOfMonth();
            $this->toDate = now($this->company->timezone);

            return $this->burndownChart($this->project);
        case 'activity':
            $this->activities = ProjectActivity::getProjectActivities($id, 10);
            $this->view = 'projects.ajax.activity';
            break;
        default:
            $this->taskChart = $this->taskChartData($id);
            $this->hoursBudgetChart = $this->hoursBudgetChartData($this->project);
            $this->amountBudgetChart = $this->amountBudgetChartData($this->project);
            $this->taskBoardStatus = TaskboardColumn::all();
            $this->earnings = Payment::where('status', 'complete')
                ->where('project_id', $id)
                ->sum('amount');
            $hoursLogged = $this->project->times()->sum('total_minutes');

            $breakMinutes = ProjectTimeLogBreak::projectBreakMinutes($id);

            $this->hoursLogged = intdiv($hoursLogged - $breakMinutes, 60);
            $this->expenses = Expense::where(['project_id' => $id, 'status' => 'approved'])->sum('price');
            $this->view = 'projects.ajax.overview';
            break;
        }


        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->activeTab = $tab ?: 'overview';

        return view('projects.show', $this->data);

    }

    /**
     * XXXXXXXXXXX
     *
     * @return array
     */
    public function taskChartData($id)
    {
        $taskStatus = TaskboardColumn::all();
        $data['labels'] = $taskStatus->pluck('column_name');
        $data['colors'] = $taskStatus->pluck('label_color');
        $data['values'] = [];

        foreach ($taskStatus as $label) {
            $data['values'][] = Task::where('project_id', $id)->where('tasks.board_column_id', $label->id)->count();
        }

        return $data;
    }

    /**
     * XXXXXXXXXXX
     *
     * @return array
     */
    public function hoursBudgetChartData($project)
    {
        $hoursBudget = $project->hours_allocated ? $project->hours_allocated : 0;
        $hoursLogged = $project->times()->sum('total_minutes');
        $hoursLogged = intdiv($hoursLogged, 60);
        $overRun = $hoursLogged - $hoursBudget;
        $overRun = $overRun < 0 ? 0 : $overRun;

        $data['labels'] = [__('app.planned'), __('app.actual')];
        $data['colors'] = ['#2cb100', '#d30000'];
        $data['threshold'] = $hoursBudget;
        $dataset = [
            [
                'name' => __('app.planned'),
                'values' => [$hoursBudget, $hoursBudget],
            ],
            [
                'name' => __('app.overrun'),
                'values' => [0, $overRun],
            ],
        ];
        $data['datasets'] = $dataset;

        return $data;
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function amountBudgetChartData($project)
    {
        $amountBudget = $project->project_budget ?: 0;
        $earnings = Payment::where('status', 'complete')
            ->where('project_id', $project->id)
            ->sum('amount');
        $plannedOverun = $earnings < $amountBudget ? $earnings : $amountBudget;
        $overRun = $earnings - $amountBudget;
        $overRun = $overRun < 0 ? 0 : $overRun;

        $data['labels'] = [__('app.planned'), __('app.actual')];
        $data['colors'] = ['#2cb100', '#d30000'];
        $data['threshold'] = $amountBudget;
        $dataset = [
            [
                'name' => __('app.planned'),
                'values' => [$amountBudget, $plannedOverun],
            ],
            [
                'name' => __('app.overrun'),
                'values' => [0, $overRun],
            ],
        ];
        $data['datasets'] = $dataset;

        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function storePin(Request $request)
    {
        $pinned = new Pinned();
        $pinned->task_id = $request->task_id;
        $pinned->project_id = $request->project_id;
        $pinned->save();

        return Reply::success(__('messages.pinnedSuccess'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return array
     */
    public function destroyPin(Request $request, $id)
    {
        Pinned::where('project_id', $id)->where('user_id', user()->id)->delete();

        return Reply::success(__('messages.pinnedRemovedSuccess'));
    }

    public function assignProjectAdmin(Request $request)
    {
        $userId = $request->userId;
        $projectId = $request->projectId;
        $project = Project::findOrFail($projectId);
        $project->project_admin = $userId;
        $project->save();

        return Reply::success(__('messages.roleAssigned'));
    }

    public function tasks($projectAdmin = false)
    {
        $dataTable = new TasksDataTable();

        if (!$projectAdmin) {
            $viewPermission = user()->permission('view_project_tasks');
            abort_403(!in_array($viewPermission, ['all', 'added', 'owned']));

            $viewPermission = user()->permission('view_tasks');
            abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        }

        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        $this->view = 'projects.ajax.tasks';

        return $dataTable->render('projects.show', $this->data);

    }

    public function archivedTasks($projectAdmin = false)
    {
        $dataTable = new ArchiveTasksDataTable();

        if (!$projectAdmin) {
            $viewPermission = user()->permission('view_project_tasks');
            abort_403(!in_array($viewPermission, ['all', 'added', 'owned']));

            $viewPermission = user()->permission('view_tasks');
            abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        }

        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        $this->view = 'projects.ajax.tasks';

        return $dataTable->render('projects.show', $this->data);

    }

    public function ganttData()
    {
        $id = request('projectID');
        $assignedTo = request('assignedTo');
        $projectTask = request('projectTask');
        $taskStatus = request('taskStatus');
        $milestones = request('milestones');
        $withoutDueDate = false;

        if ($assignedTo != 'all') {
            $tasks = Task::projectTasks($id, $assignedTo, null, $withoutDueDate);
        }
        else {
            $tasks = Task::projectTasks($id, null, null, $withoutDueDate);
        }

        if ($projectTask) {
            $tasks = $tasks->whereIn('id', explode(',', $projectTask));
        }

        if ($taskStatus) {
            $tasks = $tasks->whereIn('board_column_id', explode(',', $taskStatus));
        }

        if ($milestones != '') {
            $tasks = $tasks->whereIn('milestone_id', explode(',', $milestones));
        }

        $data = array();

        $count = 0;

        foreach ($tasks as $task) {
            $data[$count] = [
                'id' => 'task-' . $task->id,
                'name' => ucfirst($task->heading),
                'start' => ((!is_null($task->start_date)) ? $task->start_date->format('Y-m-d') : ((!is_null($task->due_date)) ? $task->due_date->format('Y-m-d') : null)),
                'end' => (!is_null($task->due_date)) ? $task->due_date->format('Y-m-d') : $task->start_date->format('Y-m-d'),
                'progress' => 0,
                'bg_color' => $task->boardColumn->label_color,
                'taskid' => $task->id,
                'draggable' => true
            ];

            if (!is_null($task->dependent_task_id)) {
                $data[$count]['dependencies'] = 'task-' . $task->dependent_task_id;
            }

            $count++;
        }

        return response()->json($data);
    }

    public function invoices()
    {
        $dataTable = new InvoicesDataTable;
        $viewPermission = user()->permission('view_project_invoices');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned']));

        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        $this->view = 'projects.ajax.invoices';

        return $dataTable->render('projects.show', $this->data);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function invoiceList($id)
    {
        $options = '<option value="">--</option>';

        $viewPermission = user()->permission('view_invoices');

        if (($viewPermission == 'all' || $viewPermission == 'added')) {

            if ($id != 0) {
                $invoices = Invoice::with('payment', 'currency')->where('project_id', $id)->pending()->get();
            }
            else {
                $invoices = Invoice::with('payment')->where(function ($q) {
                    $q->where('status', 'unpaid')
                        ->orWhere('status', 'partial');
                })->get();
            }

            foreach ($invoices as $item) {
                $paidAmount = $item->amountPaid();

                $options .= '<option data-currency-id="' . $item->currency->id . '" data-content="' . $item->invoice_number . ' - ' . __('app.total') . ': <span class=\'text-dark f-w-500 mr-2\'>' . currency_format($item->total, $item->currency->id) . ' </span>' . __('modules.invoices.due') . ': <span class=\'text-red\'>' . currency_format(max(($item->total - $paidAmount), 0), $item->currency->id) . '</span>" value="' . $item->id . '"> ' . $item->invoice_number . ' </option>';
            }
        }

        $exchangeRate = Currency::where('id', request()->curId)->pluck('exchange_rate')->toArray();

        return Reply::dataOnly(['status' => 'success', 'data' => $options, 'exchangeRate' => $exchangeRate]);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function members($id)
    {
        $options = '';

        $project = Project::select('id', 'public')->find($id);
        $checkPublic = ($project) ? $project->public : 0;

        if ($id == 0 || $checkPublic == 1) {
            $members = User::allEmployees(null, true);

            foreach ($members as $item) {
                $self_select = (user() && user()->id == $item->id) ? '<span class=\'ml-2 badge badge-secondary\'>' . __('app.itsYou') . '</span>' : '';

                $options .= '<option  data-content="<span class=\'badge badge-pill badge-light border\'><div class=\'d-inline-block mr-1\'><img class=\'taskEmployeeImg rounded-circle\' src=' . $item->image_url . ' ></div> ' . $item->name . '' . $self_select . '</span>" value="' . $item->id . '"> ' . $item->name . '</option>';
            }

            $projectShortCode = '--';
        }
        else {
            $members = ProjectMember::with('user')->where('project_id', $id)->get();

            foreach ($members as $item) {
                $self_select = (user() && user()->id == $item->user->id) ? '<span class=\'ml-2 badge badge-secondary\'>' . __('app.itsYou') . '</span>' : '';

                $options .= '<option
                data-content="<span class=\'badge badge-pill badge-light border\'><div class=\'d-inline-block mr-1\'><img class=\'taskEmployeeImg rounded-circle\' src=' . $item->user->image_url . ' ></div> ' . $item->user->name . '' . $self_select . '</span>"
                value="' . $item->user->id . '"> ' . $item->user->name . ' </option>';
            }

            $project = Project::findOrFail($id);
            $projectShortCode = $project->project_short_code;
        }

        return Reply::dataOnly(['status' => 'success', 'unique_id' => $projectShortCode, 'data' => $options]);
    }

    public function timelogs($projectAdmin = false)
    {
        $dataTable = new TimeLogsDataTable();

        if (!$projectAdmin) {
            $viewPermission = user()->permission('view_project_timelogs');
            abort_403(!in_array($viewPermission, ['all', 'added', 'owned']));
        }

        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        $this->view = 'projects.ajax.timelogs';

        return $dataTable->render('projects.show', $this->data);
    }

    public function expenses()
    {
        $dataTable = new ExpensesDataTable();
        $viewPermission = user()->permission('view_project_expenses');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned']));

        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        $this->view = 'projects.ajax.expenses';

        return $dataTable->render('projects.show', $this->data);

    }

    public function payments()
    {
        $dataTable = new PaymentsDataTable();
        $viewPermission = user()->permission('view_project_payments');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned']));

        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        $this->view = 'projects.ajax.payments';

        return $dataTable->render('projects.show', $this->data);

    }

    public function discussions($projectAdmin = false)
    {
        $dataTable = new DiscussionDataTable();

        if (!$projectAdmin) {
            $viewPermission = user()->permission('view_project_discussions');
            abort_403(!in_array($viewPermission, ['all', 'added']));
        }

        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        $this->view = 'projects.ajax.discussion';

        return $dataTable->render('projects.show', $this->data);

    }

    public function burndown(Request $request, $id)
    {
        $this->project = Project::with(['tasks' => function ($query) use ($request) {
            if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
                $query->where(DB::raw('DATE(`start_date`)'), '>=', Carbon::createFromFormat($this->company->date_format, $request->startDate));
            }

            if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
                $query->where(DB::raw('DATE(`due_date`)'), '<=', Carbon::createFromFormat($this->company->date_format, $request->endDate));
            }

            $query->whereNotNull('due_date');
        }])->withTrashed()->findOrFail($id);

        $this->totalTask = $this->project->tasks->count();
        $datesArray = [];
        $startDate = $request->startDate ? Carbon::createFromFormat($this->company->date_format, $request->startDate) : Carbon::parse($this->project->start_date);

        if ($this->project->deadline) {
            $endDate = $request->endDate ? Carbon::createFromFormat($this->company->date_format, $request->endDate) : Carbon::parse($this->project->deadline);
        }
        else {
            $endDate = $request->endDate ? Carbon::createFromFormat($this->company->date_format, $request->endDate) : now();
        }

        for ($startDate; $startDate <= $endDate; $startDate->addDay()) {
            $datesArray[] = $startDate->format($this->company->date_format);
        }

        $uncompletedTasks = [];
        $createdTasks = [];
        $deadlineTasks = [];
        $deadlineTasksCount = [];
        $this->datesArray = json_encode($datesArray);

        foreach ($datesArray as $key => $value) {

            if (Carbon::createFromFormat($this->company->date_format, $value)->lessThanOrEqualTo(now())) {
                $uncompletedTasks[$key] = $this->project->tasks->filter(function ($task) use ($value) {

                    if (is_null($task->completed_on)) {
                        return true;
                    }

                    return $task->completed_on ? $task->completed_on->greaterThanOrEqualTo(Carbon::createFromFormat($this->company->date_format, $value)) : false;
                })->count();

                $createdTasks[$key] = $this->project->tasks->filter(function ($task) use ($value) {
                    return Carbon::createFromFormat($this->company->date_format, $value)->startOfDay()->equalTo($task->created_at->startOfDay());
                })->count();

                if ($key > 0) {
                    $uncompletedTasks[$key] += $createdTasks[$key];
                }

            }

            $deadlineTasksCount[] = $this->project->tasks->filter(function ($task) use ($value) {
                return Carbon::createFromFormat($this->company->date_format, $value)->startOfDay()->equalTo($task->due_date->startOfDay());
            })->count();

            if ($key == 0) {
                $deadlineTasks[$key] = $this->totalTask - $deadlineTasksCount[$key];
            }
            else {
                $newKey = $key - 1;
                $deadlineTasks[$key] = $deadlineTasks[$newKey] - $deadlineTasksCount[$key];
            }
        }

        $this->uncompletedTasks = json_encode($uncompletedTasks);
        $this->deadlineTasks = json_encode($deadlineTasks);

        if ($request->ajax()) {
            return $this->data;
        }

        $this->startDate = $request->startDate ? Carbon::parse($request->startDate)->format($this->company->date_format) : Carbon::parse($this->project->start_date)->format($this->company->date_format);
        $this->endDate = $endDate->format($this->company->date_format);

        return view('projects.ajax.burndown', $this->data);
    }

    public function notes($projectAdmin = false)
    {
        $dataTable = new ProjectNotesDataTable();

        if (!$projectAdmin) {
            $viewPermission = user()->permission('view_project_note');
            abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        }

        $tab = request('tab');
        $this->activeTab = $tab ?: 'profile';

        $this->view = 'projects.ajax.notes';

        return $dataTable->render('projects.show', $this->data);

    }

    public function burndownChart($project)
    {
        $viewPermission = user()->permission('view_project_burndown_chart');
        abort_403(!(in_array($viewPermission, ['all']) || $project->project_admin == user()->id));

        $tab = request('tab');
        $this->activeTab = $tab ?: 'burndown-chart';
        $this->view = 'projects.ajax.burndown';

        return view('projects.show', $this->data);

    }

    public function rating($projectAdmin)
    {

        if (!$projectAdmin) {
            $viewPermission = user()->permission('view_project_rating');
            abort_403(!in_array($viewPermission, ['all', 'added']));
        }

        $tab = request('tab');
        $this->activeTab = $tab ?: 'rating';


        $this->view = 'projects.ajax.rating';

        return view('projects.show', $this->data);

    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function archive(ArchiveProjectsDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_projects');
        abort_403($viewPermission == 'none');

        if (!request()->ajax()) {

            if (in_array('client', user_roles())) {
                $this->clients = User::client();
            }
            else {
                $this->clients = User::allClients();
                $this->allEmployees = User::allEmployees();
            }

            $this->categories = ProjectCategory::all();
            $this->departments = Team::all();
        }

        return $dataTable->render('projects.archive', $this->data);

    }

    public function archiveRestore($id)
    {
        $project = Project::withTrashed()->findOrFail($id);
        $project->restore();

        return Reply::success(__('messages.projectRevertSuccessfully'));
    }

    public function importProject()
    {
        $this->pageTitle = __('app.importExcel') . ' ' . __('app.menu.projects');

        $this->addPermission = user()->permission('add_projects');
        abort_403(!in_array($this->addPermission, ['all', 'added']));


        if (request()->ajax()) {
            $html = view('projects.ajax.import', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'projects.ajax.import';

        return view('projects.create', $this->data);
    }

    public function importStore(ImportRequest $request)
    {
        $this->file = Files::upload($request->import_file, Files::IMPORT_FOLDER, false, false, false);
        $excelData = Excel::toArray(new ProjectImport, public_path(Files::UPLOAD_FOLDER . '/' . Files::IMPORT_FOLDER . '/' . $this->file))[0];
        $this->hasHeading = $request->has('heading');
        $this->heading = array();
        $this->fileHeading = array();

        $this->columns = ProjectImport::fields();
        $this->importMatchedColumns = array();
        $this->matchedColumns = array();

        if ($this->hasHeading) {
            $this->heading = (new HeadingRowImport)->toArray(public_path(Files::UPLOAD_FOLDER . '/' . Files::IMPORT_FOLDER . '/' . $this->file))[0][0];

            // Excel Format None for get Heading Row Without Format and after change back to config
            HeadingRowFormatter::default('none');
            $this->fileHeading = (new HeadingRowImport)->toArray(public_path(Files::UPLOAD_FOLDER . '/' . Files::IMPORT_FOLDER . '/' . $this->file))[0][0];
            HeadingRowFormatter::default(config('excel.imports.heading_row.formatter'));

            array_shift($excelData);
            $this->matchedColumns = collect($this->columns)->whereIn('id', $this->heading)->pluck('id');
            $importMatchedColumns = array();

            foreach ($this->matchedColumns as $matchedColumn) {
                $importMatchedColumns[$matchedColumn] = 1;
            }

            $this->importMatchedColumns = $importMatchedColumns;
        }

        $this->importSample = array_slice($excelData, 0, 5);

        $view = view('projects.ajax.import_progress', $this->data)->render();

        return Reply::successWithData(__('messages.importUploadSuccess'), ['view' => $view]);
    }

    public function importProcess(ImportProcessRequest $request)
    {
        // clear previous import
        Artisan::call('queue:clear database --queue=import_project');
        Artisan::call('queue:flush');
        // Get index of an array not null value with key
        $columns = array_filter($request->columns, function ($value) {
            return $value !== null;
        });

        $excelData = Excel::toArray(new ProjectImport, public_path(Files::UPLOAD_FOLDER . '/' . Files::IMPORT_FOLDER . '/' . $request->file))[0];

        if ($request->has_heading) {
            array_shift($excelData);
        }

        $jobs = [];

        foreach ($excelData as $row) {

            $jobs[] = (new ImportProjectJob($row, $columns));
        }

        $batch = Bus::batch($jobs)->onConnection('database')->onQueue('import_project')->name('import_project')->dispatch();

        Files::deleteFile($request->file, Files::IMPORT_FOLDER);

        return Reply::successWithData(__('messages.importProcessStart'), ['batch' => $batch]);
    }

    public function changeProjectStatus(Request $request)
    {
        $projectId = $request->projectId;
        $statusID = $request->statusId;
        $project = Project::with('members')->findOrFail($projectId);
        $projectUsers = $project->members->pluck('user_id')->toArray();

        $this->editProjectPermission = user()->permission('edit_projects');

        abort_403(!(
            $this->editProjectPermission == 'all'
            || ($this->editProjectPermission == 'added' && user()->id == $project->added_by)
            || ($this->editProjectPermission == 'owned' && user()->id == $project->client_id && in_array('client', user_roles()))
            || ($this->editProjectPermission == 'owned' && in_array(user()->id, $projectUsers) && in_array('employee', user_roles()))
            || ($this->editProjectPermission == 'both' && (user()->id == $project->client_id || user()->id == $project->added_by))
            || ($this->editProjectPermission == 'both' && in_array(user()->id, $projectUsers) && in_array('employee', user_roles())
            )));

        $projectStatus = ProjectStatusSetting::where('status_name', $statusID)->first();

        $project->status = $projectStatus->status_name;
        $project->save();

        return Reply::success(__('messages.statusUpdatedSuccessfully'));
    }

    public function pendingTasks($id)
    {
        if ($id != 0) {
            $tasks = Task::join('task_users', 'task_users.task_id', '=', 'tasks.id')
                ->with('project')
                ->pending()
                ->where('task_users.user_id', '=', $this->user->id)
                ->where('tasks.project_id', '=', $id)
                ->select('tasks.*')
                ->get();

        }
        else {
            $tasks = Task::join('task_users', 'task_users.task_id', '=', 'tasks.id')
                ->with('project')
                ->pending()
                ->where('task_users.user_id', '=', $this->user->id)
                ->select('tasks.*')
                ->get();
        }

        $options = '<option value="">--</option>';

        foreach ($tasks as $item) {
            $name = '';

            if (!is_null($item->project_id)) {
                $name .= "<h5 class='f-12 text-darkest-grey'>" . ucfirst($item->heading) . "</h5><div class='text-muted f-11'>" . $item->project->project_name . '</div>';

            }
            else {
                $name .= "<span class='text-dark-grey f-11'>" . ucfirst($item->heading) . '</span>';
            }

            $options .= '<option data-content="' . $name . '" value="' . $item->id . '">' . $item->heading . '</option>';
        }

        return Reply::dataOnly(['status' => 'success', 'data' => $options]);

    }

    public function ajaxLoadProject(Request $request)
    {
        $search = $request->search;

        $response = [];

        if ($search) {
            $lists = Project::allProjects($search);

            foreach ($lists as $list) {
                $response[] = [
                    'id' => $list->id,
                    'text' => $list->project_name,
                ];
            }
        }


        return response()->json($response);
    }

}
