<?php

namespace App\Traits;

trait BaseModelTrait
{
    public function getCreatedAtTextAttribute(): ?string
    {
        return $this->created_at?->translatedFormat('Y/m/d h:i:s A');
    }

    public function getUpdatedAtTextAttribute(): ?string
    {
        return $this->updated_at?->translatedFormat('Y/m/d h:i:s A');
    }

    public function getPaidAtTextAttribute(): ?string
    {
        return $this->paid_at?->translatedFormat('Y/m/d h:i:s A');
    }
}
