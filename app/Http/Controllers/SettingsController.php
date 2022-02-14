<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\PinMatches;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserResource;

class SettingsController extends ApiController
{
	/**
	 * @param Request $req
	 * @return UserResource
	 */
	public function avatar(Request $req): UserResource
	{
		[$avatar] = array_values($req->validate([
			'avatar' => 'image|mimes:jpg,bmp,png|max:1024'
		]));
		$req->user()->saveAvatar($avatar);

		return new UserResource($req->user()->loadMissing('media'));
	}

	/**
	 * @param Request $req
	 * @return UserResource
	 */
	public function username(Request $req): UserResource
	{
		/** @var User $user */
		$user = $req->user();
		$req->validate([
			'username' => 'required|alpha_num|min:6|' . Rule::unique('users')->ignore($user)
		]);
		$user->update(['username' => $req->get('username')]);

		return new UserResource($user->load('fcmTokens'));
	}

	public function name(Request $req): UserResource
	{
		/** @var User $user */
		$user = $req->user();
		$validated = $req->validate([
			'first_name' => 'required_without:last_name|min:3',
			'last_name' => 'required_without:first_name|min:3'
		]);
		$user->updateName($validated);

		return new UserResource($user->load('fcmTokens'));
	}

	/**
	 * @param Request $req
	 * @return UserResource
	 */
	public function pin(Request $req): UserResource
	{
		/** @var User $user */
		$user = $req->user();
		$req->validate([
			'pin' => ['required', new PinMatches($user)],
			'pin_new' => 'required|numeric|digits_between:4,6|confirmed',
		]);
		$user->updatePin($req->get('pin_new'));

		return new UserResource($user->load('fcmTokens'));
	}

	/**
	 * @param Request $req
	 * @return UserResource
	 */
	public function notifications(Request $req): UserResource
	{
		/** @var User $user */
		$user = $req->user();
		$req->validate([
			'enabled' => 'required|boolean'
		]);
		$user->update(['ntf_enabled' => $req->boolean('enabled')]);

		return new UserResource($user->load('fcmTokens'));
	}

	/**
	 * @param Request $req
	 * @return JsonResponse
	 */
	public function destroy(Request $req): JsonResponse
	{
		$req->user()->tokens()->delete();
		$req->user()->delete();

		return response()->json(null, Response::HTTP_NO_CONTENT);
	}
}
