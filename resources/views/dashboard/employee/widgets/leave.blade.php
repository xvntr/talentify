@if (in_array('leave', $activeWidgets))
    <!-- EMP DASHBOARD BIRTHDAY START -->
    <div class="col-sm-12">
        <x-cards.data class="e-d-info mb-3" :title="__('modules.dashboard.leave')" padding="false" otherClasses="h-200">
            <x-table>
                @forelse ($leave as $totalLeave)
                    <tr>
                        <td class="pl-20">
                            <x-employee :user="$totalLeave->user"/>
                        </td>
                        <td class="pr-20">
                            @if ($totalLeave->duration == 'single' || $totalLeave->duration == 'multiple')
                                <span class="badge badge-secondary p-2">@lang('modules.dashboard.fullDay')</span>
                            @else
                                @if ($totalLeave->duration == 'half day' && $totalLeave->half_day_type == 'first_half')
                                    <span class="badge badge-secondary p-2">@lang('modules.leaves.firstHalf')</span>
                                @else
                                    <span class="badge badge-secondary p-2">@lang('modules.leaves.secondHalf')</span>
                                @endif
                            @endif
                        </td>
                        <td class="pr-20" align="right">
                            <span class="badge badge-success p-2"
                                  style="background-color:{{$totalLeave->type->color}}">{{ucfirst($totalLeave->type->type_name)}}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="shadow-none">
                            <x-cards.no-record icon="plane-departure" :message="__('messages.noRecordFound')"/>
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </x-cards.data>
    </div>
    <!-- EMP DASHBOARD BIRTHDAY END -->
@endif
