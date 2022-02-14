<?php

namespace App\Jobs;

use App\Models\Chat;
use App\Models\User;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PruneChatMessages implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	private Chat $chat;
	private User $user;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct(Chat $chat, User $user)
	{
		$this->chat = $chat->withoutRelations();
		$this->user = $user->withoutRelations();
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		$interlocutor = $this->chat->interlocutor($this->user);
		if ($this->chat->userMessagesCount($interlocutor) > 0) {
			$this->chat->deleteUserMessages($this->user);
			return;
		}

		$this->chat->pictureMessages()->each(function (Message $m) {
			$m->pictures->each(fn($p) => $p->delete());
		});
		$this->chat->messages()->delete();
	}
}
