<?php

namespace IPanov\UcallerClient\Models;

class Call extends Model
{
    public bool $status;
    public string $ucaller_id;
    public string $phone;
    public string $code;
    public string $client;
    public ?string $unique_request_id;
    public bool $exists;
    public ?bool $free_repeated;

    protected function getDefaultValues(): array {
        return [
            'status' => false,
            'unique_request_id' => 'null',
            'exists' => false,
            'free_repeated' => null,
        ];
    }
}
