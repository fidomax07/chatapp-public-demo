<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Admin;
use App\Jobs\SendSmsVC;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Validation\ValidationException;

class AuthController extends ApiController
{
	/**
	 * @param Request $req
	 * @return JsonResponse
	 * @throws ValidationException
	 */
	public function pinReset(Request $req): JsonResponse
	{
		$req->validate(array_merge($this->adminCredentialsRules(), [
			'phone' => 'required|max:20',
			'pin' => 'required|numeric|digits_between:4,6|confirmed'
		]));

		$admin = Admin::identify([
			'email' => $req->get('admin_email'), 'password' => $req->get('admin_password')
		]);
		$this->verifyAdminCredentials($admin);

		$user = User::findByPhone($req->get('phone'));
		$this->verifyUserFound($user);

		$user->updatePin($req->get('pin'));
		$user->tokens()->delete();

		return response()->json(['message' => 'Success']);
	}


	/**
	 * @param Request $req
	 * @return UserResource
	 * @throws \Throwable
	 */
	public function register(Request $req): UserResource
	{
		$validated = $req->validate(array_merge(
			$this->credentialsRules(true), [
			'first_name' => 'nullable|string|min:3',
			'last_name' => 'nullable|string|min:3'
		]));

		$user = User::store($validated);
		SendSmsVC::dispatch($user->phone);

		return new UserResource($user);
	}

	/**
	 * @param Request $req
	 * @return JsonResponse
	 * @throws ValidationException
	 */
	public function registerVerify(Request $req): JsonResponse
	{
		$req->validate(array_merge($this->credentialsRules(), [
			'sms_vc' => 'required|numeric|digits:5'
		]));

		$user = User::identify($req->only(['phone', 'pin']));
		$this->verifyPreconditions($user);
		if (!$user->verifySmsVc($req->get('sms_vc'))) {
			throw ValidationException::withMessages([
				'sms_vc' => ['The provided SMS-VC is incorrect.'],
			]);
		}
		$user->verify();

		return $this->successLoginResponse($user);
	}

	/**
	 * @param Request $req
	 * @return JsonResponse
	 * @throws ValidationException
	 */
	public function smsVc(Request $req): JsonResponse
	{
		$req->validate($this->credentialsRules());

		$user = User::identify($req->only(['phone', 'pin']));
		$this->verifyCredentials($user)
			->verifyUserNotVerified($user);
		if ($user->maxSmsVcSentsReached()) {
			throw ValidationException::withMessages([
				'sms_vc' => ['Maximum number of SMS-VC sents reached.'],
			]);
		}

		$user->regenerateSmsVc();
		SendSmsVC::dispatch($user->phone);

		return response()->json(null, Response::HTTP_NO_CONTENT);
	}

	/**
	 * @param Request $req
	 * @return JsonResponse
	 * @throws ValidationException
	 */
	public function smsVcReset(Request $req): JsonResponse
	{
		$req->validate($this->credentialsRules());

		$user = User::identify($req->only(['phone', 'pin']));
		$this->verifyCredentials($user)
			->verifyUserNotVerified($user);

		$user->resetSmsVc();

		return response()->json(null, Response::HTTP_NO_CONTENT);
	}

	/**
	 * @param Request $req
	 * @return JsonResponse
	 * @throws ValidationException
	 */
	public function login(Request $req): JsonResponse
	{
		$req->validate($this->credentialsRules());

		$user = User::identify($req->only(['phone', 'pin']));
		$this->verifyCredentials($user)
			->verifyUserVerified($user);

		return $this->successLoginResponse($user->load('fcmTokens'));
	}

	/**
	 * @param Request $req
	 * @return UserResource
	 */
	public function me(Request $req): UserResource
	{
		return new UserResource($req->user()->load('fcmTokens'));
	}

	/**
	 * @param Request $req
	 * @return JsonResponse
	 */
	public function logout(Request $req): JsonResponse
	{
		$req->user()->tokens()->delete();

		return response()->json(null, Response::HTTP_NO_CONTENT);
	}

	/**
	 * @param Request $req
	 * @return mixed
	 */
	public function broadcastingAuth(Request $req)
	{
		$req->validate([
			'channel_name' => 'required',
			'socket_id' => 'required'
		]);

		return Broadcast::auth($req);
	}

