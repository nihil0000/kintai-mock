<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'requested_clock_in' => 'required',
            'requested_clock_out' => 'required',
            'requested_breaks.start.*' => 'required',
            'requested_breaks.end.*' => 'required',
            'note' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'requested_clock_in.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'requested_clock_out.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'requested_breaks.start.*.required' =>  '休憩時間が勤務時間外です',
            'requested_breaks.end.*.required' =>  '休憩時間が勤務時間外です',
            'note.required' => '備考を記入してください'
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $in  = $this->input('requested_clock_in');
            $out = $this->input('requested_clock_out');
            $breakStarts = $this->input('requested_breaks.start', []);
            $breakEnds = $this->input('requested_breaks.end', []);

            if ($in > $out) {
                $validator->errors()->add('requested_clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // 休憩時間が勤務時間外かどうか
            foreach ($breakStarts as $i => $start) {
                $end = $breakEnds[$i] ?? null;

                if ($start < $in || $start > $out) {
                    $validator->errors()->add("requested_breaks.start.$i", '休憩時間が勤務時間外です');
                }
                if ($end < $in || $end > $out) {
                    $validator->errors()->add("requested_breaks.end.$i", '休憩時間が勤務時間外です');
                }
            }

            // Display a single error message when both or either one of the fields is empty
            if ((empty($in) && empty($out)) || (empty($in) || empty($out))) {
                $validator->errors()->add('requested_clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }
        });
    }
}
