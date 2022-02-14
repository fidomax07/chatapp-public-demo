<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ContactResource;
use App\Http\Resources\ContactCollection;
use Illuminate\Validation\ValidationException;
use App\Notifications\ContactAddedDbNotification;
use App\Notifications\ContactAddedPushNotification;

class ContactController extends ApiController
{
	/**
	 * ContactController constructor.
	 */
	public function __construct()
	{
		$this->sortConfig();
	}



	/**
	 * @return ContactCollection
	 */
	public function index(): ContactCollection
	{
		return new ContactCollection(
			auth()->user()->contactsWithMedia()->paginate($this->getLimitPerPage())
		);
	}

	/**
	 * @param Request $req
	 * @return ContactResource
	 * @throws ValidationException
	 */
	public function store(Request $req): ContactResource
	{
		/** @var User $user */
		$user = $req->user();

		$contact = $this->validateContactStore($req);
		$user->contacts()->attach($contact->id);

		$notificationData = $user->only($this->notificationAttributes());
		$contact->pushNotify(new ContactAddedPushNotification($notificationData));
		$contact->notify(new ContactAddedDbNotification($notificationData));

		return new ContactResource($contact);
	}

	/**
	 * @param User $contact
	 * @param Request $req
	 * @return ContactResource
	 * @throws ValidationException
	 */
	public function show(User $contact, Request $req): ContactResource
	{
		$this->validateUserHasContact($req->user(), $contact);

		return new ContactResource($contact);
	}

	/**
	 * @param User $contact
	 * @param Request $req
	 * @return JsonResponse
	 * @throws ValidationException
	 */
	public function destroy(User $contact, Request $req): JsonResponse
	{
		$this->validateUserHasContact($req->user(), $contact);
		$req->user()->contacts()->detach($contact->id);

		return response()->json(['deleted' => true]);
	}


	/**
	 * @param Request $req
	 * @return User|null
	 * @throws ValidationException
	 */
	private function validateContactStore(Request $req): ?User
	{
		$req->validate([
			'phone' => 'required_without:username|max:20',
			'username' => 'required_without:phone|alpha_num|min:6'
		]);

		$contact = $req->has('phone')
			? User::findByPhone($req->get('phone'))
			: User::findByUsername($req->get('username'));
		if (!$contact) {
			throw ValidationException::withMessages([
				'contact' => ["No contact was found with the given {$req->keys()[0]}."],
			]);
		}
		if ($req->user()->hasContact($contact)) {
			throw ValidationException::withMessages([
				'contact' => ['The contact is already in your contact list.'],
			]);
		}

		return $contact;
	}

	/**
	 * @param User $user
	 * @param User $contact
	 * @return void
	 * @throws ValidationException
	 */
	private function validateUserHasContact(User $user, User $contact): void
	{
		if (!$user->hasContact($contact)) {
			throw ValidationException::withMessages([
				'contact' => ['The contact is not in your contact list.'],
			]);
		}
	}

	/**
	 * @return string[]
	 */
	private function notificationAttributes(): array
	{
		return ['id', 'phone', 'username', 'full_name'];
	}
}