	/**
	 * @param Request $req
	 * @return UserResource
	 */
	public function fcmToken(Request $req): UserResource
	{
		$req->validate([
			'value' => 'required',
			'device_name' => 'nullable|string|max:45'
		]);
		$req->user()->fcmTokens()->updateOrCreate(
			$req->only('value'), $req->only('device_name')
		);

		return new UserResource($req->user()->load('fcmTokens'));
	}

	/**
	 * @param Request $req
	 * @param int $id
	 * @return UserResource|JsonResponse
	 */
	public function fcmTokenDestroy(Request $req, int $id)
	{
		$deletedCount = $req->user()->fcmTokens()->where('id', $id)->delete();
		if ($deletedCount == 0) {
			return response()->json(null, Response::HTTP_NOT_MODIFIED);
		}

		return new UserResource($req->user()->load('fcmTokens'));
	}



	/**
	 * @param User $user
	 * @return JsonResponse
	 */
	protected function successLoginResponse(User $user): JsonResponse
	{
		return response()->json([
			'data' => [
				'token_type' => 'Bearer',
				'token' => $user->createToken(Str::random(20))->plainTextToken,
				'user' => new UserResource($user)
			]
		]);
	}

	/**
	 * @return string[]
	 */
	protected function credentialsRules(bool $register = false): array
	{
		return [
			'phone' => 'required|max:20' . ($register ? '|unique:users' : ''),
			'pin' => 'required|numeric|digits_between:4,6' . ($register ? '|confirmed' : '')
		];
	}

	/**
	 * @return string[]
	 */
	protected function adminCredentialsRules(bool $register = false): array
	{
		return [
			'admin_email' => 'required|email' . ($register ? '|unique:admins' : ''),
			'admin_password' => 'required|min:6' . ($register ? '|confirmed' : '')
		];
	}

	/**
	 * @param User|null $user
	 * @return AuthController
	 * @throws ValidationException
	 */
	protected function verifyCredentials(?User $user): AuthController
	{
		if (!$user) {
			throw ValidationException::withMessages([
				'phone' => ['The provided credentials are incorrect.'],
			]);
		}

		return $this;
	}

	/**
	 * @param Admin|null $admin
	 * @return AuthController
	 * @throws ValidationException
	 */
	protected function verifyAdminCredentials(?Admin $admin): AuthController
	{
		if (!$admin) {
			throw ValidationException::withMessages([
				'email' => ['The provided credentials are incorrect.'],
			]);
		}

		return $this;
	}



	/**
	 * @param User|null $user
	 * @return AuthController
	 * @throws ValidationException
	 */
	protected function verifyUserFound(?User $user): AuthController
	{
		if (!$user) {
			throw ValidationException::withMessages([
				'phone' => ['No user found with the the provided phone.'],
			]);
		}

		return $this;
	}

	/**
	 * @param User|null $user
	 * @return AuthController
	 * @throws ValidationException
	 */
	protected function verifyPreconditions(?User $user): AuthController
	{
		$this->verifyCredentials($user)
			->verifyUserNotVerified($user);
		if ($user->smsVcExpired()) {
			throw ValidationException::withMessages([
				'sms_vc' => ['This SMS-VC has expired. Generate a new one.'],
			]);
		}
		if ($user->maxSmsVcAttemptsReached()) {
			throw ValidationException::withMessages([
				'sms_vc' => ['Maximum number of SMS-VC attempts reached.'],
			]);
		}

		return $this;
	}

	/**
	 * @param User $user
	 * @return AuthController
	 * @throws ValidationException
	 */
	protected function verifyUserVerified(User $user): AuthController
	{
		if (!$user->verified()) {
			throw ValidationException::withMessages([
				'sms_vc' => ['This user is not verified yet.'],
			]);
		}

		return $this;
	}

	/**
	 * @param User $user
	 * @return AuthController
	 * @throws ValidationException
	 */
	protected function verifyUserNotVerified(User $user): AuthController
	{
		if ($user->verified()) {
			throw ValidationException::withMessages([
				'sms_vc' => ['This user is already verified'],
			]);
		}

		return $this;
	}
}
