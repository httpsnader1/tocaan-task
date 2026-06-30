<?php

namespace App\Models\Builders;

class PaymentBuilder extends BaseBuilder
{
    public function filters(): self
    {
        return $this->filterStatus(request('filterStatus'))
            ->filterMethod(request('filterMethod'));
    }

    public function filterStatus(?string $value): self
    {
        return $this->when($value, fn(self $query, string $value) => $query->whereStatus($value));
    }

    public function filterMethod(?string $value): self
    {
        return $this->when($value, fn(self $query, string $value) => $query->whereMethod($value));
    }
}
