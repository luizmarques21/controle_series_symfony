<?php

namespace App\Message;

use App\Entity\Series;

class SeriesWasCreated
{
    public function __construct(
        public readonly Series $series
    )
    {
    }

}