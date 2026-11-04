<?php

namespace App\Http\Requests\Grades;

class GradeCorrectionRequest extends GradeRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        $this->errorBag = 'default';
        $this->merge(['reason' => is_string($this->input('reason')) ? trim($this->input('reason')) : $this->input('reason')]);
    }

    public function rules(): array
    {
        return [
            ...parent::rules(),
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }

    public function reason(): string
    {
        return $this->validated('reason');
    }
}
