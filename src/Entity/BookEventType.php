<?php

namespace App\Entity;

enum BookEventType: string
{
    case BOUGHT = 'bought';
    case READ = 'read';
}
