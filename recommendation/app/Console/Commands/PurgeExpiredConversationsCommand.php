<?php

namespace App\Console\Commands;

use App\Services\ConversationService;
use Illuminate\Console\Command;

class PurgeExpiredConversationsCommand extends Command
{
    protected $signature = 'recommendations:purge-conversations';

    protected $description = 'Delete conversation contexts that exceeded max lifetime or idle timeout';

    public function handle(ConversationService $conversations): int
    {
        $removed = $conversations->purgeExpired();
        $this->info('Removed '.$removed.' conversation(s).');

        return self::SUCCESS;
    }
}
