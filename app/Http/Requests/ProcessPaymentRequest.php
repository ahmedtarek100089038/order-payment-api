<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $paymentMethod = $this->input('payment_method');

        $rules = [
            'order_id' => ['required', 'exists:orders,id'],
            'payment_method' => ['required', Rule::in(['credit_card', 'paypal', 'stripe'])],
            'payment_details' => ['required', 'array'],
        ];

        if ($paymentMethod === 'credit_card') {
            $rules['payment_details.card_number'] = ['required', 'string', 'min:13', 'max:19'];
            $rules['payment_details.expiry_month'] = ['required', 'integer', 'between:1,12'];
            $rules['payment_details.expiry_year'] = ['required', 'integer', 'min:' . date('Y')];
            $rules['payment_details.cvv'] = ['required', 'string', 'size:3'];
        } elseif ($paymentMethod === 'paypal') {
            $rules['payment_details.paypal_email'] = ['required', 'email'];
        } elseif ($paymentMethod === 'stripe') {
            $rules['payment_details.stripe_token'] = ['required', 'string'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'Order ID is required',
            'order_id.exists' => 'Invalid order ID',
            'payment_method.required' => 'Payment method is required',
            'payment_method.in' => 'Invalid payment method. Supported methods: credit_card, paypal, stripe',
            'payment_details.required' => 'Payment details are required',
        ];
    }
}