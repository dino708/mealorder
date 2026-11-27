<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class CheckoutRequest extends FormRequest {
    public function authorize(): bool {return $this->user()?->role==='customer';}
    public function rules(): array {
        return ['schedule_id'=>'required|integer|exists:preorder_schedules,id','notes'=>'nullable|string|max:1000',
            'fulfillment'=>'sometimes|in:pickup,delivery',
            'delivery_address'=>'required_if:fulfillment,delivery|nullable|string|max:500',
            'contact_phone'=>'required_if:fulfillment,delivery|nullable|string|max:20|regex:/^[0-9+() -]{7,20}$/',
            'payment_method'=>'sometimes|in:manual,cash,transfer',
            'items'=>'required|array|min:1|max:50','items.*.product_id'=>['required','integer','distinct',\Illuminate\Validation\Rule::exists('products','id')->whereNull('deleted_at')],
            'items.*.quantity'=>'required|integer|min:1|max:100'];
    }
}
