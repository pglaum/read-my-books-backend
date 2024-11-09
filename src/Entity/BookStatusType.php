<?php

namespace App\Entity;

enum BookStatusType: string
{
    case WISHLIST = 'wishlist';
    case OWNED = 'owned';
    case READING = 'reading';
    case READ = 'read';
}
