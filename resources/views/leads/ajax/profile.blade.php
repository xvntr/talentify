<!-- ROW START -->
<div class="row">
    <!--  USER CARDS START -->
    <div class="col-xl-12 col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4 mb-md-0">

        <x-cards.data :title="__('modules.client.profileInfo')">

            <x-slot name="action">
                <div class="dropdown">
                    <button class="btn f-14 px-0 py-0 text-dark-grey dropdown-toggle" type="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-ellipsis-h"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                        aria-labelledby="dropdownMenuLink" tabindex="0">
                        <a class="dropdown-item openRightModal"
                            href="{{ route('leads.edit', $lead->id) }}">@lang('app.edit')</a>
                        @if (
                            $deleteLeadPermission == 'all'
                            || ($deleteLeadPermission == 'added' && user()->id == $lead->added_by)
                            || ($deleteLeadPermission == 'owned' && !is_null($lead->agent_id) && user()->id == $lead->leadAgent->user->id)
                            || ($deleteLeadPermission == 'both' && ((!is_null($lead->agent_id) && user()->id == $lead->leadAgent->user->id)
                                    || user()->id == $lead->added_by))
                        )
                            <a class="dropdown-item delete-table-row" href="javascript:;" data-id="{{ $lead->id }}">
                                    @lang('app.delete')
                                </a>
                        @endif
                        @if ($lead->client_id == null || $lead->client_id == '')
                            <a class="dropdown-item" href="{{route('clients.create') . '?lead=' . $lead->id }}">
                                @lang('modules.lead.changeToClient')
                            </a>
                        @endif
                    </div>
                </div>
            </x-slot>

            <x-cards.data-row :label="__('modules.lead.clientName')" :value="$lead->client_name ?? '--'" />

            <x-cards.data-row :label="__('modules.lead.clientEmail')" :value="$lead->client_email ?? '--'" />

            <x-cards.data-row :label="__('modules.lead.companyName')" :value="!empty($lead->company_name) ? mb_ucwords($lead->company_name) : '--'" />

            <x-cards.data-row :label="__('modules.lead.website')" :value="$lead->website ?? '--'" />

            <x-cards.data-row :label="__('modules.lead.mobile')" :value="$lead->mobile ?? '--'" />

            <x-cards.data-row :label="__('modules.client.officePhoneNumber')" :value="$lead->office ?? '--'" />
            <x-cards.data-row :label="__('app.country')" :value="$lead->country ?? '--'" />

            <x-cards.data-row :label="__('modules.stripeCustomerAddress.state')" :value="$lead->state ?? '--'" />

            <x-cards.data-row :label="__('modules.stripeCustomerAddress.city')" :value="$lead->city ?? '--'" />

            <x-cards.data-row :label="__('modules.stripeCustomerAddress.postalCode')" :value="$lead->postal_code ?? '--'" />

            <x-cards.data-row :label="__('modules.lead.address')" :value="$lead->address ?? '--'" />

            <div class="col-12 px-0 pb-3 d-flex">
                <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                    @lang('modules.lead.leadAgent')</p>
                <p class="mb-0 text-dark-grey f-14">
                    @if (!is_null($lead->leadAgent))
                        <x-employee :user="$lead->leadAgent->user" />
                    @else
                        --
                    @endif
                </p>
            </div>

            <x-cards.data-row :label="__('modules.lead.source')" :value="$lead->leadSource ? mb_ucwords($lead->leadSource->type) : '--'" />

            @if ($lead->leadStatus)
                <div class="col-12 px-0 pb-3 d-flex">
                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">@lang('app.status')</p>
                    <p class="mb-0 text-dark-grey f-14">
                        <x-status :value="ucfirst($lead->leadStatus->type)"
                            :style="'color:'.$lead->leadStatus->label_color" />
                    </p>

                </div>
            @endif

            <x-cards.data-row :label="__('modules.lead.leadCategory')" :value="$lead->category->category_name ?? '--'" />

            <x-cards.data-row :label="__('app.lead') . ' ' .__('app.value')" :value="$lead->value ?? '--'" />

            {{-- <x-cards.data-row :label="__('app.note')" :value="!empty($lead->note) ? $lead->note : '--'" html="true" /> --}}

            {{-- Custom fields data --}}
            <x-forms.custom-field-show :fields="$fields" :model="$lead"></x-forms.custom-field-show>

        </x-cards.data>
    </div>
    <!--  USER CARDS END -->
</div>
<!-- ROW END -->
