<?php

namespace App\Http\Controllers;

use App\Models\SmtpSetting;
use App\Scopes\ActiveScope;
use Carbon\Carbon;
use App\Helper\Files;
use App\Helper\Reply;
use App\Models\Invoice;
use App\Models\Contract;
use App\Models\Estimate;
use App\Models\ContractSign;
use App\Models\EstimateItem;
use App\Models\InvoiceItems;
use Illuminate\Http\Request;
use App\Models\AcceptEstimate;
use Illuminate\Support\Facades\DB;
use App\Events\ContractSignedEvent;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use App\Traits\UniversalSearchTrait;
use App\Http\Requests\EstimateAcceptRequest;
use App\Http\Requests\Admin\Contract\SignRequest;
use Nwidart\Modules\Facades\Module;

class PublicUrlController extends Controller
{

    use UniversalSearchTrait;

    /* Contract */
    public function contractView(Request $request, $hash)
    {
        $pageTitle = 'app.menu.contracts';
        $pageIcon = 'fa fa-file';
        $contract = Contract::where('hash', $hash)
            ->with('client', 'contractType', 'signature', 'discussion', 'discussion.user')
            ->withoutGlobalScope(ActiveScope::class)
            ->firstOrFail();
        $company = $contract->company;
        $invoiceSetting = $contract->company->invoiceSetting;

        return view('contract', ['contract' => $contract, 'company' => $company, 'pageTitle' => $pageTitle, 'pageIcon' => $pageIcon, 'invoiceSetting' => $invoiceSetting]);
    }

    public function contractSign(SignRequest $request, $id)
    {
        $this->contract = Contract::with('signature')->findOrFail($id);
        $this->company = $this->contract->company;
        $this->invoiceSetting = $this->contract->company->invoiceSetting;

        if ($this->contract && $this->contract->signature) {
            return Reply::error(__('messages.alreadySigned'));
        }

        $sign = new ContractSign();
        $sign->company_id = $this->company->id;
        $sign->full_name = $request->first_name . ' ' . $request->last_name;
        $sign->contract_id = $this->contract->id;
        $sign->email = $request->email;
        $imageName = null;

        if ($request->signature_type == 'signature') {
            $image = $request->signature;  // your base64 encoded
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = str_random(32) . '.' . 'jpg';

            Files::createDirectoryIfNotExist('contract/sign');

            File::put(public_path() . '/' . Files::UPLOAD_FOLDER . '/contract/sign/' . $imageName, base64_decode($image));
        }
        else {
            if ($request->hasFile('image')) {
                $imageName = Files::upload($request->image, 'contract/sign', 300);
            }
        }

        $sign->signature = $imageName;
        $sign->save();

        event(new ContractSignedEvent($this->contract, $sign));

        return Reply::redirect(route('contracts.show', $this->contract->id));
    }

    public function contractDownload($id)
    {
        $contract = Contract::findOrFail($id);
        $company = $contract->company;

        $this->invoiceSetting = $contract->company->invoiceSetting;
        $pdf = app('dompdf.wrapper');
        $pdf->getDomPDF()->set_option('enable_php', true);
        App::setLocale($this->invoiceSetting->locale);
        Carbon::setLocale($this->invoiceSetting->locale);
        $pdf->loadView('contracts.contract-pdf', ['contract' => $contract, 'company' => $company]);

        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->getCanvas();
        $canvas->page_text(530, 820, 'Page {PAGE_NUM} of {PAGE_COUNT}', null, 10);

        $filename = 'contract-' . $contract->id;

        return $pdf->download($filename . '.pdf');
    }

