<?php

namespace App\Entity;

enum BookList: string
{
    case WISHLIST = 'whishlist';
    case LIBRARY = 'library';
}
