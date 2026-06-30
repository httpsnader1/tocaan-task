<?php

namespace App\Models\Builders;

class OrderBuilder extends BaseBuilder
{
    public function filters(): self
    {
        return $this->filterStatus(request('filterStatus'))
            ->filterUser(request('filterUser'))
            ->filterPaymentMethod(request('filterPaymentMethod'))
            ->filterPaymentStatus(request('filterPaymentStatus'));
    }

    public function filterStatus(?string $value): self
    {
        return $this->when($value, fn(self $query, string $value) => $query->whereStatus($value));
    }

    public function filterUser(?int $value): self
    {
        return $this->when($value, fn(self $query, int $value) => $query->whereUserId($value));
    }

    public function filterPaymentMethod(?string $value): self
    {
        return $this->when($value, function ($query, string $value) {
            $query->whereRelation('payment', 'method', $value);
        });
    }

    public function filterPaymentStatus(?string $value): self
    {
        return $this->when($value, function ($query, string $value) {
            $query->whereRelation('payment', 'status', $value);
        });
    }
}
