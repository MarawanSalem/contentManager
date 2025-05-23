<?php

namespace App\Console\Commands;

use App\Services\PostService;
use Illuminate\Console\Command;

class ProcessScheduledPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process all scheduled posts that are due for publication';

    protected $postService;

    public function __construct(PostService $postService)
    {
        parent::__construct();
        $this->postService = $postService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to process scheduled posts...');

        try {
            $this->postService->processScheduledPosts();
            $this->info('Successfully processed scheduled posts.');
        } catch (\Exception $e) {
            $this->error('Error processing scheduled posts: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
