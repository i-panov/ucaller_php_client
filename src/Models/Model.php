<?php

namespace IPanov\UcallerClient\Models;

abstract class Model
{
    public function load(array $data): self {
        $defaults = $this->getDefaultValues();
        $reflection = new \ReflectionObject($this);

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $name = $property->getName();
            $setValue = fn($value) => $property->setValue($this, $value);

            if (isset($data[$name])) {
                $setValue($data[$name]);
            } elseif (array_key_exists($name, $defaults)) {
                $setValue($defaults[$name]);
            } else {
                throw new \InvalidArgumentException("Required key $name missed");
            }
        }

        return $this;
    }

    protected function getDefaultValues(): array {
        return [];
    }
}