    public function estimateView($hash)
    {
        $estimate = Estimate::with('client', 'clientdetails')->where('hash', $hash)->firstOrFail();
        $company = $estimate->company;
        $defaultAddress = $company->defaultAddress;
        $pageTitle = $estimate->estimate_number;
        $pageIcon = 'icon-people';
        $this->discount = 0;

        if ($estimate->discount > 0) {
            if ($estimate->discount_type == 'percent') {
                $this->discount = (($estimate->discount / 100) * $estimate->sub_total);
            }
            else {
                $this->discount = $estimate->discount;
            }
        }


        $taxList = array();

        $items = EstimateItem::whereNotNull('taxes')
            ->where('estimate_id', $estimate->id)
            ->get();

        foreach ($items as $item) {

            foreach (json_decode($item->taxes) as $tax) {
                $this->tax = EstimateItem::taxbyid($tax)->first();

                if ($this->tax) {
                    if (!isset($taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'])) {

                        if ($estimate->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = ($item->amount - ($item->amount / $estimate->sub_total) * $this->discount) * ($this->tax->rate_percent / 100);

                        }
                        else {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $item->amount * ($this->tax->rate_percent / 100);
                        }

                    }
                    else {
                        if ($estimate->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + (($item->amount - ($item->amount / $estimate->sub_total) * $this->discount) * ($this->tax->rate_percent / 100));

                        }
                        else {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + ($item->amount * ($this->tax->rate_percent / 100));
                        }
                    }
                }
            }
        }

        $taxes = $taxList;

        $this->invoiceSetting = $company->invoiceSetting;

        return view('estimate', [
            'estimate' => $estimate,
            'taxes' => $taxes,
            'company' => $company,
            'discount' => $this->discount,
            'pageTitle' => $pageTitle,
            'pageIcon' => $pageIcon,
            'invoiceSetting' => $this->invoiceSetting,
            'defaultAddress' => $defaultAddress
        ]);

    }

    public function estimateAccept(EstimateAcceptRequest $request, $id)
    {
        DB::beginTransaction();

        $estimate = Estimate::with('sign')->findOrFail($id);
        $company  = $estimate->company;

        /** @phpstan-ignore-next-line */
        if ($estimate && $estimate->sign) {
            return Reply::error(__('messages.alreadySigned'));
        }

        $accept = new AcceptEstimate();
        $accept->company_id = $estimate->company->id;
        $accept->full_name = $request->first_name . ' ' . $request->last_name;
        $accept->estimate_id = $estimate->id;
        $accept->email = $request->email;
        $imageName = null;

        if ($request->signature_type == 'signature') {
            $image = $request->signature;  // your base64 encoded
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = str_random(32) . '.' . 'jpg';

            Files::createDirectoryIfNotExist('estimate/accept');

            File::put(public_path() . '/' . Files::UPLOAD_FOLDER . '/estimate/accept/' . $imageName, base64_decode($image));
        }
        else {
            if ($request->hasFile('image')) {
                $imageName = Files::upload($request->image, 'estimate/accept/', 300);
            }
        }

        $accept->signature = $imageName;
        $accept->save();

        $estimate->status = 'accepted';
        $estimate->saveQuietly();

        $invoice = new Invoice();

        $invoice->company_id = $company->id;
        $invoice->client_id = $estimate->client_id;
        $invoice->issue_date = Carbon::now($company->timezone)->format('Y-m-d');
        $invoice->due_date = Carbon::now($company->timezone)->addDays($company->invoiceSetting->due_after)->format('Y-m-d');
        $invoice->sub_total = round($estimate->sub_total, 2);
        $invoice->discount = round($estimate->discount, 2);
        $invoice->discount_type = $estimate->discount_type;
        $invoice->total = round($estimate->total, 2);
        $invoice->currency_id = $estimate->currency_id;
        $invoice->note = trim_editor($estimate->note);
        $invoice->status = 'unpaid';
        $invoice->estimate_id = $estimate->id;
        $invoice->invoice_number = Invoice::lastInvoiceNumber() + 1;
        $invoice->save();

        /** @phpstan-ignore-next-line */
        foreach ($estimate->items as $item) :

            if (!is_null($item)) {
                InvoiceItems::create(
                    [
                        'invoice_id' => $invoice->id,
                        'item_name' => $item->item_name,
                        'item_summary' => $item->item_summary ?: '',
                        'type' => 'item',
                        'quantity' => $item->quantity,
                        'unit_price' => round($item->unit_price, 2),
                        'amount' => round($item->amount, 2),
                        'taxes' => $item->taxes
                    ]
                );
            }

        endforeach;

        // Log search
        $this->logSearchEntry($invoice->id, $invoice->invoice_number, 'invoices.show', 'invoice');

        DB::commit();

        return Reply::success(__('messages.estimateSigned'));
    }

    public function estimateDecline(Request $request, $id)
    {
        $estimate = Estimate::findOrFail($id);
        $estimate->status = 'declined';
        $estimate->saveQuietly();

        return Reply::dataOnly(['status' => 'success']);
    }

    public function estimateDownload($id)
    {
        $this->estimate = Estimate::with('client', 'clientdetails')->findOrFail($id);
        $this->invoiceSetting = $this->estimate->company->invoiceSetting;
        App::setLocale($this->invoiceSetting->locale);
        Carbon::setLocale($this->invoiceSetting->locale);

        $pdfOption = $this->domPdfObjectForDownload($id);
        $pdf = $pdfOption['pdf'];
        $filename = $pdfOption['fileName'];

        return $pdf->download($filename . '.pdf');

    }

    public function domPdfObjectForDownload($id)
    {
        $this->estimate = Estimate::findOrFail($id);
        $this->company = $this->estimate->company;
        $this->invoiceSetting = $this->company->invoiceSetting;
        App::setLocale($this->invoiceSetting->locale);
        Carbon::setLocale($this->invoiceSetting->locale);

        $this->discount = 0;

        if ($this->estimate->discount > 0) {

            if ($this->estimate->discount_type == 'percent') {
                $this->discount = (($this->estimate->discount / 100) * $this->estimate->sub_total);
            }
            else {
                $this->discount = $this->estimate->discount;
            }
        }


        $taxList = array();

        $items = EstimateItem::whereNotNull('taxes')
            ->where('estimate_id', $this->estimate->id)
            ->get();

        foreach ($items as $item) {

            foreach (json_decode($item->taxes) as $tax) {
                $this->tax = EstimateItem::taxbyid($tax)->first();

                if ($this->tax) {
                    if (!isset($taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'])) {

                        if ($this->estimate->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = ($item->amount - ($item->amount / $this->estimate->sub_total) * $this->discount) * ($this->tax->rate_percent / 100);

                        }
                        else {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $item->amount * ($this->tax->rate_percent / 100);
                        }

                    }
                    else {
                        if ($this->estimate->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + (($item->amount - ($item->amount / $this->estimate->sub_total) * $this->discount) * ($this->tax->rate_percent / 100));

                        }
                        else {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + ($item->amount * ($this->tax->rate_percent / 100));
                        }
                    }
                }
            }
        }

        $this->taxes = $taxList;

        $pdf = app('dompdf.wrapper');

        $pdf->getDomPDF()->set_option('enable_php', true);
        $pdf->loadView('estimates.pdf.' . $this->invoiceSetting->template, $this->data);

        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->getCanvas();
        $canvas->page_text(530, 820, 'Page {PAGE_NUM} of {PAGE_COUNT}', null, 10);
        $filename = $this->estimate->estimate_number;

        return [
            'pdf' => $pdf,
            'fileName' => $filename
        ];
    }

    public function checkEnv()
    {

        $plugins = Module::all();
        $updateArray = [];
        $updateArrayEnabled = [];

        foreach ($plugins as $key => $plugin) {
            $module = Module::find($key);

            if ($module->isEnabled()) {
                $updateArrayEnabled[$key] = trim(File::get($module->getPath() . '/version.txt'));
            }

            $updateArray[$key] = trim(File::get($module->getPath() . '/version.txt'));
        }


        return [
            'app' => config('froiden_envato.envato_product_name'),
            'version' => trim(File::get('version.txt')),
            'debug' => config('app.debug'),
            'queue' => config('queue.default'),
            'php' => phpversion(),
            'environment' => \app()->environment(),
            'smtp_verified' => SmtpSetting::select('verified')->first()->verified,
            'all_modules' => $updateArray,
            'modules_enabled' => $updateArrayEnabled,
        ];

    }

}
