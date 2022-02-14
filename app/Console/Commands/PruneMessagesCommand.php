<?php

namespace App\Console\Commands;

use App\Models\Message;
use App\Models\MessagePicture;
use Illuminate\Console\Command;

class PruneMessagesCommand extends Command
{
	/**
	 * @const int
	 */
	protected const MESSAGE_TTL = 60 * 60;

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'messages:prune';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Prune older messages.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle(): void
	{
		Message::seen()->lazy()->each(function (Message $message) {
			if (now()->diffInSeconds($message->created_at) < self::MESSAGE_TTL) {
				return;
			}

			if ($message->isPicture()) {
				$message->pictures->each(fn(MessagePicture $mp) => $mp->delete());
			}
			$message->delete();
		});
	}
}
