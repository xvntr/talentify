<?php

namespace App\Http\Requests\Tickets;

use App\Http\Requests\CoreRequest;
use App\Models\CustomField;

class StoreCustomTicket extends CoreRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $setting = \global_setting();
        $rules = array();
        $rules['name'] = 'required';
        $rules['email'] = 'required|email:rfc';
        $rules['ticket_subject'] = 'required';
        $rules['ticket_description'] = 'required';

        $checkCustomField = request()->custom_fields_data;

        if ($checkCustomField)
        {
            foreach ($checkCustomField as $key => $customFieldsData) {
                $fieldName = explode ('_', $key);
                $name = $fieldName[0];
                $id = $fieldName[1];

                $customField = CustomField::findOrFail($id);

                if ($customField->required == 'yes' && $customFieldsData == null)
                {
                    $rules[$name] = 'required';
                }
            }
        }

        if($setting->google_recaptcha_status == 'active' && $setting->ticket_form_google_captcha == 1 && ($setting->google_recaptcha_v2_status == 'active')){
            $rules['g-recaptcha-response'] = 'required';
        }

        return $rules;
    }

}
