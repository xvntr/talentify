@if (in_array('appreciation', $activeWidgets))
    <!-- EMP DASHBOARD APPRECIATION START -->
    <div class="col-sm-12">
        <x-cards.data class="e-d-info mb-3" :title="__('app.employee').' '.__('modules.dashboard.appreciation')" padding="false" otherClasses="h-200">
            <x-table class="appreciation-table">
                @forelse ($appreciations as $appreciation)
                    <tr>
                        <td>
                            <x-employee :user="$appreciation->awardTo" />
                        </td>
                        <td class="text-right pr-20">
                            <div class="position-relative d-flex justify-content-between" data-toggle="tooltip" data-original-title="">
                                @if(isset($appreciation->award->awardIcon->icon))
                                    <i class="bi bi-{{ $appreciation->award->awardIcon->icon }} f-15 text-white position-absolute appreciation-icon"></i>
                                    <i class="bi bi-hexagon-fill fs-40" style="color: {{ $appreciation->award->color_code }}"></i>
                                @endif
                                @if(isset($appreciation->award))
                                    <div class="ml-1 f-12">
                                        <span class="font-weight-bold">{{ mb_ucwords($appreciation->award->title) }}</span><br>
                                        {{ $appreciation->award_date->format($company->date_format) }}
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="shadow-none">
                            <x-cards.no-record icon="award" :message="__('messages.noRecordFound')" />
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </x-cards.data>
    </div>
    <!-- EMP DASHBOARD APPRECIATION END -->
@endif
