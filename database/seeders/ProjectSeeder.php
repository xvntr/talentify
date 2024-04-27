<?php

namespace Database\Seeders;

use App\Models\CompanyAddress;
use App\Models\Currency;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\TaskboardColumn;
use App\Models\TaskUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {

        $count = config('app.seed_record_count');

        $faker = \Faker\Factory::create();

        DB::beginTransaction();

        \App\Models\Project::factory()->count((int)$count)->make()->each(function (Project $project) use ($faker, $companyId) {
            $project->company_id = $companyId;
            $project->client_id = $this->getClientId($companyId);
            $project->currency_id = $this->getCurrencyId($companyId);
            $project->category_id = $this->getCategoryId($companyId);
            $project->added_by = $this->getAdminId($companyId);
            $project->save();

            $activity = new \App\Models\ProjectActivity();
            $activity->project_id = $project->id;
            /* @phpstan-ignore-line */
            $activity->activity = $project->project_name . ' added as new project.';
            /* @phpstan-ignore-line */
            $activity->save();

            $search = new \App\Models\UniversalSearch();
            $search->searchable_id = $project->id;
            $search->company_id = $companyId;
            /* @phpstan-ignore-line */
            $search->title = $project->project_name;
            /* @phpstan-ignore-line */
            $search->route_name = 'projects.show';
            $search->save();

            $randomRange = $faker->numberBetween(1, 3);

            // Assign random members
            for ($i = 1; $i <= $randomRange; $i++) {
                $this->assignMembers($project->id, $companyId);
                /* @phpstan-ignore-line */
            }

            // Create tasks
            for ($i = 1; $i <= $randomRange; $i++) {
                $this->createTask($faker, $project, $companyId);
            }

            // Create invoice

            for ($i = 1; $i <= $randomRange; $i++) {
                $this->createInvoice($faker, $project, $companyId);
            }

            // Create project time log
            for ($i = 1; $i <= $randomRange; $i++) {
                $this->createTimeLog($faker, $project, $companyId);
            }
        });

        DB::commit();
    }

    private function getClientId($companyId)
    {
        return User::join('role_user', 'role_user.user_id', '=', 'users.id')
            ->leftJoin('client_details', 'users.id', '=', 'client_details.user_id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->select('users.id', 'users.name', 'client_details.company_name', 'users.email', 'users.created_at')
            ->where('roles.name', 'client')
            ->where('users.company_id', $companyId)
            ->inRandomOrder()
            ->first()->id;
    }

    private function getCurrencyId($companyId)
    {
        return Currency::where('company_id', $companyId)->first()->id;
    }

    private function getCategoryId($companyId)
    {
        return ProjectCategory::where('company_id', $companyId)->inRandomOrder()->first()->id;
    }

    private function getAdminId($companyId)
    {
        return User::join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', 'admin')
            ->where('users.company_id', $companyId)
            ->select('users.id')
            ->first()->id;
    }

    private function assignMembers($projectId, $companyId)
    {
        $admin = User::join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', 'admin')
            ->select('users.id')
            ->where('users.company_id', $companyId)
            ->first();

        $employeeId = $this->getRandomEmployee($companyId);

        // Assign member
        $member = new \App\Models\ProjectMember();
        $member->user_id = $employeeId->id;
        $member->project_id = $projectId;
        $member->added_by = $admin->id;
        $member->last_updated_by = $admin->id;
        $member->hourly_rate = $employeeId->hourly_rate;
        $member->save();

        $activity = new \App\Models\ProjectActivity();
        $activity->project_id = $projectId;
        $activity->activity = 'New member added to the project.';
        $activity->save();
    }

    private function getRandomEmployee($companyId)
    {
        return User::select('users.id as id', 'employee_details.hourly_rate')
            ->join('employee_details', 'users.id', '=', 'employee_details.user_id')
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', 'employee')
            ->where('users.company_id', $companyId)
            ->inRandomOrder()
            ->first();
    }

    private function createTask($faker, $project, $companyId)
    {
        $assignee = \App\Models\ProjectMember::inRandomOrder()
            ->where('project_id', $project->id)
            ->first();

        $boards = TaskboardColumn::all()->where('company_id', $companyId)->pluck('id')->toArray();

        $startDate = $faker->randomElement([$faker->dateTimeThisMonth($max = 'now'), $faker->dateTimeThisYear($max = 'now')]);

        $task = new \App\Models\Task();
        $task->company_id = $companyId;
        $task->heading = $faker->realText(20);
        $task->description = $faker->realText(200);
        $task->start_date = $startDate;
        $task->due_date = Carbon::parse($startDate)->addDays(rand(1, 10))->toDateString();
        $task->project_id = $project->id;
        $task->priority = $faker->randomElement(['high', 'medium', 'low']);
        $task->status = $faker->randomElement(['incomplete', 'completed']);
        $task->board_column_id = $faker->randomElement($boards);
        $task->save();

        $task->task_short_code = $project->project_short_code . '-' . $task->id;
        $task->saveQuietly();

        TaskUser::create(
            [
                'user_id' => $assignee->user_id,
                'task_id' => $task->id
            ]
        );

        $search = new \App\Models\UniversalSearch();
        $search->searchable_id = $task->id;
        $search->company_id = $companyId;
        $search->title = $task->heading;
        $search->route_name = 'tasks.show';
        $search->save();

        $activity = new \App\Models\ProjectActivity();
        $activity->project_id = $project->id;
        $activity->activity = 'New task added to the project.';
        $activity->save();
    }

    private function createInvoice($faker, $project, $companyId)
    {
        $items = [$faker->word, $faker->word];
        $cost_per_item = [$faker->numberBetween(1000, 2000), $faker->numberBetween(1000, 2000)];
        $quantity = [$faker->numberBetween(1, 20), $faker->numberBetween(1, 20)];
        $amount = [$cost_per_item[0] * $quantity[0], $cost_per_item[1] * $quantity[1]];
        $type = ['item', 'item'];

        $companyAddress = CompanyAddress::where('is_default', 1)->firstOrFail();
        $invoice = new \App\Models\Invoice();
        $invoice->project_id = $project->id;
        $invoice->company_id = $companyId;
        $invoice->company_address_id = $companyAddress->id;
        $invoice->client_id = $project->client_id;
        $invoice->invoice_number = \App\Models\Invoice::where('company_id', $companyId)->count() == 0 ? 1 : \App\Models\Invoice::where('company_id', $companyId)->count() + 1;
        $invoice->issue_date = Carbon::parse((date('m') - 1) . '/' . $faker->numberBetween(1, 30) . '/' . date('Y'))->format('Y-m-d');
        $invoice->due_date = Carbon::parse($invoice->issue_date)->addDays(10)->format('Y-m-d');
        $invoice->sub_total = array_sum($amount);
        $invoice->total = array_sum($amount);
        $invoice->currency_id = $this->getCurrencyId($companyId);
        $invoice->status = $faker->randomElement(['paid', 'unpaid']);
        $invoice->send_status = 1;
        $invoice->due_amount = array_sum($amount);
        $invoice->hash = md5(microtime());
        $invoice->default_currency_id = $this->getCurrencyId($companyId);
        $invoice->exchange_rate = 1;
        $invoice->save();

        $search = new \App\Models\UniversalSearch();
        $search->searchable_id = $invoice->id;
        $search->company_id = $companyId;
        $search->title = 'Invoice ' . $invoice->invoice_number;
        $search->route_name = 'invoices.show';
        $search->save();

        foreach ($items as $key => $item) :
            \App\Models\InvoiceItems::create(['invoice_id' => $invoice->id, 'item_name' => $item, 'type' => $type[$key], 'quantity' => $quantity[$key], 'unit_price' => $cost_per_item[$key], 'amount' => $amount[$key]]);
        endforeach;

        if ($invoice->status == 'paid') {
            $payment = new \App\Models\Payment();
            $payment->amount = $invoice->total;
            $payment->company_id = $companyId;
            $payment->invoice_id = $invoice->id;
            $payment->project_id = $project->id;

            $payment->gateway = 'Bank Transfer';
            $payment->transaction_id = md5($invoice->id);
            $payment->currency_id = $this->getCurrencyId($companyId);
            $payment->status = 'complete';
            $payment->paid_on = Carbon::parse(now()->month . '/' . $faker->numberBetween(1, now()->day) . '/' . now()->year . ' ' . $faker->numberBetween(1, 23) . ':' . $faker->numberBetween(1, 59) . ':' . $faker->numberBetween(1, 59))->format('Y-m-d H:i:s');
            $payment->default_currency_id = $this->getCurrencyId($companyId);
            $payment->exchange_rate = 1;
            $payment->save();
        }
    }

    private function createTimeLog($faker, $project, $companyId)
    {
        $projectMember = $project->members->first();
        // Create time logs
        $timeLog = new \App\Models\ProjectTimeLog();
        $timeLog->project_id = $project->id;
        $timeLog->company_id = $companyId;
        $timeLog->task_id = $project->tasks->first()->id;
        $timeLog->user_id = $projectMember->user_id;
        $timeLog->start_time = $faker->randomElement([date('Y-m-d', strtotime('+' . mt_rand(0, 7) . ' days')), $faker->dateTimeThisMonth($max = 'now'), $faker->dateTimeThisYear($max = 'now')]);
        $timeLog->end_time = Carbon::parse($timeLog->start_time)->addHours($faker->numberBetween(1, 5))->toDateTimeString();
        /** @phpstan-ignore-next-line */
        $timeLog->total_hours = $timeLog->end_time->diff($timeLog->start_time)->format('%d') * 24 + $timeLog->end_time->diff($timeLog->start_time)->format('%H');

        if ($timeLog->total_hours == 0) {
            /** @phpstan-ignore-next-line */
            $timeLog->total_hours = round(($timeLog->end_time->diff($timeLog->start_time)->format('%i') / 60), 2);

        }

        $timeLog->total_minutes = $timeLog->total_hours * 60;
        $timeLog->hourly_rate = (!is_null($projectMember->hourly_rate) ? $projectMember->hourly_rate : 0);

        $minuteRate = $projectMember->hourly_rate / 60;
        $earning = round($timeLog->total_minutes * $minuteRate);
        /* @phpstan-ignore-line */
        $timeLog->earnings = $earning;

        $timeLog->memo = 'working on' . $faker->word;
        $timeLog->save();
    }

}
