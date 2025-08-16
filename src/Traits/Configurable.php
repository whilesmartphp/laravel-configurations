<?php

namespace Whilesmart\ModelConfiguration\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Whilesmart\ModelConfiguration\Enums\ConfigValueType;
use Whilesmart\ModelConfiguration\Models\Configuration;

trait Configurable
{
    public function getConfig(string $key): ?Configuration
    {
        return $this->configurations()->where('key', $key)
            ->first();
    }

    public function configurations(): MorphMany
    {
        return $this->morphMany(Configuration::class, 'configurable');
    }

    public function getConfigValue(string $key)
    {
        $config = $this->configurations()->where('key', $key)
            ->first();
        if (! is_null($config)) {
            $type = ConfigValueType::tryFrom($config->type);
            if (! is_null($type)) {
                return $type->getValue($config->value);
            }
        }

        return null;
    }

    public function getConfigType(string $key): ?ConfigValueType
    {
        $config = $this->configurations()->where('key', $key)
            ->first();
        if (! is_null($config)) {
            $type = ConfigValueType::tryFrom($config->type);
            if (! is_null($type)) {
                return $type;
            }
        }

        return null;
    }

    public function setConfigValue($key, $value, ConfigValueType $type): Configuration
    {
        if ($type === ConfigValueType::Date && $value instanceof Carbon) {
            $value = $value->toDateTimeString(); // Or any other suitable string format
        }

        $configuration = $this->configurations()->where('key', $key)->first();
        if ($configuration) {
            $configuration->update([
                'value' => $value,
                'type' => $type->value,
            ]);
        } else {
            $configuration = $this->configurations()->create([
                'key' => $key,
                'value' => $value,
                'type' => $type->value,
            ]);
        }

        return $configuration;
    }

    public function getConfigurationsAttribute()
    {
        return $this->configurations()->get();
    }
}
