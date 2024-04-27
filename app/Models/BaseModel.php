<?php

namespace App\Models;

use Froiden\RestAPI\ApiModel;

/**
 * App\Models\BaseModel
 *
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel query()
 * @mixin \Eloquent
 */
class BaseModel extends ApiModel
{

    public static function options($items, $group = null, $columnName = null): string
    {
        $options = '<option value="">--</option>';


        foreach ($items as $item) {

            $name = is_null($columnName) ? $item->name : $item->{$columnName};

            $selected = (!is_null($group) && ($item->id == $group->id)) ? 'selected' : '';

            $options .= '<option ' . $selected . ' value="' . $item->id . '"> ' . ($name) . ' </option>';
        }

        return $options;
    }

    public static function clickAbleLink($route, $title, $other = null)
    {
        return '<div class="media align-items-center">
                        <div class="media-body">
                    <h5 class="mb-0 f-13 text-darkest-grey"><a href="' . $route . '" class="openRightModal">' . ucfirst($title) . '</a></h5>
                    <p class="mb-0">' . $other . '</p>
                    </div>
                  </div>';
    }

}
