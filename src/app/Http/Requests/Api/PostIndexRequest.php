<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostIndexRequest extends FormRequest
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
	 */
	public function rules(): array
	{
		return [
			'status' => [
				'sometimes',
				'string',
				Rule::in(['draft', 'published', 'archived']),
			],
			'category' => [
				'sometimes',
				'string',
				'max:255',
			],
			'tag' => [
				'sometimes',
				'string',
				'max:255',
			],
			'search' => [
				'sometimes',
				'string',
				'max:255',
			],
			'per_page' => [
				'sometimes',
				'integer',
				'min:1',
				'max:100',
			],
			'page' => [
				'sometimes',
				'integer',
				'min:1',
			],
		];
	}
}
