<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Imahmood\FileStorage\Controllers\DownloadController;

Route::get('/assets/{disk}/{path}', DownloadController::class)
    ->middleware('signed')
    ->where('path', '.*')
    ->name('file-storage:private');
