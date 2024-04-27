<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@lang('app.proposal')</title>
    <style>
        body {
            position: relative;
            width: 100%;
            height: auto;
            margin: 0 auto;
            color: #555555;
            background: #FFFFFF;
            font-size: 13px;
            /* font-family: Verdana, Arial, Helvetica, sans-serif; */
        }
        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: normal;
            src: url("{{ storage_path('fonts/THSarabunNew.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: bold;
            src: url("{{ storage_path('fonts/THSarabunNew_Bold.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'THSarabunNew';
            font-style: italic;
            font-weight: bold;
            src: url("{{ storage_path('fonts/THSarabunNew_Bold_Italic.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'THSarabunNew';
            font-style: italic;
            font-weight: bold;
            src: url("{{ storage_path('fonts/THSarabunNew_Italic.ttf') }}") format('truetype');
        }

        @if($invoiceSetting->is_chinese_lang)
        @font-face {
            font-family: SimHei;
            /*font-style: normal;*/
            font-weight: bold;
            src: url('{{ asset('fonts/simhei.ttf') }}') format('truetype');
        }
        @endif

        @php
            $font = '';
            if($invoiceSetting->locale == 'ja') {
                $font = 'ipag';
            } else if($invoiceSetting->locale == 'hi') {
                $font = 'hindi';
            } else if($invoiceSetting->locale == 'th') {
                $font = 'THSarabunNew';
            } else if($invoiceSetting->is_chinese_lang) {
                $font = 'SimHei';
            }else {
                $font = 'Verdana';
            }
        @endphp

        @if($invoiceSetting->is_chinese_lang)
            body
        {
            font-weight: normal !important;
        }
        @endif
        * {
            font-family: {{$font}}, DejaVu Sans , sans-serif;
        }

        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }

        a {
            text-decoration: none;
        }



        h2 {
            font-weight: normal;
        }

        header {
            padding: 10px 0;
        }

        #logo img {
            height: 50px;
            margin-bottom: 15px;
        }

        #details {
            margin-bottom: 25px;
        }

        #client {
            padding-left: 6px;
            float: left;
        }

        #client .to {
            color: #777777;
        }

        h2.name {
            font-size: 1.2em;
            font-weight: normal;
            margin: 0;
        }

        #invoice h1 {
            color: #0087C3;
            line-height: 2em;
            font-weight: normal;
            margin: 0 0 10px 0;
            font-size: 20px;
        }

        #invoice .date {
            font-size: 1.1em;
            color: #777777;
        }

        table {
            width: 100%;
            border-spacing: 0;
            /* margin-bottom: 20px; */
        }

        table th,
        table td {
            padding: 5px 8px;
            text-align: center;
        }

        table th {
            background: #EEEEEE;
        }

        table th {
            white-space: nowrap;
            font-weight: normal;
        }

        table td {
            text-align: right;
        }

        table td.desc h3,
        table td.qty h3 {
            font-size: 0.9em;
            font-weight: normal;
            margin: 0 0 0 0;
        }

        table .no {
            font-size: 0.9em;
            width: 10%;
            text-align: center;
            border-left: 1px solid #e7e9eb;
        }

        table .desc, table .item-summary  {
            text-align: left;
        }

        table .unit {
            border: 1px solid #e7e9eb;
        }


        table .total {
            background: #57B223;
            color: #FFFFFF;
        }

        table td.unit,
        table td.qty,
        table td.total {
            font-size: 1.2em;
            text-align: center;
        }

        table td.unit {
            width: 35%;
        }

        table td.desc {
            width: 45%;
        }

        table td.qty {
            width: 5%;
        }

        .status {
            margin-top: 15px;
            padding: 1px 8px 5px;
            font-size: 1.3em;
            width: 80px;
            float: right;
            text-align: center;
            display: inline-block;
        }

        .status.unpaid {
            background-color: #E7505A;
        }

        .status.paid {
            background-color: #26C281;
        }

        .status.cancelled {
            background-color: #95A5A6;
        }

        .status.error {
            background-color: #F4D03F;
        }

        table tr.tax .desc {
            text-align: right;
        }

        table tr.discount .desc {
            text-align: right;
            color: #E43A45;
        }

        table tr.subtotal .desc {
            text-align: right;
        }

        table tbody tr:last-child td {
            border: none;
        }

        table tfoot td {
            padding: 10px;
            font-size: 1.2em;
            white-space: nowrap;
            border-bottom: 1px solid #e7e9eb;
            font-weight: 700;
        }

        table tfoot tr:first-child td {
            border-top: none;
        }

        table tfoot tr td:first-child {
            /* border: none; */
        }

        #notices {
            padding-left: 6px;
            border-left: 6px solid #0087C3;
        }

        #notices .notice {
            font-size: 1.2em;
        }

        footer {
            color: #777777;
            width: 100%;
            height: 30px;
            position: absolute;
            bottom: 0;
            border-top: 1px solid #e7e9eb;
            padding: 8px 0;
            text-align: center;
        }

        table.billing td {
            background-color: #fff;
        }

        table td#invoiced_to {
            text-align: left;
            padding-left: 0;
        }

        #notes {
            color: #767676;
            font-size: 11px;
        }

        .item-summary {
            font-size: 11px;
            padding-left: 0;
        }

        .page_break {
            page-break-before: always;
        }


        table td.text-center {
            text-align: center;
        }

        .word-break {
            word-wrap:break-word;
        }

        #invoice-table td {
            border: 1px solid #e7e9eb;
        }

        .border-left-0 {
            border-left: 0 !important;
        }

        .border-right-0 {
            border-right: 0 !important;
        }

        .border-top-0 {
            border-top: 0 !important;
        }

        .border-bottom-0 {
            border-bottom: 0 !important;
        }
        @if($invoiceSetting->locale == 'th')

            table td {
            font-weight: bold !important;
            font-size: 20px !important;
            }

            .description
            {
                font-weight: bold !important;
                font-size: 18px !important;
            }
        @endif
    </style>
