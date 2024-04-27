<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\HasCompany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\LeaveType
 *
 * @property int $id
 * @property string $type_name
 * @property string $color
 * @property int $no_of_leaves
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $paid
 * @property-read mixed $icon
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Leave[] $leaves
 * @property-read int|null $leaves_count
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereNoOfLeaves($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType wherePaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereTypeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $company_id
 * @property int $monthly_limit
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Leave[] $leavesCount
 * @property-read int|null $leaves_count_count
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereMonthlyLimit($value)
 */
class LeaveType extends BaseModel
{

    use HasCompany;

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class, 'leave_type_id');
    }

    public function leavesCount(): HasMany
    {
        return $this->leaves()
            ->selectRaw('leave_type_id, count(*) as count, SUM(if(duration="half day", 1, 0)) AS halfday')
            ->groupBy('leave_type_id');
    }

    public static function byUser($user, $leaveTypeId = null, $status = array('approved'), $leaveDate = null)
    {
        if (!is_null($leaveDate)) {
            $leaveDate = Carbon::createFromFormat(company()->date_format, $leaveDate);

        }
        else {
            $leaveDate = now(company()->timezone);
        }

        if (!$user instanceof User) {
            $user = User::withoutGlobalScope(ActiveScope::class)->withOut('clientDetails', 'role')->findOrFail($user);
        }

        $setting = company();

        if (isset($user->employee[0])) {
            if ($setting->leaves_start_from == 'joining_date') {
                $currentYearJoiningDate = Carbon::parse($user->employee[0]->joining_date->format((now(company()->timezone)->year) . '-m-d'));

                if ($currentYearJoiningDate->isFuture()) {
                    $currentYearJoiningDate->subYear();
                }

                $leaveTypes = LeaveType::with(['leavesCount' => function ($q) use ($user, $currentYearJoiningDate, $status) {
                    $q->where('leaves.user_id', $user->id);
                    $q->whereBetween('leaves.leave_date', [$currentYearJoiningDate->copy()->toDateString(), $currentYearJoiningDate->copy()->addYear()->toDateString()]);
                    $q->whereIn('leaves.status', $status);
                }]);

                if (!is_null($leaveTypeId)) {
                    $leaveTypes = $leaveTypes->where('id', $leaveTypeId);
                }

                return $leaveTypes = $leaveTypes->get();

            }
            else {
                $leaveTypes = LeaveType::with(['leavesCount' => function ($q) use ($user, $status, $leaveDate) {
                    $q->where('leaves.user_id', $user->id);
                    $q->whereBetween('leaves.leave_date', [$leaveDate->startOfYear()->toDateString(), $leaveDate->endOfYear()->toDateString()]);
                    $q->whereIn('leaves.status', $status);
                }]);
            }

            if (!is_null($leaveTypeId)) {
                $leaveTypes = $leaveTypes->where('id', $leaveTypeId);
            }

            return $leaveTypes->get();
        }

        return [];

    }

}
