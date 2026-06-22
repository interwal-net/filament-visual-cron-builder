<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Resources\ScheduleResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Workbench\App\Filament\Resources\ScheduleResource;

class CreateSchedule extends CreateRecord
{
    protected static string $resource = ScheduleResource::class;
}
