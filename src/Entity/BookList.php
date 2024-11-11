<?php

namespace App\Entity;

enum BookList: string
{
    case WISHLIST = 'wishlist';
    case LIBRARY = 'library';
}
