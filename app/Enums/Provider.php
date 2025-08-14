<?php

namespace App\Enums;


enum Provider: string
{
  case GITHUB = 'github';
  case X = 'x'; // Twitter
  case GOOGLE = 'google';
}
