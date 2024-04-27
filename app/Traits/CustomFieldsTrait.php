<?php

namespace App\Traits;

use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use App\Models\LeadCustomForm;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

trait CustomFieldsTrait
{

    public $model;
    private $extraData;
    public $custom_fields;
    public $custom_fields_data;

    /** Get company ID for current object
     * @return int Returns current object's company id
     */

    private function getModelName()
    {
        $model = new \ReflectionClass($this);
        $this->model = $model;

        return $this->model->getName();
    }

    public function updateCustomField($group)
    {

        // Add Custom Fields for this group
        foreach ($group['fields'] as $field) {
            $insertData = [
                'custom_field_group_id' => 1,
                'label' => $field['label'],
                'name' => $field['name'],
                'type' => $field['type']
            ];

            if (isset($field['required']) && (in_array(strtolower($field['required']), ['yes', 'on', 1]))) {
                $insertData['required'] = 'yes';
            }
            else {
                $insertData['required'] = 'no';
            }

            // Single value should be stored as text (multi value JSON encoded)
            if (isset($field['value'])) {
                if (is_array($field['value'])) {
                    $insertData['values'] = json_encode($field['value']);

                }
                else {
                    $insertData['values'] = $field['value'];
                }
            }

            DB::table('custom_fields')->insert($insertData);
        }
    }

    public function getCustomFieldGroups()
    {
        return CustomFieldGroup::where('model', $this->getModelName())->get();
    }

    public function getCustomFieldGroupsWithFields()
    {
        $fields = [];

        $groups = $this->getCustomFieldGroups();

        foreach ($groups as $group) {

            $customFields = CustomField::where('custom_field_group_id', $group->id)->get();
            $customFields = collect($customFields);

            // convert values to json array if type is select
            $customFields = $customFields->map(function ($item) {
                if ($item->type == 'select' || $item->type == 'radio' || $item->type == 'checkbox') {
                    $item->values = json_decode($item->values);

                    return $item;
                }

                return $item;
            });

            $group->fields = $customFields;
            $fields[] = $group;
        }

        if (!empty($fields)) {
            return $fields[0];
        }

        return $fields;
    }

    public function getCustomFieldsData()
    {

        $modelId = $this->id;

        // Get custom fields for this modal
        /** @var Collection $data */
        $data = DB::table('custom_fields_data')
            ->rightJoin('custom_fields', function ($query) use ($modelId) {
                $query->on('custom_fields_data.custom_field_id', '=', 'custom_fields.id');
                $query->on('model_id', '=', DB::raw($modelId));
            })
            ->rightJoin('custom_field_groups', 'custom_fields.custom_field_group_id', '=', 'custom_field_groups.id')
            ->select('custom_fields.id', DB::raw('CONCAT("field_", custom_fields.id) as field_id'), 'custom_fields.type', 'custom_fields_data.value')
            ->where('custom_field_groups.model', $this->getModelName())
            ->get();

        $data = collect($data);

        // Convert collection to an associative array
        // of format ['field_{id}' => $value]
        $result = $data->pluck('value', 'field_id');

        return $result;
    }

    public function updateCustomFieldData($fields)
    {
        foreach ($fields as $key => $value) {

            $idarray = explode('_', $key);
            $id = end($idarray);

            $fieldType = CustomField::findOrFail($id)->type;

            $value = ($fieldType == 'date') ? Carbon::createFromFormat(company()->date_format, $value)->format('Y-m-d') : $value;

            // Find is entry exists
            $entry = DB::table('custom_fields_data')
                ->where('model', $this->getModelName())
                ->where('model_id', $this->id)
                ->where('custom_field_id', $id)
                ->first();

            if ($entry) {
                DB::table('custom_fields_data')
                    ->where('model', $this->getModelName())
                    ->where('model_id', $this->id)
                    ->where('custom_field_id', $id)
                    ->update(['value' => $value]);


            }
            else {
                DB::table('custom_fields_data')
                    ->insert([
                        'model' => $this->getModelName(),
                        'model_id' => $this->id,
                        'custom_field_id' => $id,
                        'value' => (!is_null($value)) ? $value : ''
                    ]);
            }
        }
    }

    public function getExtrasAttribute()
    {
        if ($this->extraData == null) {
            $this->extraData = $this->getCustomFieldGroupsWithFields();
        }

        return $this->extraData;
    }

    public function withCustomFields()
    {
        $this->custom_fields = $this->getCustomFieldGroupsWithFields();
        $this->custom_fields_data = $this->getCustomFieldsData();

        return $this;
    }

}
