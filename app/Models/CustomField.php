<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\CustomField
 *
 * @property int $id
 * @property int|null $custom_field_group_id
 * @property string $label
 * @property string $name
 * @property bool $export
 * @property string $type
 * @property string $required
 * @property string|null $values
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField query()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereCustomFieldGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereValues($value)
 * @mixin \Eloquent
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\LeadCustomForm|null $leadCustomForm
 * @property-read \App\Models\TicketCustomForm|null $ticketCustomForm
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereExport($value)
 * @property-read \App\Models\CustomFieldGroup|null $customFieldGroup
 */
class CustomField extends BaseModel
{

    use HasCompany;

    public $timestamps = false;

    protected $guarded = ['id'];

    public function leadCustomForm(): HasOne
    {
        return $this->hasOne(LeadCustomForm::class, 'custom_fields_id');
    }

    public function ticketCustomForm(): HasOne
    {
        return $this->hasOne(TicketCustomForm::class, 'custom_fields_id');
    }

    public function customFieldGroup(): HasOne
    {
        return $this->hasOne(CustomFieldGroup::class, 'custom_fields_group_id');
    }

    public static function exportCustomFields($model)
    {
        $customFieldsGroupsId = CustomFieldGroup::where('model', $model::CUSTOM_FIELD_MODEL)->select('id')->first();
        $customFields = collect();

        if ($customFieldsGroupsId) {
            $customFields = CustomField::where('custom_field_group_id', $customFieldsGroupsId->id)->where('export', 1)->get();
        }

        return $customFields;
    }

    public static function customFieldData($datatables, $model)
    {
        $customFields = CustomField::exportCustomFields($model);

        $customFieldsId = $customFields->pluck('id');
        $fieldData = DB::table('custom_fields_data')->where('model', $model)->whereIn('custom_field_id', $customFieldsId)->select('id', 'custom_field_id', 'model_id', 'value')->get();

        foreach ($customFields as $customField) {
            $datatables->addColumn($customField->name, function ($row) use ($fieldData, $customField) {

                $finalData = $fieldData->filter(function ($value) use ($customField, $row) {
                    return ($value->custom_field_id == $customField->id) && ($value->model_id == $row->id);
                })->first();

                if ($customField->type == 'select') {
                    $data = $customField->values;
                    $data = json_decode($data); // string to array

                    return $finalData ? (($finalData->value >= 0 && $finalData->value != null) ? $data[$finalData->value] : '--') : '--';
                }

                return $finalData ? $finalData->value : '--';
            });
        }
    }

}
