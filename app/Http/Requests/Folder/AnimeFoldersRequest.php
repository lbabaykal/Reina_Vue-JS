<?php /** @noinspection PhpInconsistentReturnPointsInspection */

namespace App\Http\Requests\Folder;

use Illuminate\Foundation\Http\FormRequest;

class AnimeFoldersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->isMethod('POST')) {  //TODO включить правила валидации для создание папок аниме
            return [
                'title' => ['required', 'string', 'min:2', 'max:32'],
//                'is_private' => ['required', 'boolean'],
//                'number' => ['required', 'integer', 'min:6'],

//                'title' => ['required', 'string', 'min:2', 'max:255',
//                    Rule::unique('folder_animes')
//                        ->where(function ($query) {
//                            return $query->where('user_id', auth()->id());
//                        })
//                ],
            ];
        } elseif ($this->isMethod('PATCH')) {
            return [
                'title' => ['required', 'string', 'min:2', 'max:32'],
//                'is_private' => ['required', 'boolean'],
//                'number' => ['required', 'integer', 'min:6'],

//                'title' => ['required', 'string', 'min:2', 'max:255',
//                    Rule::unique('folder_animes')
//                        ->where(function ($query) {
//                            return $query->where('user_id', auth()->id());
//                        })
//                        ->ignore($this->folder->id)],
            ];
        }
    }

}
