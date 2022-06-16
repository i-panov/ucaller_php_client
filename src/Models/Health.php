<?php

namespace IPanov\UcallerClient\Models;

class Health extends Model
{
    public bool $status;
    public bool $database;
    public bool $providers;
}
