<?php

$config = require base_path('vendor/livewire/livewire/config/livewire.php');

$config['temporary_file_upload']['disk'] = 'local';
$config['temporary_file_upload']['directory'] = 'livewire-tmp';
$config['temporary_file_upload']['rules'] = ['required', 'file', 'max:32768'];
$config['temporary_file_upload']['max_upload_time'] = 15;

return $config;
