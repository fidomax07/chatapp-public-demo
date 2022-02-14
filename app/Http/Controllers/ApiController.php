<?php

namespace App\Http\Controllers;


use App\Models\Chat;
use Illuminate\Http\Response;

class ApiController extends Controller
{
	/**
	 * Default limit of the items per page.
	 */
	public const LIMIT_PER_PAGE = 15;

	/**
	 * @var int
	 */
	protected int $limitPerPage = self::LIMIT_PER_PAGE;

	/**
	 * @var string
	 */
	protected string $sort = 'id';

	/**
	 * @var string|null
	 */
	protected ?string $withParameter = null;

	/**
	 * @var string
	 */
	protected string $sortDirection = 'asc';

	public function sortConfig()
	{
		$this->middleware(function ($request, $next) {

			if ($request->has('sort')) {
				$this->setSortCriteria($request->sort);

				// It has a sort criteria, but is it a valid one?
				if (empty($this->getSortCriteria())) {
					abort(400);
				}
			}

			return $next($request);
		});
	}

	/**
	 * @return string
	 */
	public function getWithParameter(): ?string
	{
		return $this->withParameter;
	}

	/**
	 * @param string $with
	 * @return ApiController
	 */
	public function setWithParameter(string $with): ApiController
	{
		$this->withParameter = $with;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getLimitPerPage(): int
	{
		return $this->limitPerPage;
	}

	/**
	 * Get the sort direction parameter.
	 * @return string
	 */
	public function getSortDirection(): string
	{
		return $this->sortDirection;
	}

	/**
	 * @return string
	 */
	public function getSortCriteria(): string
	{
		return $this->sort;
	}

	/**
	 * @param string $criteria
	 * @return self
	 */
	public function setSortCriteria(string $criteria): ApiController
	{
		$acceptedCriteria = [
			'id',
			'-id',
			'created_at',
			'-created_at',
			'updated_at',
			'-updated_at'
		];

		if (in_array($criteria, $acceptedCriteria)) {
			$this->setSQLOrderByQuery($criteria);

			return $this;
		}

		$this->sort = '';

		return $this;
	}

	/**
	 * Set both the column and order necessary to perform an orderBy.
	 * @param $criteria
	 */
	public function setSQLOrderByQuery($criteria)
	{
		$this->sort = $criteria;
		$this->sortDirection = 'asc';

		$firstCharacter = $this->getSortCriteria()[0];

		if ($firstCharacter == '-') {
			$this->sort = substr($this->getSortCriteria(), 1);
			$this->sortDirection = 'desc';
		}
	}

	/**
	 * @return void
	 */
	protected function validateUserChat(Chat $chat)
	{
		if (!auth()->user()->hasChat($chat)) {
			abort(Response::HTTP_FORBIDDEN, 'User does not own this chat!');
		}
	}
}