</head>

<body>
    <header class="clearfix"  class="description">

        <table cellpadding="0" cellspacing="0" class="billing">
            <tr>
                <td colspan="2"><h1>@lang('app.proposal')</h1></td>
            </tr>
            <tr>
                <td id="invoiced_to">
                    @if ($proposal->lead && ($proposal->lead->client_name || $proposal->lead->client_email || $proposal->lead->mobile || $proposal->lead->company_name || $proposal->lead->address) && ($invoiceSetting->show_client_name == 'yes' || $invoiceSetting->show_client_email == 'yes' || $invoiceSetting->show_client_phone == 'yes' || $invoiceSetting->show_client_company_name == 'yes' || $invoiceSetting->show_client_company_address == 'yes'))
                    <div>
                        <small>@lang("modules.invoices.billedTo"):</small>
                        <div class="mb-3 description">
                            @if ($proposal->lead && $proposal->lead->client_name && $invoiceSetting->show_client_name == 'yes')
                                <b>{{ mb_ucwords($proposal->lead->client_name) }}</b>
                            @endif
                            @if ($proposal->lead && $proposal->lead->client_email && $invoiceSetting->show_client_email == 'yes')
                                <div>{{ mb_ucwords($proposal->lead->client_email) }}</div>
                            @endif
                            @if ($proposal->lead && $proposal->lead->mobile && $invoiceSetting->show_client_phone == 'yes')
                                <div>{{ $proposal->lead->mobile }}</div>
                            @endif
                            @if ($proposal->lead && $proposal->lead->company_name && $invoiceSetting->show_client_company_name == 'yes')
                                <div>{{ mb_ucwords($proposal->lead->company_name) }}</div>
                            @endif
                            @if ($proposal->lead && $proposal->lead->address && $invoiceSetting->show_client_company_address == 'yes')
                                <div>{!! nl2br($proposal->lead->address) !!}</div>
                            @endif
                        </div>
                    </div>
                    @endif
                </td>
                <td>
                    <div id="company"  class="description">
                        <div id="logo" >
                            <img src="{{ $invoiceSetting->logo_url }}" alt="home" class="dark-logo" />
                        </div>
                        <small>@lang("modules.invoices.generatedBy"):</small>
                        <div id="logo" description">
                        <h3 class="name >{{ mb_ucwords($company->company_name) }}</h3>
                        @if (!is_null($company))
                            <div>{!! nl2br(default_address()->address) !!}</div>
                            <div>{{ $company->company_phone }}</div>
                        @endif
                        @if ($invoiceSetting->show_gst == 'yes' && !is_null($invoiceSetting->gst_number))
                            <div  class="description">@lang('app.gstIn'): {{ $invoiceSetting->gst_number }}</div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </header>
    <main>
        <div id="details">

            <div id="invoice"  class="description">
                <h1 class="description">@lang('modules.lead.proposal')#{{ $proposal->id }}</h1>
                <div  class="description">@lang('app.status'): {{ mb_ucwords($proposal->status) }}</div>
                <div  class="description">@lang('modules.estimates.validTill'):
                    {{ $proposal->valid_till->format($company->date_format) }}</div>
            </div>

        </div>
        @if ($proposal->description)
            <div  class="description">
                {!! strip_tags($proposal->description, ['p', 'b', 'strong', 'a', 'ul', 'li', 'ol', 'i', 'u', 'em', 'blockquote', 'img']) !!}
            </div>
        @endif
        <table cellspacing="0" cellpadding="0" id="invoice-table">
            <thead>
                <tr>
                    <th class="no">#</th>
                    <th class="desc description">@lang("modules.invoices.item")</th>
                    @if ($invoiceSetting->hsn_sac_code_show)
                        <th class="qty description">@lang("app.hsnSac")</th>
                    @endif
                    <th class="qty description">@lang("modules.invoices.qty")</th>
                    <th class="qty description">@lang("modules.invoices.unitPrice")</th>
                    <th class="qty description">@lang("modules.invoices.tax")</th>
                    <th class="unit description">@lang("modules.invoices.price") ({!! htmlentities($proposal->currency->currency_code) !!})</th>
                </tr>
            </thead>
            <tbody>
                <?php $count = 0; ?>
                @foreach ($proposal->items as $item)
                    @if ($item->type == 'item')
                        <tr style="page-break-inside: avoid;">
                            <td class="no">{{ ++$count }}</td>
                            <td class="desc">
                                <h3 class="description">{{ ucfirst($item->item_name) }}</h3>
                                @if (!is_null($item->item_summary))
                                <table>
                                    <tr>
                                        <td class="item-summary word-break border-top-0 border-right-0 border-left-0 border-bottom-0 description">{!! nl2br(strip_tags($item->item_summary, ['p', 'b', 'strong', 'a'])) !!}</td>
                                    </tr>
                                </table>
                                @endif
                                @if ($item->proposalItemImage)
                                    <p class="mt-2">
                                        <img src="{{ $item->proposalItemImage->file_url }}" width="60" height="60" class="img-thumbnail">
                                    </p>
                                @endif
                            </td>
                            @if ($invoiceSetting->hsn_sac_code_show)
                                <td class="qty">
                                    <h3>{{ $item->hsn_sac_code ? $item->hsn_sac_code : '--' }}</h3>
                                </td>
                            @endif
                            <td class="qty">
                                <h3>{{ $item->quantity }}</h3>
                            </td>
                            <td class="qty">
                                <h3>{{ currency_format($item->unit_price, $proposal->currency_id, false) }}</h3>
                            </td>
                            <td>
                                {{ $item->tax_list }}
                            </td>
                            <td class="unit">{{ currency_format($item->amount, $proposal->currency_id, false) }}</td>
                        </tr>
                    @endif
                @endforeach
                <tr style="page-break-inside: avoid;" class="subtotal">
                    <td class="no">&nbsp;</td>
                    <td class="qty">&nbsp;</td>
                    <td class="qty">&nbsp;</td>
                    @if ($invoiceSetting->hsn_sac_code_show)
                        <td class="qty">&nbsp;</td>
                    @endif
                    <td class="qty">&nbsp;</td>
                    <td class="desc">@lang("modules.invoices.subTotal")</td>
                    <td class="unit">{{ currency_format($proposal->sub_total, $proposal->currency_id, false) }}</td>
                </tr>

                @if ($discount != 0 && $discount != '')
                    <tr style="page-break-inside: avoid;" class="discount">
                        <td class="no">&nbsp;</td>
                        <td class="qty">&nbsp;</td>
                        <td class="qty">&nbsp;</td>
                        @if ($invoiceSetting->hsn_sac_code_show)
                            <td class="qty">&nbsp;</td>
                        @endif
                        <td class="qty">&nbsp;</td>
                        <td class="desc">@lang("modules.invoices.discount")</td>
                        <td class="unit">{{ currency_format($discount, $proposal->currency_id, false) }}</td>
                    </tr>
                @endif
                @foreach ($taxes as $key => $tax)
                    <tr style="page-break-inside: avoid;" class="tax">
                        <td class="no">&nbsp;</td>
                        <td class="qty">&nbsp;</td>
                        <td class="qty">&nbsp;</td>
                        @if ($invoiceSetting->hsn_sac_code_show)
                            <td class="qty">&nbsp;</td>
                        @endif
                        <td class="qty">&nbsp;</td>
                        <td class="desc">{{ mb_strtoupper($key) }}</td>
                        <td class="unit">{{ currency_format($tax, $proposal->currency_id, false) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr dontbreak="true">
                    <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '6' : '5' }}">
                        @lang("modules.invoices.subTotal")</td>
                    <td style="text-align: center">{{ currency_format($proposal->sub_total, $proposal->currency_id, false) }}</td>
                </tr>
                <tr dontbreak="true">
                    <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '6' : '5' }}">
                        @lang("modules.invoices.discount")</td>
                    <td style="text-align: center">{{ currency_format($discount, $proposal->currency_id, false) }}</td>
                </tr>
                <tr dontbreak="true">
                    <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '6' : '5' }}">
                        @lang("modules.invoices.total")</td>
                    <td style="text-align: center">{{ currency_format($proposal->total, $proposal->currency_id, false) }} {!! htmlentities($proposal->currency->currency_code) !!}</td>
                </tr>
            </tfoot>
        </table>


        <p id="notes" class="word-break description">
            @if (!is_null($proposal->note))
                @lang('app.note') <br>{!! nl2br($proposal->note) !!}<br>
            @endif
            @lang('modules.invoiceSettings.invoiceTerms') <br>{!! nl2br($invoiceSetting->invoice_terms) !!}
        </p>

        @if (isset($taxes) && $invoiceSetting->tax_calculation_msg == 1)
            <p class="text-dark-grey description">
                @if ($proposal->calculate_tax == 'after_discount')
                    @lang('messages.calculateTaxAfterDiscount')
                @else
                    @lang('messages.calculateTaxBeforeDiscount')
                @endif
            </p>
        @endif
        <p>
            @if ($proposal->signature)
                @if (!is_null($proposal->signature->signature))
                    <img src="{{ $proposal->signature->signature }}" style="width: 200px;">
                    <h6 class="description">@lang('modules.estimates.signature')</h6>
                @else
                    <h6>@lang('modules.estimates.signedBy')</h6>
                @endif
                <p class="description">({{ $proposal->signature->full_name }})</p>
            @endif
        </p>
    </main>
</body>

</html>
