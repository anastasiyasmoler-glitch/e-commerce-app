<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('recommendations:purge-conversations')->everyTenMinutes();
