<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GetFonnteGroups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fonnte:get-groups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get list of WhatsApp groups joined by Fonnte bot';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $token = env('FONNTE_TOKEN');

        if (empty($token)) {
            $this->error('FONNTE_TOKEN is not set in .env file.');
            return 1;
        }

        $this->info('Fetching groups using token: ' . substr($token, 0, 5) . '...');

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/fetch-group');

            $result = $response->json();

            if (!$result['status']) {
                $this->error('Failed to fetch groups: ' . ($result['reason'] ?? 'Unknown error'));
                return 1;
            }

            $this->info('Groups found:');

            if (empty($result['data'])) {
                $this->warn('No groups found. Make sure the bot has joined at least one group.');
                $this->info('To add the bot to a group:');
                $this->info('1. Ensure the device linked to Fonnte is part of the group you want to notify.');

                $inviteLink = env('FONNTE_GROUP_LINK');
                if ($inviteLink) {
                    $this->info("2. Join using this link (on the bot device): $inviteLink");
                } else {
                    $this->info('2. If you have an invite link, open it on the device running the Fonnte bot.');
                }

                $this->info('3. Once joined, run this command again to get the Group ID.');
            } else {
                $headers = ['Group ID', 'Group Name'];
                $data = [];
                foreach ($result['data'] as $group) {
                    $data[] = [
                        $group['id'],
                        $group['name'] ?? 'Unknown Name'
                    ];
                }
                $this->table($headers, $data);

                $this->info('Copy the Group ID of your target group and set it as FONNTE_TARGET in your .env file.');
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
