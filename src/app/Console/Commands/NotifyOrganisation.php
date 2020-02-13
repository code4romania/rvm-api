<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Organisation;
use App\Mail\NotifyTheOrganisation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Console\Command;

class NotifyOrganisation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:sendmailtoorganisation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email to update the organisation profile.';

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
     * @return mixed
     */
    public function handle()
    {
        $organisations = Organisation::all();
        $organisations->map(function($organisation) {
            if(Carbon::parse($organisation->updated_at)->addDays(env('MAIL_CRON_TIME'))->isPast()) {
                $data = ['url' => env('FRONT_END_URL').'/organisations/id/'.$organisation->_id.'/validate'];
                Mail::to($organisation['contact_person']['email'])->send(new NotifyTheOrganisation($data));
            } 
        });
    }
}
