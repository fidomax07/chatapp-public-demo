<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class MessageStatusController extends Controller
{
	/**
	 * @param Request $req
	 * @return JsonResponse
	 * @throws ValidationException
	 */
	public function delivered(Request $req): JsonResponse
	{
		$this->validateMessageIds($req);
		$ids = $this->filterOwnMessageIds($req, $req->get('ids') ?? array($req->get('id')));

        return response()->json(['delivered_count' => Message::markDelivered($ids)]);
	}

	/**
	 * @param Request $req
	 * @return JsonResponse
	 * @throws ValidationException
	 */
	public function seen(Request $req): JsonResponse
	{
		$this->validateMessageIds($req);
		$ids = $this->filterOwnMessageIds($req, $req->get('ids') ?? array($req->get('id')));

		return response()->json(['seen_count' => Message::markSeen($ids)]);
	}



	/**
	 * @param Request $req
	 * @return void
	 * @throws ValidationException
	 */
	private function validateMessageIds(Request $req)
	{
		$req->validate([
			'id' => 'required_without:ids|string',
			'ids' => 'required_without:id|array',
			'ids.*' => 'string'
		]);
	}

	/**
	 * @param Request $req
	 * @param array $ids
	 * @return array
	 */
	private function filterOwnMessageIds(Request $req, array $ids): array
	{
		$filteredIds = $ids;
		$ownIds = $req->user()->messages()->whereIn('id', $ids)->pluck('id');
		if ($ownIds->isNotEmpty()) {
			$filteredIds = collect($ids)
				->filter(fn($id) => !$ownIds->contains($id))
				->values()
				->toArray();
		}
		return $filteredIds;
	}
}
