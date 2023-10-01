<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Imahmood\FileStorage\Controllers\DownloadController;

Route::get('/media/public/{mediaId}/{fileName}', DownloadController::class)
    ->name('file-storage:public');

Route::get('/media/{mediaId}/{fileName}', DownloadController::class)
    ->middleware('signed')
    ->name('file-storage:private');
